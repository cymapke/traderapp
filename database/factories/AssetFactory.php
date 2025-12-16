<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use App\Models\Ticker;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ticker_id' => Ticker::factory(),
            'amount' => $this->faker->randomFloat(18, 0, 100),
            'locked_amount' => $this->faker->randomFloat(18, 0, 10),
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forTicker(Ticker $ticker): self
    {
        return $this->state(fn (array $attributes) => [
            'ticker_id' => $ticker->id,
        ]);
    }

    public function withBalance(float $amount): self
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'locked_amount' => 0,
        ]);
    }

    public function withLocked(float $lockedAmount): self
    {
        return $this->state(fn (array $attributes) => [
            'locked_amount' => $lockedAmount,
        ]);
    }
}
