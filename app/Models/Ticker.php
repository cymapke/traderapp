<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticker extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'symbol',
        'type',
    ];

    /**
     * Get the ticker's type as a readable string.
     *
     * @return string
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'crypto' => 'Cryptocurrency',
            'currency' => 'Currency',
            'commodity' => 'Commodity',
            'stock' => 'Stock',
            default => 'Unknown',
        };
    }

    /**
     * Scope a query to only include crypto tickers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCrypto($query)
    {
        return $query->where('type', 'crypto');
    }

    /**
     * Scope a query to only include currency tickers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrency($query)
    {
        return $query->where('type', 'currency');
    }

    /**
     * Scope a query to only include commodity tickers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCommodity($query)
    {
        return $query->where('type', 'commodity');
    }

    /**
     * Scope a query to only include stock tickers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStock($query)
    {
        return $query->where('type', 'stock');
    }

    /**
     * Scope a query to only include tickers matching a search term.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('symbol', 'LIKE', "%{$search}%");
    }

    /**
     * Check if ticker is of specific type.
     *
     * @param string $type
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Get all available types.
     *
     * @return array
     */
    public static function getAvailableTypes(): array
    {
        return ['crypto', 'currency', 'commodity', 'stock'];
    }

    /**
     * Get the assets for this ticker.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get total holdings across all users for this ticker.
     */
    public function getTotalHoldingsAttribute()
    {
        return $this->assets()->sum('amount');
    }

    /**
     * Get total locked amount across all users for this ticker.
     */
    public function getTotalLockedAmountAttribute()
    {
        return $this->assets()->sum('locked_amount');
    }    
}
