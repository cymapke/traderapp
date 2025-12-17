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

    // Static price data (typical prices in USD)
    private static $cryptoPrices = [
        'BTC'  => 45000.50,    // Bitcoin
        'ETH'  => 3200.75,     // Ethereum
        'XRP'  => 0.85,        // Ripple
        'BNB'  => 350.20,      // Binance Coin
        'ADA'  => 0.60,        // Cardano
        'SOL'  => 120.45,      // Solana
        'DOT'  => 8.90,        // Polkadot
        'DOGE' => 0.15,        // Dogecoin
        'AVAX' => 40.30,       // Avalanche
        'LTC'  => 85.60,       // Litecoin
        'MATIC' => 0.95,       // Polygon
        'ATOM' => 12.30,       // Cosmos
        'LINK' => 18.75,       // Chainlink
        'UNI'  => 7.80,        // Uniswap
        'XLM'  => 0.13,        // Stellar
        'ETC'  => 28.90,       // Ethereum Classic
        'ALGO' => 0.25,        // Algorand
        'VET'  => 0.035,       // VeChain
        'XTZ'  => 1.20,        // Tezos
        'FIL'  => 6.50,        // Filecoin
    ];

    /**
     * Get the current price of the ticker in USD.
     * Returns static price for development/testing.
     */
    public function getPrice(): ?float
    {
        if ($this->type !== 'crypto') {
            // For non-crypto tickers, return null or handle differently
            return null;
        }

        return self::$cryptoPrices[$this->symbol] ?? $this->getRandomPrice();
    }

    /**
     * Get all available static prices.
     */
    public static function getAllPrices(): array
    {
        return self::$cryptoPrices;
    }

    /**
     * Get price with simulated minor fluctuations (for testing).
     */
    public function getFluctuatedPrice(): float
    {
        $basePrice = $this->getPrice();
        
        if (!$basePrice) {
            return 0;
        }
        
        // Simulate ±2% price fluctuation
        $fluctuation = mt_rand(-200, 200) / 10000; // -2% to +2%
        return round($basePrice * (1 + $fluctuation), 2);
    }

    /**
     * Get 24-hour price change percentage (simulated).
     */
    public function getPriceChange(): float
    {
        // Simulate random change between -5% and +5%
        return round(mt_rand(-500, 500) / 100, 2);
    }

    /**
     * Get market data for the ticker.
     */
    public function getMarketData(): array
    {
        $price = $this->getPrice();
        $change = $this->getPriceChange();
        
        return [
            'symbol' => $this->symbol,
            'price' => $price,
            'price_usd' => $price,
            'change_24h' => $change,
            'change_percent' => $change,
            'high_24h' => $price * 1.05, // Simulated high
            'low_24h' => $price * 0.95,  // Simulated low
            'volume' => $this->getSimulatedVolume(),
            'market_cap' => $this->getSimulatedMarketCap(),
            'last_updated' => now()->toISOString(),
        ];
    }

    /**
     * Get price with random variation for unknown symbols.
     */
    private function getRandomPrice(): float
    {
        // Return a random price between $0.10 and $100 for unknown cryptos
        return round(mt_rand(10, 100000) / 100, 2);
    }

    /**
     * Get simulated volume based on price.
     */
    private function getSimulatedVolume(): float
    {
        $price = $this->getPrice();
        $volume = $price * mt_rand(1000, 1000000);
        return round($volume, 2);
    }

    /**
     * Get simulated market cap based on price.
     */
    private function getSimulatedMarketCap(): float
    {
        $price = $this->getPrice();
        $marketCap = $price * mt_rand(1000000, 1000000000);
        return round($marketCap, 2);
    }    

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
