<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    use HasFactory;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buy_order_id',
        'sell_order_id',
        'buy_commission',
        'sell_commission'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'buy_commission' => 'decimal:2',
        'sell_commission' => 'decimal:2'        
    ];

    /**
     * Get the buy order for this trade.
     */
    public function buyOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'buy_order_id');
    }

    /**
     * Get the sell order for this trade.
     */
    public function sellOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sell_order_id');
    }

    /**
     * Get the ticker through either order.
     */
    public function ticker()
    {
        return $this->buyOrder->ticker() ?? $this->sellOrder->ticker();
    }

    /**
     * Get formatted buy commission.
     */
    public function getFormattedBuyCommissionAttribute(): string
    {
        return '$' . number_format($this->buy_commission, 2);
    }

    /**
     * Get formatted sell commission.
     */
    public function getFormattedSellCommissionAttribute(): string
    {
        return '$' . number_format($this->sell_commission, 2);
    }

    /**
     * Scope a query to only include trades for a specific ticker.
     */
    public function scopeForTicker($query, $tickerId)
    {
        return $query->whereHas('buyOrder', function ($q) use ($tickerId) {
            $q->where('ticker_id', $tickerId);
        })->orWhereHas('sellOrder', function ($q) use ($tickerId) {
            $q->where('ticker_id', $tickerId);
        });
    }

    /**
     * Scope a query to only include recent trades.
     */
    public function scopeRecent($query, $limit = 100)
    {
        return $query->orderBy('id', 'desc')->limit($limit);
    }

    public static function booted()
    {
        static::created(function ($trade) {
            // Eager load relationships when trade is created
            $trade->load(['buyOrder.ticker', 'sellOrder.ticker']);
        });
    }    
}
