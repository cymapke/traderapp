<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Ticker;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $side = $this->faker->randomElement(['BUY', 'SELL']);
        $price = $this->faker->randomFloat(6, 0.01, 100000);
        $amount = $this->faker->randomFloat(18, 0.001, 100);
        
        $status = $this->faker->randomElement([
            Order::STATUS_OPEN,
            Order::STATUS_FILLED,
            Order::STATUS_CANCELLED
        ]);
        
        $timestamps = $this->getTimestampsBasedOnStatus($status);
        
        return [
            'user_id' => User::factory(),
            'ticker_id' => Ticker::factory(),
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'status' => $status,
            'opened_at' => $timestamps['opened_at'],
            'filled_at' => $timestamps['filled_at'],
            'cancelled_at' => $timestamps['cancelled_at'],
        ];
    }
    
    private function getTimestampsBasedOnStatus($status): array
    {
        $openedAt = $this->faker->dateTimeBetween('-30 days', 'now');
        $filledAt = null;
        $cancelledAt = null;
        
        if ($status === Order::STATUS_FILLED) {
            $filledAt = $this->faker->dateTimeBetween($openedAt, 'now');
        } elseif ($status === Order::STATUS_CANCELLED) {
            $cancelledAt = $this->faker->dateTimeBetween($openedAt, 'now');
        }
        
        return [
            'opened_at' => $openedAt,
            'filled_at' => $filledAt,
            'cancelled_at' => $cancelledAt,
        ];
    }
    
    public function buy(): self
    {
        return $this->state(fn (array $attributes) => [
            'side' => 'BUY',
        ]);
    }
    
    public function sell(): self
    {
        return $this->state(fn (array $attributes) => [
            'side' => 'SELL',
        ]);
    }
    
    public function open(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_OPEN,
            'filled_at' => null,
            'cancelled_at' => null,
        ]);
    }
    
    public function filled(): self
    {
        return $this->state(function (array $attributes) {
            $filledAt = $this->faker->dateTimeBetween($attributes['opened_at'], 'now');
            
            return [
                'status' => Order::STATUS_FILLED,
                'filled_at' => $filledAt,
                'cancelled_at' => null,
            ];
        });
    }
    
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            $cancelledAt = $this->faker->dateTimeBetween($attributes['opened_at'], 'now');
            
            return [
                'status' => Order::STATUS_CANCELLED,
                'filled_at' => null,
                'cancelled_at' => $cancelledAt,
            ];
        });
    }
    
    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
    
    public function forTicker(Ticker $ticker): self
    {
        return $this->state(function (array $attributes) use ($ticker) {
            // Generate realistic price based on ticker symbol
            $price = $this->getRealisticPrice($ticker->symbol);
            $amount = $this->getRealisticAmount($ticker->symbol);
            
            return [
                'ticker_id' => $ticker->id,
                'price' => $price,
                'amount' => $amount,
            ];
        });
    }
    
    private function getRealisticPrice(string $symbol): float
    {
        $priceRanges = [
            'BTC' => [30000, 50000],
            'ETH' => [2000, 4000],
            'XRP' => [0.4, 1.0],
            'BNB' => [300, 600],
            'ADA' => [0.4, 1.0],
            'SOL' => [100, 200],
            'DOT' => [6, 10],
            'DOGE' => [0.1, 0.2],
            'AVAX' => [30, 50],
            'LTC' => [70, 100],
        ];
        
        $range = $priceRanges[$symbol] ?? [1, 100];
        return $this->faker->randomFloat(6, $range[0], $range[1]);
    }
    
    private function getRealisticAmount(string $symbol): float
    {
        $amountRanges = [
            'BTC' => [0.001, 2],
            'ETH' => [0.01, 10],
            'XRP' => [10, 5000],
            'BNB' => [0.1, 50],
            'ADA' => [10, 5000],
            'SOL' => [0.1, 50],
            'DOT' => [1, 200],
            'DOGE' => [100, 50000],
            'AVAX' => [0.1, 50],
            'LTC' => [0.1, 20],
        ];
        
        $range = $amountRanges[$symbol] ?? [0.1, 100];
        return $this->faker->randomFloat(18, $range[0], $range[1]);
    }
}
