<?php

namespace App\Models;

use App\Events\OrderUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
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
        'side',
        'price',
        'amount',
        'status',
        'opened_at',
        'filled_at',
        'cancelled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:6',
        'amount' => 'decimal:18',
        'opened_at' => 'datetime',
        'filled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Order status constants
     */
    const STATUS_OPEN = 1;
    const STATUS_FILLED = 2;
    const STATUS_CANCELLED = 3;

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ticker that the order is for.
     */
    public function ticker()
    {
        return $this->belongsTo(Ticker::class);
    }

    /**
     * Get the order status as a string.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'Open',
            self::STATUS_FILLED => 'Filled',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get the order side with icon.
     */
    public function getSideIconAttribute(): string
    {
        return $this->side === 'BUY' ? '🟢' : '🔴';
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, $this->price < 1 ? 6 : 2);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, $this->amount < 1 ? 8 : 2);
    }

    /**
     * Calculate total value (price * amount).
     */
    public function getTotalAttribute(): float
    {
        return bcmul($this->price, $this->amount, 2);
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    /**
     * Check if order is open.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if order is filled.
     */
    public function isFilled(): bool
    {
        return $this->status === self::STATUS_FILLED;
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Mark order as filled.
     */
    public function markAsFilled(): bool
    {
        return $this->update([
            'status' => self::STATUS_FILLED,
            'filled_at' => now(),
        ]);
    }

    /**
     * Mark order as cancelled.
     */
    public function markAsCancelled(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Scope a query to only include open orders.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope a query to only include filled orders.
     */
    public function scopeFilled($query)
    {
        return $query->where('status', self::STATUS_FILLED);
    }

    /**
     * Scope a query to only include cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope a query to only include buy orders.
     */
    public function scopeBuy($query)
    {
        return $query->where('side', 'BUY');
    }

    /**
     * Scope a query to only include sell orders.
     */
    public function scopeSell($query)
    {
        return $query->where('side', 'SELL');
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
     * Scope a query for recent orders.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Calculate total value of orders.
     */
    public static function getTotalValue($orders): float
    {
        return $orders->sum(fn($order) => $order->total);
    }

    /**
     * Calculate total locked amount for a user's open orders in a specific ticker.
     */
    public static function getLockedAmountForUserAndTicker($userId, $tickerId): float
    {
        return self::where('user_id', $userId)
            ->where('ticker_id', $tickerId)
            ->open()
            ->get()
            ->sum(fn($order) => $order->amount);
    }

    public function getTotalInOrders()
    {
        return $this->orders()->open()->get()->sum(fn($order) => $order->total);
    }

    public function getOpenOrdersTotal()
    {
        return $this->getTotalInOrders();
    }
    
    protected static function booted()
    {
        static::created(function ($order) {
            event(new OrderUpdated($order, 'created'));
        });

        static::updated(function ($order) {
            $action = 'updated';
            
            // Check for specific status changes
            if ($order->isDirty('status')) {
                if ($order->status === self::STATUS_FILLED) {
                    $action = 'filled';
                } elseif ($order->status === self::STATUS_CANCELLED) {
                    $action = 'cancelled';
                }
            }
            
            event(new OrderUpdated($order, $action));
        });

        static::deleted(function ($order) {
            event(new OrderUpdated($order, 'deleted'));
        });
    }    
}
