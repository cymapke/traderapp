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
    
// Get cash balance in USD
    public function getCashBalance(): float
    {
        return $this->balance;
    }

    // Get total value of all assets (amount * price)
    public function getAssetsValue(): float
    {
        $total = 0;
        
        foreach ($this->assets as $asset) {
            $ticker = $asset->ticker;
            if ($ticker && $ticker->type === 'crypto') {
                $price = $ticker->getPrice();
                $total += $asset->amount * $price;
            }
        }
        
        return $total;
    }

    // Get total value of locked assets (locked_amount * price)
    public function getLockedAssetsValue(): float
    {
        $total = 0;
        
        foreach ($this->assets as $asset) {
            $ticker = $asset->ticker;
            if ($ticker && $ticker->type === 'crypto' && $asset->locked_amount > 0) {
                $price = $ticker->getPrice();
                $total += $asset->locked_amount * $price;
            }
        }
        
        return $total;
    }

    // Get available assets value (total - locked)
    public function getAvailableAssetsValue(): float
    {
        return $this->getAssetsValue() - $this->getLockedAssetsValue();
    }

    // Get total balance (cash + all assets)
    public function getTotalBalance(): float
    {
        return $this->getCashBalance() + $this->getAssetsValue();
    }

    // Get percentage of available assets vs total portfolio
    public function getAvailableAssetsPercentage(): float
    {
        $totalPortfolio = $this->getTotalBalance();
        
        if ($totalPortfolio == 0) {
            return 0;
        }
        
        $availableValue = $this->getAvailableAssetsValue();
        return ($availableValue / $totalPortfolio) * 100;
    }

    // Get complete profile data for widget
    public function getProfileData(): array
    {
        $cashBalance = $this->getCashBalance();
        $assetsValue = $this->getAssetsValue();
        $lockedValue = $this->getLockedAssetsValue();
        $totalBalance = $this->getTotalBalance();
        $availablePercentage = $this->getAvailableAssetsPercentage();
        
        return [
            'cash_balance' => round($cashBalance, 2),
            'assets_value' => round($assetsValue, 2),
            'locked_assets_value' => round($lockedValue, 2),
            'total_balance' => round($totalBalance, 2),
            'available_percentage' => round($availablePercentage, 1),
            'formatted' => [
                'cash_balance' => '$' . number_format($cashBalance, 2),
                'assets_value' => '$' . number_format($assetsValue, 2),
                'locked_assets_value' => '$' . number_format($lockedValue, 2),
                'total_balance' => '$' . number_format($totalBalance, 2),
                'available_percentage' => number_format($availablePercentage, 1) . '%',
            ]
        ];
    }

    // Broadcast profile update via Pusher
    public function broadcastProfileUpdate(): void
    {
        event(new \App\Events\ProfileUpdated($this));
    }    
}
