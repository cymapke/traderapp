<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Models\Ticker;
use App\Models\Trade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing orders and trades
        Order::query()->delete();

        // Get users and tickers
        $users = User::all();
        $tickers = Ticker::all();

        if ($users->isEmpty() || $tickers->isEmpty()) {
            $this->command->error('Need users and tickers first. Run UserSeeder and TickerSeeder.');
            return;
        }

        // Target: 100 total orders, 25 matches (25 matching pairs = 50 matched orders)
        $totalTargetOrders = 100;
        $matchedPairsTarget = 25; // 25 matching pairs = 50 matched orders
        $openOrdersTarget = $totalTargetOrders - ($matchedPairsTarget * 2); // Remaining open orders
        
        $allOrders = [];
        
        $this->command->info("Creating {$totalTargetOrders} total orders with {$matchedPairsTarget} matching pairs...");

        // Step 1: Create MATCHING ORDER PAIRS (25 pairs = 50 orders)
        for ($pair = 1; $pair <= $matchedPairsTarget; $pair++) {
            // Select random ticker for this pair
            $ticker = $tickers->random();
            
            // Select different users for buyer and seller
            $buyer = $users->random();
            $seller = $users->where('id', '!=', $buyer->id)->random();
            
            // Generate matching price and amount
            $price = $this->generateRealisticPrice($ticker->symbol);
            $amount = $this->generateRealisticAmount($ticker->symbol);
            
            // Random timestamp within last 30 days
            $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            $filledAt = $createdAt->addMinutes(rand(1, 5));
            
            // Create BUY order (FILLED)
            $buyOrder = Order::create([
                'user_id' => $buyer->id,
                'ticker_id' => $ticker->id,
                'side' => 'BUY',
                'price' => $price,
                'amount' => $amount,
                'status' => Order::STATUS_FILLED,
                'opened_at' => $createdAt,
                'filled_at' => $filledAt,
                'cancelled_at' => null
            ]);
            
            // Create SELL order (FILLED) - matches with buy order
            $sellOrder = Order::create([
                'user_id' => $seller->id,
                'ticker_id' => $ticker->id,
                'side' => 'SELL',
                'price' => $price, // SAME PRICE for matching
                'amount' => $amount, // SAME AMOUNT for matching
                'status' => Order::STATUS_FILLED,
                'opened_at' => $createdAt->subMinutes(rand(1, 10)), // Sell order created before buy order
                'filled_at' => $filledAt,
               'cancelled_at' => null                
            ]);
            
            $allOrders[] = $buyOrder;
            $allOrders[] = $sellOrder;
            
            if ($pair % 5 === 0) {
                $this->command->info("  Created {$pair} matching pairs...");
            }
        }

        $this->command->info("✓ Created {$matchedPairsTarget} matching pairs ({($matchedPairsTarget * 2)} orders)");

        // Step 2: Create OPEN ORDERS (50 orders)
        $this->command->info("Creating {$openOrdersTarget} open orders...");
        
        for ($i = 1; $i <= $openOrdersTarget; $i++) {
            $ticker = $tickers->random();
            $user = $users->random();
            $side = rand(0, 1) ? 'BUY' : 'SELL';
            
            $price = $this->generateRealisticPrice($ticker->symbol);
            $amount = $this->generateRealisticAmount($ticker->symbol);
            
            // Random timestamp within last 7 days (recent orders)
            $createdAt = now()->subDays(rand(0, 7))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            Order::create([
                'user_id' => $user->id,
                'ticker_id' => $ticker->id,
                'side' => $side,
                'price' => $price,
                'amount' => $amount,
                'status' => Order::STATUS_OPEN,
                'opened_at' => $createdAt,
                'filled_at' => null,
                'cancelled_at' => null,
            ]);
            
            if ($i % 10 === 0) {
                $this->command->info("  Created {$i} open orders...");
            }
        }

        // Step 3: Create some CANCELLED ORDERS (10 orders)
        $cancelledOrders = 10;
        $this->command->info("Creating {$cancelledOrders} cancelled orders...");
        
        for ($i = 1; $i <= $cancelledOrders; $i++) {
            $ticker = $tickers->random();
            $user = $users->random();
            $side = rand(0, 1) ? 'BUY' : 'SELL';
            
            $price = $this->generateRealisticPrice($ticker->symbol);
            $amount = $this->generateRealisticAmount($ticker->symbol);
            
            // Order was created and cancelled in the past
            $createdAt = now()->subDays(rand(8, 30))->subHours(rand(0, 23));
            $cancelledAt = $createdAt->addHours(rand(1, 24));
            
            Order::create([
                'user_id' => $user->id,
                'ticker_id' => $ticker->id,
                'side' => $side,
                'price' => $price,
                'amount' => $amount,
                'status' => Order::STATUS_CANCELLED,
                'opened_at' => $createdAt,
                'filled_at' => null,
                'cancelled_at' => $cancelledAt,
            ]);
        }

        // Display statistics
        $this->displayOrderStatistics();
    }
    
    private function generateRealisticPrice(string $symbol): float
    {
        $priceRanges = [
            'BTC' => [35000, 55000],
            'ETH' => [2200, 3800],
            'XRP' => [0.45, 0.95],
            'BNB' => [320, 580],
            'ADA' => [0.42, 0.88],
            'SOL' => [95, 195],
            'DOT' => [6.5, 9.5],
            'DOGE' => [0.08, 0.18],
            'AVAX' => [28, 52],
            'LTC' => [68, 98],
            'MATIC' => [0.7, 1.1],
            'ATOM' => [10, 15],
            'LINK' => [14, 22],
            'UNI' => [6, 10],
            'XLM' => [0.10, 0.16],
            'ETC' => [25, 35],
            'ALGO' => [0.18, 0.30],
            'VET' => [0.02, 0.04],
            'XTZ' => [0.9, 1.5],
            'FIL' => [5, 8],
        ];
        
        $range = $priceRanges[$symbol] ?? [0.01, 100];
        $price = $range[0] + (mt_rand() / mt_getrandmax()) * ($range[1] - $range[0]);
        return round($price, 6);
    }
    
    private function generateRealisticAmount(string $symbol): float
    {
        $amountRanges = [
            'BTC' => [0.001, 1.5],
            'ETH' => [0.01, 8],
            'XRP' => [50, 10000],
            'BNB' => [0.1, 40],
            'ADA' => [50, 10000],
            'SOL' => [0.1, 40],
            'DOT' => [2, 150],
            'DOGE' => [1000, 100000],
            'AVAX' => [0.1, 40],
            'LTC' => [0.1, 15],
            'MATIC' => [10, 5000],
            'ATOM' => [1, 100],
            'LINK' => [0.5, 50],
            'UNI' => [0.5, 50],
            'XLM' => [100, 20000],
            'ETC' => [0.5, 30],
            'ALGO' => [10, 2000],
            'VET' => [100, 50000],
            'XTZ' => [5, 500],
            'FIL' => [0.5, 30],
        ];
        
        $range = $amountRanges[$symbol] ?? [0.1, 100];
        $amount = $range[0] + (mt_rand() / mt_getrandmax()) * ($range[1] - $range[0]);
        return round($amount, 8);
    }
    
    private function displayOrderStatistics(): void
    {
        $totalOrders = Order::count();
        $openOrders = Order::open()->count();
        $filledOrders = Order::filled()->count();
        $cancelledOrders = Order::cancelled()->count();
        $buyOrders = Order::buy()->count();
        $sellOrders = Order::sell()->count();
        
        // Calculate matching rate
        $matchedOrders = $filledOrders;
        $matchingRate = ($totalOrders > 0) ? round(($matchedOrders / $totalOrders) * 100, 1) : 0;
        
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('✅ ORDER SEEDER STATISTICS');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info("Total Orders: {$totalOrders}");
        $this->command->info("Matching Rate: {$matchingRate}%");
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info("Open Orders: {$openOrders}");
        $this->command->info("Filled Orders: {$filledOrders}");
        $this->command->info("Cancelled Orders: {$cancelledOrders}");
        $this->command->info("Buy Orders: {$buyOrders}");
        $this->command->info("Sell Orders: {$sellOrders}");
        $this->command->info('═══════════════════════════════════════════════');
        
        // Display top tickers by orders
        $topTickers = DB::table('orders')
            ->select('tickers.symbol', DB::raw('COUNT(orders.id) as order_count'))
            ->join('tickers', 'orders.ticker_id', '=', 'tickers.id')
            ->groupBy('tickers.id', 'tickers.symbol')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();
        
        if ($topTickers->isNotEmpty()) {
            $this->command->info('📈 Top Tickers by Orders:');
            foreach ($topTickers as $ticker) {
                $this->command->info("   {$ticker->symbol}: {$ticker->order_count} orders");
            }
        }
                
        $this->command->info('═══════════════════════════════════════════════');
    }
}
