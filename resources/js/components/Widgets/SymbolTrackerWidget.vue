<template>
    <div class="symbol-tracker-widget widget-container">
        <div class="widget-header">
            <div class="widget-title">
                <i class="fas fa-chart-line widget-icon"></i>
                <h3>Symbol Tracker</h3>
            </div>
            <div class="widget-actions">
                <button class="widget-action-btn" title="Add Symbol">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="widget-action-btn" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        
        <div class="widget-content">
            <!-- Search and Filter -->
            <div class="search-section">
                <div class="search-input">
                    <i class="fas fa-search search-icon"></i>
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        placeholder="Search symbols..."
                        class="search-field"
                    />
                </div>
                <select v-model="selectedCategory" class="category-select">
                    <option value="all">All Categories</option>
                    <option value="crypto">Cryptocurrency</option>
                    <option value="stocks">Stocks</option>
                    <option value="forex">Forex</option>
                    <option value="commodities">Commodities</option>
                </select>
            </div>
            
            <!-- Symbols Grid -->
            <div class="symbols-grid">
                <div 
                    v-for="symbol in filteredSymbols" 
                    :key="symbol.id"
                    class="symbol-card"
                    :class="{ 'symbol-card-active': activeSymbol === symbol.id }"
                    @click="activeSymbol = symbol.id"
                >
                    <div class="symbol-header">
                        <div class="symbol-name">
                            <span class="symbol-ticker">{{ symbol.ticker }}</span>
                            <span class="symbol-fullname">{{ symbol.name }}</span>
                        </div>
                        <div class="symbol-price">
                            <span class="price-amount">${{ symbol.price.toLocaleString() }}</span>
                            <span :class="['price-change', symbol.change >= 0 ? 'positive' : 'negative']">
                                {{ symbol.change >= 0 ? '+' : '' }}{{ symbol.change }}%
                            </span>
                        </div>
                    </div>
                    
                    <div class="symbol-chart">
                        <!-- Mini chart representation -->
                        <div class="mini-chart">
                            <div 
                                v-for="(point, index) in symbol.chartPoints" 
                                :key="index"
                                class="chart-point"
                                :style="{
                                    height: `${point}%`,
                                    backgroundColor: symbol.change >= 0 ? '#10b981' : '#ef4444'
                                }"
                            ></div>
                        </div>
                    </div>
                    
                    <div class="symbol-footer">
                        <div class="symbol-volume">
                            <span class="volume-label">24h Vol</span>
                            <span class="volume-value">${{ formatVolume(symbol.volume) }}</span>
                        </div>
                        <div class="symbol-market">
                            <span class="market-label">Market</span>
                            <span class="market-value">{{ symbol.market }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Active Symbol Details -->
            <div v-if="activeSymbolDetails" class="active-symbol-details">
                <div class="details-header">
                    <h4>{{ activeSymbolDetails.ticker }} - {{ activeSymbolDetails.name }}</h4>
                    <div class="details-price">
                        <span class="details-price-amount">${{ activeSymbolDetails.price.toLocaleString() }}</span>
                        <span :class="['details-price-change', activeSymbolDetails.change >= 0 ? 'positive' : 'negative']">
                            {{ activeSymbolDetails.change >= 0 ? '+' : '' }}{{ activeSymbolDetails.change }}%
                        </span>
                    </div>
                </div>
                
                <div class="details-stats">
                    <div class="stat">
                        <span class="stat-label">High</span>
                        <span class="stat-value">${{ activeSymbolDetails.high.toLocaleString() }}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Low</span>
                        <span class="stat-value">${{ activeSymbolDetails.low.toLocaleString() }}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Volume</span>
                        <span class="stat-value">${{ formatVolume(activeSymbolDetails.volume) }}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Market Cap</span>
                        <span class="stat-value">${{ formatVolume(activeSymbolDetails.marketCap) }}</span>
                    </div>
                </div>
                
                <div class="details-actions">
                    <button class="action-btn buy">
                        <i class="fas fa-arrow-up"></i>
                        <span>Buy</span>
                    </button>
                    <button class="action-btn sell">
                        <i class="fas fa-arrow-down"></i>
                        <span>Sell</span>
                    </button>
                    <button class="action-btn watchlist">
                        <i class="fas fa-star"></i>
                        <span>Watchlist</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedCategory = ref('all')
const activeSymbol = ref(1)

// Sample symbols data
const symbols = ref([
    { 
        id: 1, 
        ticker: 'BTC', 
        name: 'Bitcoin', 
        price: 42580.50, 
        change: 2.45, 
        volume: 28500000000,
        marketCap: 835000000000,
        market: 'Crypto',
        category: 'crypto',
        high: 42850.75,
        low: 42010.25,
        chartPoints: [30, 45, 35, 60, 40, 65, 50, 70, 55, 75]
    },
    { 
        id: 2, 
        ticker: 'ETH', 
        name: 'Ethereum', 
        price: 2150.75, 
        change: 1.25, 
        volume: 12500000000,
        marketCap: 258000000000,
        market: 'Crypto',
        category: 'crypto',
        high: 2180.50,
        low: 2105.25,
        chartPoints: [40, 50, 45, 55, 50, 60, 55, 65, 60, 70]
    },
    { 
        id: 3, 
        ticker: 'AAPL', 
        name: 'Apple Inc.', 
        price: 182.45, 
        change: -0.45, 
        volume: 85000000,
        marketCap: 2850000000000,
        market: 'NASDAQ',
        category: 'stocks',
        high: 184.20,
        low: 180.75,
        chartPoints: [70, 65, 60, 55, 50, 45, 40, 35, 30, 25]
    },
    { 
        id: 4, 
        ticker: 'TSLA', 
        name: 'Tesla Inc.', 
        price: 210.75, 
        change: 3.85, 
        volume: 125000000,
        marketCap: 670000000000,
        market: 'NASDAQ',
        category: 'stocks',
        high: 212.50,
        low: 205.25,
        chartPoints: [25, 30, 35, 40, 45, 50, 55, 60, 65, 70]
    },
    { 
        id: 5, 
        ticker: 'EUR/USD', 
        name: 'Euro/Dollar', 
        price: 1.0850, 
        change: 0.15, 
        volume: 450000000000,
        marketCap: 0,
        market: 'Forex',
        category: 'forex',
        high: 1.0875,
        low: 1.0820,
        chartPoints: [50, 55, 52, 57, 54, 59, 56, 61, 58, 63]
    },
    { 
        id: 6, 
        ticker: 'GC', 
        name: 'Gold', 
        price: 1985.50, 
        change: 0.85, 
        volume: 25000000000,
        marketCap: 0,
        market: 'Commodities',
        category: 'commodities',
        high: 1992.75,
        low: 1975.25,
        chartPoints: [45, 50, 47, 52, 49, 54, 51, 56, 53, 58]
    }
])

const filteredSymbols = computed(() => {
    return symbols.value.filter(symbol => {
        const matchesSearch = symbol.ticker.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                           symbol.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesCategory = selectedCategory.value === 'all' || symbol.category === selectedCategory.value
        
        return matchesSearch && matchesCategory
    })
})

const activeSymbolDetails = computed(() => {
    return symbols.value.find(symbol => symbol.id === activeSymbol.value)
})

const formatVolume = (volume) => {
    if (volume >= 1000000000) {
        return (volume / 1000000000).toFixed(1) + 'B'
    }
    if (volume >= 1000000) {
        return (volume / 1000000).toFixed(1) + 'M'
    }
    if (volume >= 1000) {
        return (volume / 1000).toFixed(1) + 'K'
    }
    return volume.toString()
}
</script>

<style scoped>
.symbol-tracker-widget {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.widget-content {
    flex: 1;
    padding: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Search Section */
.search-section {
    display: flex;
    gap: 12px;
    flex-shrink: 0;
}

.search-input {
    flex: 1;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
}

.search-field {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    color: #374151;
    background: white;
}

.search-field:focus {
    outline: none;
    border-color: #482e92;
    box-shadow: 0 0 0 3px rgba(72, 46, 146, 0.1);
}

.category-select {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    color: #374151;
    background: white;
    cursor: pointer;
    min-width: 140px;
}

.category-select:focus {
    outline: none;
    border-color: #482e92;
}

/* Symbols Grid */
.symbols-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    overflow-y: auto;
    min-height: 0;
    flex: 1;
}

.symbol-card {
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.symbol-card:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.symbol-card-active {
    background: white;
    border-color: #482e92;
    box-shadow: 0 0 0 1px #482e92, 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.symbol-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.symbol-name {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.symbol-ticker {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.symbol-fullname {
    font-size: 12px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.symbol-price {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.price-amount {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.price-change {
    font-size: 12px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
}

.price-change.positive {
    background: #d1fae5;
    color: #065f46;
}

.price-change.negative {
    background: #fee2e2;
    color: #991b1b;
}

.symbol-chart {
    height: 40px;
    display: flex;
    align-items: flex-end;
}

.mini-chart {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    width: 100%;
    height: 100%;
}

.chart-point {
    flex: 1;
    border-radius: 2px;
    min-height: 1px;
}

.symbol-footer {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
}

.volume-label,
.market-label {
    color: #6b7280;
    display: block;
}

.volume-value,
.market-value {
    color: #111827;
    font-weight: 600;
    display: block;
}

/* Active Symbol Details */
.active-symbol-details {
    border-top: 1px solid #e5e7eb;
    padding-top: 20px;
    flex-shrink: 0;
}

.details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.details-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.details-price {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.details-price-amount {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
}

.details-price-change {
    font-size: 14px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
}

.details-price-change.positive {
    background: #d1fae5;
    color: #065f46;
}

.details-price-change.negative {
    background: #fee2e2;
    color: #991b1b;
}

.details-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.stat-label {
    font-size: 13px;
    color: #6b7280;
}

.stat-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.details-actions {
    display: flex;
    gap: 12px;
}

.details-actions .action-btn {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.details-actions .action-btn.buy {
    background: #10b981;
    color: white;
}

.details-actions .action-btn.buy:hover {
    background: #059669;
}

.details-actions .action-btn.sell {
    background: #ef4444;
    color: white;
}

.details-actions .action-btn.sell:hover {
    background: #dc2626;
}

.details-actions .action-btn.watchlist {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.details-actions .action-btn.watchlist:hover {
    background: #e5e7eb;
}

/* Responsive */
@media (max-width: 1024px) {
    .symbols-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
}

@media (max-width: 768px) {
    .widget-content {
        padding: 16px;
        gap: 16px;
    }
    
    .search-section {
        flex-direction: column;
    }
    
    .symbols-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .details-stats {
        grid-template-columns: 1fr;
    }
    
    .details-actions {
        flex-direction: column;
    }
}
</style>
