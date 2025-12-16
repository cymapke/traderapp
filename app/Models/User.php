<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Accessor to get balance as dollars
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['balance'] / 100,
            set: fn ($value) => ['balance' => round($value * 100)],
        );
    }

    // Helper method to add money
    public function addMoney(float $amount): void
    {
        $this->increment('balance', round($amount * 100));
    }

    // Helper method to deduct money
    public function deductMoney(float $amount): bool
    {
        $deduction = round($amount * 100);
        
        if ($this->balance >= $deduction) {
            $this->decrement('balance', $deduction);
            return true;
        }
        
        return false; // Insufficient funds
    }

    // Helper method to check balance
    public function hasSufficientFunds(float $amount): bool
    {
        return $this->balance >= round($amount * 100);
    }

    /**
     * Get the user's assets.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get a specific asset by ticker symbol.
     */
    public function asset($tickerSymbol)
    {
        $ticker = Ticker::where('symbol', $tickerSymbol)->first();
        
        if (!$ticker) {
            return null;
        }

        return $this->assets()->where('ticker_id', $ticker->id)->first();
    }

    /**
     * Get all assets with positive balance.
     */
    public function assetsWithBalance()
    {
        return $this->assets()->withBalance()->with('ticker')->get();
    }

    /**
     * Get total portfolio value in USD (if you have price data).
     * This is a placeholder - you'll need to implement pricing logic.
     */
    public function getPortfolioValueAttribute()
    {
        $total = 0;
        foreach ($this->assetsWithBalance() as $asset) {
            // You'll need to fetch prices from an API or database
            // $price = $this->getPriceForTicker($asset->ticker->symbol);
            // $total += $asset->amount * $price;
        }
        return $total;
    }    
}
