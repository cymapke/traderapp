<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticker;
use Illuminate\Http\Request;

class OrderBookController extends Controller
{
    /**
     * Get order book for a specific symbol
     */
    public function index(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string|max:10',
            'limit' => 'sometimes|integer|min:1|max:1000',
        ]);

        $symbol = $request->get('symbol', 'BTC');
        $limit = $request->get('limit', 100);

        // Find ticker by symbol
        $ticker = Ticker::where('symbol', $symbol)->first();

        if (!$ticker) {
            return response()->json([
                'message' => 'Ticker not found',
                'buy_orders' => [],
                'sell_orders' => [],
                'market_price' => 0,
                'stats' => []
            ]);
        }

        // Get buy orders (bids) - sort by price descending
        $buyOrders = Order::where('ticker_id', $ticker->id)
            ->where('side', 'BUY')
            ->where('status', Order::STATUS_OPEN)
            ->orderBy('price', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'price' => (float) $order->price,
                    'amount' => (float) $order->amount,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        // Get sell orders (asks) - sort by price ascending
        $sellOrders = Order::where('ticker_id', $ticker->id)
            ->where('side', 'SELL')
            ->where('status', Order::STATUS_OPEN)
            ->orderBy('price', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'price' => (float) $order->price,
                    'amount' => (float) $order->amount,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        // Calculate market stats
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
            'total_buy_volume' => $buyOrders->sum('amount'),
            'total_sell_volume' => $sellOrders->sum('amount'),
            'total_buy_value' => $buyOrders->sum('total'),
            'total_sell_value' => $sellOrders->sum('total'),
        ];

        return response()->json([
            'symbol' => $symbol,
            'buy_orders' => $buyOrders,
            'sell_orders' => $sellOrders,
            'stats' => $stats,
        ]);
    }
}
