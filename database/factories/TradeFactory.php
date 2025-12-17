<?php

namespace Database\Factories;

use App\Models\Trade;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        $buyOrder = Order::factory()->buy()->filled()->create();
        $sellOrder = Order::factory()->sell()->filled()->create();
       
        return [
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'buy_commission' => $buyOrder->amount * $buyOrder->price * (1.5 / 100),
            'sell_commission' => $sellOrder->amount * $sellOrder->price * (1.5 / 100)  
        ];
    }
    
    public function forBuyOrder(Order $buyOrder): self
    {
        return $this->state(fn (array $attributes) => [
            'buy_order_id' => $buyOrder->id,
        ]);
    }
    
    public function forSellOrder(Order $sellOrder): self
    {
        return $this->state(fn (array $attributes) => [
            'sell_order_id' => $sellOrder->id,
        ]);
    }
}
