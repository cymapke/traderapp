<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticker;
use App\Models\Asset;
use App\Models\Trade;
use App\Models\User;
use App\Events\OrderUpdated;
use App\Events\TradeExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Get user's holdings for sell orders
     */
    public function getUserHoldings()
    {
        $user = Auth::user();
        
        $holdings = Asset::with('ticker')
            ->where('user_id', $user->id)
            ->whereRaw('amount > locked_amount')
            ->get()
            ->map(function($asset) {
                $currentPrice = $this->getCurrentPrice($asset->ticker->symbol);
                
                return [
                    'symbol' => $asset->ticker->symbol,
                    'name' => $asset->ticker->name,
                    'available' => (float) $asset->available_amount,
                    'current_price' => $currentPrice,
                    'value' => (float) bcsub($asset->amount, $asset->locked_amount, 18) * $currentPrice,
                ];
            })
            ->values();
        
        return response()->json(['holdings' => $holdings]);
    }
    
    /**
     * Get user's available USD balance
     */
    public function getAvailableBalance()
    {
        $user = Auth::user();
        
        $cashBalance = $user->getCashBalance();
        
        $lockedForBuyOrders = Order::where('user_id', $user->id)
            ->where('side', 'BUY')
            ->where('status', Order::STATUS_OPEN)
            ->get()
            ->sum(function($order) {
                return $order->amount * $order->price;
            });
        
        $availableBalance = max(0, $cashBalance - $lockedForBuyOrders);
        
        return response()->json([
            'available_balance' => $availableBalance,
            'total_balance' => $cashBalance,
            'locked_amount' => $lockedForBuyOrders,
        ]);
    }
    
    /**
     * Place a new order with immediate matching
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'symbol' => 'required|string|max:10',
            'side' => 'required|in:BUY,SELL',
            'price' => 'required|numeric|min:0.000001',
            'amount' => 'required|numeric|min:0.00000001',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $ticker = Ticker::where('symbol', $request->symbol)->first();
        
        if (!$ticker) {
            return response()->json([
                'success' => false,
                'message' => 'Symbol not found'
            ], 404);
        }
        
        try {
            return DB::transaction(function () use ($user, $ticker, $request) {
                // Validate and process the order based on side
                if ($request->side === 'BUY') {
                    $order = $this->processBuyOrder($user, $ticker, $request->price, $request->amount);
                } else {
                    $order = $this->processSellOrder($user, $ticker, $request->price, $request->amount);
                }
                
                // Attempt to match the new order immediately
                $this->attemptOrderMatching($order);
                
                // Refresh order to get updated status
                $order->refresh();
                
                // Broadcast order creation event
                event(new OrderUpdated($order, $order->status === Order::STATUS_FILLED ? 'filled' : 'created'));
                
                return response()->json([
                    'success' => true,
                    'message' => $order->status === Order::STATUS_FILLED 
                        ? 'Order filled immediately' 
                        : 'Order placed successfully',
                    'order' => [
                        'id' => $order->id,
                        'symbol' => $ticker->symbol,
                        'side' => $order->side,
                        'price' => (float) $order->price,
                        'amount' => (float) $order->amount,
                        'total' => (float) $order->total,
                        'status' => $order->status,
                        'status_text' => $order->status_text,
                        'opened_at' => $order->opened_at->toISOString(),
                        'filled_at' => $order->filled_at ? $order->filled_at->toISOString() : null,
                        'formatted_price' => $order->formatted_price,
                        'formatted_amount' => $order->formatted_amount,
                        'formatted_total' => $order->formatted_total,
                    ]
                ]);
            });
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process a BUY order
     */
    private function processBuyOrder(User $user, Ticker $ticker, $price, $amount)
    {
        // Calculate total cost
        $totalCost = bcmul($price, $amount, 18);
        
        // Check if user has sufficient balance
        if (!$user->hasSufficientFunds($totalCost)) {
            throw new \Exception('Insufficient USD balance');
        }
        
        // Deduct from user's balance
        if (!$user->deductMoney((float) $totalCost)) {
            throw new \Exception('Failed to deduct USD balance');
        }
        
        // Create the order
        return Order::create([
            'user_id' => $user->id,
            'ticker_id' => $ticker->id,
            'side' => 'BUY',
            'price' => $price,
            'amount' => $amount,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);
    }
    
    /**
     * Process a SELL order
     */
    private function processSellOrder(User $user, Ticker $ticker, $price, $amount)
    {
        // Get or create asset for the ticker
        $asset = Asset::getOrCreate($user->id, $ticker->id);
        
        // Check if user has sufficient available amount
        if (!$asset->hasSufficientAvailableAmount($amount)) {
            throw new \Exception('Insufficient holdings');
        }
        
        // Lock the amount for SELL order
        if (!$asset->lockAmount($amount)) {
            throw new \Exception('Failed to lock holdings');
        }
        
        // Create the order
        return Order::create([
            'user_id' => $user->id,
            'ticker_id' => $ticker->id,
            'side' => 'SELL',
            'price' => $price,
            'amount' => $amount,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);
    }
    
    /**
     * Attempt to match the new order immediately
     */
    private function attemptOrderMatching(Order $newOrder)
    {
        $matchingSide = $newOrder->side === 'BUY' ? 'SELL' : 'BUY';
        
        // Find matching orders (same ticker, opposite side, open status)
        $matchingOrders = Order::where('ticker_id', $newOrder->ticker_id)
            ->where('side', $matchingSide)
            ->where('status', Order::STATUS_OPEN)
            ->when($newOrder->side === 'BUY', function ($query) use ($newOrder) {
                // For BUY orders: match with SELL where sell.price <= buy.price
                return $query->where('price', '<=', $newOrder->price)
                    ->orderBy('price', 'asc') // Lowest sell price first
                    ->orderBy('created_at', 'asc'); // Oldest first
            }, function ($query) use ($newOrder) {
                // For SELL orders: match with BUY where buy.price >= sell.price
                return $query->where('price', '>=', $newOrder->price)
                    ->orderBy('price', 'desc') // Highest buy price first
                    ->orderBy('created_at', 'asc'); // Oldest first
            })
            ->get();
        
        foreach ($matchingOrders as $matchingOrder) {
            // Check if both orders have the same remaining amount (full match only)
            $newRemaining = bcsub($newOrder->amount, $newOrder->filled_quantity, 18);
            $matchRemaining = bcsub($matchingOrder->amount, $matchingOrder->filled_quantity, 18);
            
            // Full match only - amounts must be exactly equal
            if (bccomp($newRemaining, $matchRemaining, 18) === 0) {
                // Execute the trade within transaction
                $this->executeTrade($newOrder, $matchingOrder);
                break; // Stop after first match (full match only)
            }
        }
    }
    
    /**
     * Execute a trade between two orders
     */
    private function executeTrade(Order $order1, Order $order2)
    {
        DB::transaction(function () use ($order1, $order2) {
            // Determine which is buy and which is sell
            if ($order1->side === 'BUY') {
                $buyOrder = $order1;
                $sellOrder = $order2;
            } else {
                $buyOrder = $order2;
                $sellOrder = $order1;
            }
            
            // Use sell price for the trade (matching rule)
            $tradePrice = $sellOrder->price;
            $tradeQuantity = bcsub($buyOrder->amount, $buyOrder->filled_quantity, 18);
            
            // Calculate trade value
            $tradeValue = bcmul($tradePrice, $tradeQuantity, 18);
            
            // Calculate commissions (1.5% of trade value for each side)
            $commissionRate = '0.015'; // 1.5%
            $buyCommission = bcmul($tradeValue, $commissionRate, 18);
            $sellCommission = bcmul($tradeValue, $commissionRate, 18);
            
            // Create trade record with commissions
            $trade = Trade::create([
                'buy_order_id' => $buyOrder->id,
                'sell_order_id' => $sellOrder->id,
                'buy_commission' => $buyCommission,
                'sell_commission' => $sellCommission,
            ]);

            // Load relationships before broadcasting
            $trade->load(['buyOrder.ticker', 'sellOrder.ticker']);            
            
            // Update order prices to the executed price and mark as filled
            $buyOrder->update([
                'price' => $tradePrice, // Update buy order price to executed price
                'filled_quantity' => bcadd($buyOrder->filled_quantity, $tradeQuantity, 18),
                'status' => Order::STATUS_FILLED,
                'filled_at' => now(),
            ]);
            
            $sellOrder->update([
                'filled_quantity' => bcadd($sellOrder->filled_quantity, $tradeQuantity, 18),
                'status' => Order::STATUS_FILLED,
                'filled_at' => now(),
            ]);
            
            // Process settlement - transfer assets and funds
            $this->processTradeSettlement($buyOrder, $sellOrder, $trade);
            
            // Broadcast trade execution
            event(new TradeExecuted($trade));
        });
    }
    
    /**
     * Process trade settlement
     */
    private function processTradeSettlement(Order $buyOrder, Order $sellOrder, Trade $trade)
    {
        $buyer = $buyOrder->user;
        $seller = $sellOrder->user;
        $ticker = $buyOrder->ticker;
        
        $quantity = $trade->quantity;
        $price = $trade->price;
        $totalValue = bcmul($price, $quantity, 18);
        
        // Get commissions from trade record
        $buyCommission = $trade->buy_commission;
        $sellCommission = $trade->sell_commission;
        
        // Buyer receives cryptocurrency (from locked amount in seller's asset)
        $sellerAsset = Asset::getOrCreate($seller->id, $ticker->id);
        
        // Release locked amount from seller
        if (!$sellerAsset->releaseLockedAmount($quantity)) {
            throw new \Exception('Failed to release locked amount from seller');
        }
        
        // Transfer cryptocurrency to buyer
        $buyerAsset = Asset::getOrCreate($buyer->id, $ticker->id);
        $buyerAsset->addAmount($quantity);
        
        // Seller receives USD (minus sell commission)
        $sellerReceives = bcsub($totalValue, $sellCommission, 18);
        $seller->addMoney((float) $sellerReceives);
        
        // Buyer already paid full amount when order was placed
        // The buyer effectively pays: totalValue + buyCommission
        
        // Broadcast profile updates
        $buyer->broadcastProfileUpdate();
        $seller->broadcastProfileUpdate();
        
        // Log the trade settlement
        \Log::info('Trade settled', [
            'trade_id' => $trade->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'symbol' => $ticker->symbol,
            'quantity' => $quantity,
            'price' => $price,
            'total_value' => $totalValue,
            'buy_commission' => $buyCommission,
            'sell_commission' => $sellCommission,
            'seller_receives' => $sellerReceives,
            'buyer_pays' => bcadd($totalValue, $buyCommission, 18),
        ]);
    }
    
    /**
     * Get current price for a symbol
     */
    private function getCurrentPrice($symbol)
    {
        $prices = [
            'BTC' => 42580,
            'ETH' => 2150,
            'XRP' => 0.62,
            'BNB' => 320,
            'ADA' => 0.52,
            'SOL' => 98,
            'DOT' => 7.25,
            'DOGE' => 0.15,
            'AVAX' => 42,
            'LTC' => 85,
            'MATIC' => 0.85,
            'ATOM' => 12,
            'LINK' => 16,
            'UNI' => 7.5,
            'XLM' => 0.13,
            'ETC' => 28,
            'ALGO' => 0.18,
            'VET' => 0.03,
            'XTZ' => 1.05,
            'FIL' => 5.8
        ];
        
        return $prices[$symbol] ?? 100;
    }
    
    /**
     * Get user's open orders
     */
    public function getUserOrders()
    {
        $user = Auth::user();
        
        $orders = Order::with('ticker')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'symbol' => $order->ticker->symbol,
                    'side' => $order->side,
                    'price' => (float) $order->price,
                    'amount' => (float) $order->amount,
                    'filled' => (float) $order->filled_quantity,
                    'remaining' => (float) ($order->amount - $order->filled_quantity),
                    'total' => (float) $order->total,
                    'status' => $order->status,
                    'status_text' => $order->status_text,
                    'opened_at' => $order->opened_at->toISOString(),
                    'filled_at' => $order->filled_at ? $order->filled_at->toISOString() : null,
                    'formatted_price' => $order->formatted_price,
                    'formatted_amount' => $order->formatted_amount,
                    'formatted_total' => $order->formatted_total,
                ];
            });
        
        return response()->json(['orders' => $orders]);
    }
    
    /**
     * Cancel an order
     */
    public function cancelOrder($id)
    {
        $user = Auth::user();
        
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        if ($order->status !== Order::STATUS_OPEN) {
            return response()->json([
                'success' => false,
                'message' => 'Only open orders can be cancelled'
            ], 400);
        }
        
        try {
            return DB::transaction(function () use ($user, $order) {
                if ($order->side === 'BUY') {
                    // Refund locked USD to user
                    $refundAmount = $order->amount * $order->price;
                    $user->addMoney((float) $refundAmount);
                } else {
                    // SELL order - unlock the asset
                    $asset = Asset::getOrCreate($user->id, $order->ticker_id);
                    if (!$asset->unlockAmount($order->amount)) {
                        throw new \Exception('Failed to unlock asset');
                    }
                }
                
                $order->markAsCancelled();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully',
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'status_text' => $order->status_text,
                    ]
                ]);
            });
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get order book for a symbol
     */
    public function getOrderBook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'symbol' => 'required|string|max:10',
            'limit' => 'sometimes|integer|min:1|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $symbol = $request->get('symbol');
        $limit = $request->get('limit', 100);
        
        $ticker = Ticker::where('symbol', $symbol)->first();
        
        if (!$ticker) {
            return response()->json([
                'success' => false,
                'message' => 'Symbol not found'
            ], 404);
        }
        
        // Get buy orders (bids) - highest price first
        $buyOrders = Order::where('ticker_id', $ticker->id)
            ->where('side', 'BUY')
            ->where('status', Order::STATUS_OPEN)
            ->orderBy('price', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'price' => (float) $order->price,
                    'amount' => (float) $order->amount,
                    'filled' => (float) $order->filled_quantity,
                    'remaining' => (float) ($order->amount - $order->filled_quantity),
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });
        
        // Get sell orders (asks) - lowest price first
        $sellOrders = Order::where('ticker_id', $ticker->id)
            ->where('side', 'SELL')
            ->where('status', Order::STATUS_OPEN)
            ->orderBy('price', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'price' => (float) $order->price,
                    'amount' => (float) $order->amount,
                    'filled' => (float) $order->filled_quantity,
                    'remaining' => (float) ($order->amount - $order->filled_quantity),
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });
        
        // Calculate market statistics
        $highestBid = $buyOrders->max('price') ?? 0;
        $lowestAsk = $sellOrders->min('price') ?? 0;
        $marketPrice = $highestBid > 0 && $lowestAsk > 0 
            ? ($highestBid + $lowestAsk) / 2 
            : ($highestBid > 0 ? $highestBid : ($lowestAsk > 0 ? $lowestAsk : 0));
        
        $stats = [
            'market_price' => $marketPrice,
            'highest_bid' => $highestBid,
            'lowest_ask' => $lowestAsk,
            'spread' => $lowestAsk > 0 && $highestBid > 0 ? $lowestAsk - $highestBid : 0,
            'total_buy_volume' => $buyOrders->sum('remaining'),
            'total_sell_volume' => $sellOrders->sum('remaining'),
            'total_buy_value' => $buyOrders->sum(function($order) {
                return $order['remaining'] * $order['price'];
            }),
            'total_sell_value' => $sellOrders->sum(function($order) {
                return $order['remaining'] * $order['price'];
            }),
        ];
        
        return response()->json([
            'success' => true,
            'symbol' => $symbol,
            'buy_orders' => $buyOrders,
            'sell_orders' => $sellOrders,
            'stats' => $stats,
        ]);
    }
}
