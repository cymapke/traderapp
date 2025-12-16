<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ticker_id',
        'amount',
        'locked_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:18',
        'locked_amount' => 'decimal:18',
    ];

    /**
     * Get the user that owns the asset.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ticker that owns the asset.
     */
    public function ticker()
    {
        return $this->belongsTo(Ticker::class);
    }

    /**
     * Get the available (unlocked) amount.
     */
    public function getAvailableAmountAttribute()
    {
        return bcsub($this->amount, $this->locked_amount, 18);
    }

    /**
     * Get the total amount (including locked).
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount;
    }

    /**
     * Check if there's sufficient available amount.
     */
    public function hasSufficientAvailableAmount($amount): bool
    {
        return bccomp($this->available_amount, $amount, 18) >= 0;
    }

    /**
     * Check if there's sufficient total amount.
     */
    public function hasSufficientAmount($amount): bool
    {
        return bccomp($this->amount, $amount, 18) >= 0;
    }

    /**
     * Lock a specific amount.
     */
    public function lockAmount($amount): bool
    {
        if (!$this->hasSufficientAvailableAmount($amount)) {
            return false;
        }

        $this->locked_amount = bcadd($this->locked_amount, $amount, 18);
        return $this->save();
    }

    /**
     * Unlock a specific amount.
     */
    public function unlockAmount($amount): bool
    {
        if (bccomp($this->locked_amount, $amount, 18) < 0) {
            return false;
        }

        $this->locked_amount = bcsub($this->locked_amount, $amount, 18);
        return $this->save();
    }

    /**
     * Add amount to available balance.
     */
    public function addAmount($amount): bool
    {
        $this->amount = bcadd($this->amount, $amount, 18);
        return $this->save();
    }

    /**
     * Subtract amount from available balance.
     */
    public function subtractAmount($amount): bool
    {
        if (!$this->hasSufficientAvailableAmount($amount)) {
            return false;
        }

        $this->amount = bcsub($this->amount, $amount, 18);
        return $this->save();
    }

    /**
     * Transfer from locked to available (after successful transaction).
     */
    public function releaseLockedAmount($amount): bool
    {
        if (bccomp($this->locked_amount, $amount, 18) < 0) {
            return false;
        }

        $this->locked_amount = bcsub($this->locked_amount, $amount, 18);
        $this->amount = bcsub($this->amount, $amount, 18);
        return $this->save();
    }

    /**
     * Scope a query to only include assets with positive amount.
     */
    public function scopeWithBalance($query)
    {
        return $query->where('amount', '>', 0);
    }

    /**
     * Scope a query to only include assets with available amount.
     */
    public function scopeWithAvailableBalance($query)
    {
        return $query->whereRaw('amount > locked_amount');
    }

    /**
     * Scope a query for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query for a specific ticker.
     */
    public function scopeForTicker($query, $tickerId)
    {
        return $query->where('ticker_id', $tickerId);
    }

    /**
     * Get or create asset for user and ticker.
     */
    public static function getOrCreate($userId, $tickerId)
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'ticker_id' => $tickerId],
            ['amount' => 0, 'locked_amount' => 0]
        );
    }
}
