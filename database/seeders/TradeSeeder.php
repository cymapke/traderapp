<?php

namespace Database\Seeders;

use App\Models\Trade;
use App\Models\Order;
use App\Models\Ticker;
use Illuminate\Database\Seeder;

class TradeSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing trades first
        Trade::query()->delete();

        // Get all tickers
        $tickers = Ticker::all();
        
        foreach ($tickers as $ticker) {
            // Create some completed trades for each ticker
            $buyOrders = Order::where('ticker_id', $ticker->id)
                ->where('side', 'BUY')
                ->where('status', Order::STATUS_FILLED)
                ->limit(10)
                ->get();
                
            $sellOrders = Order::where('ticker_id', $ticker->id)
                ->where('side', 'SELL')
                ->where('status', Order::STATUS_FILLED)
                ->limit(10)
                ->get();
            
            for ($i = 0; $i < min(10, count($buyOrders), count($sellOrders)); $i++) {
                Trade::create([
                    'buy_order_id' => $buyOrders[$i]->id,
                    'sell_order_id' => $sellOrders[$i]->id,
                    'buy_commission' => $buyOrders[$i]->amount * $buyOrders[$i]->price * (1.5 / 100)  ,
                    'sell_commission' => $buyOrders[$i]->amount * $buyOrders[$i]->price * (1.5 / 100)
                ]);
            }
        }
    }
}
