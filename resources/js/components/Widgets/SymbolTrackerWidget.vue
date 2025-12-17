<template>
    <div class="symbol-tracker-widget widget-container">
        <div class="widget-header">
            <div class="header-left">
                <span class="icon">📈</span>
                <h3>Order Book</h3>
            </div>
            <button 
                @click="refreshProfile" 
                class="refresh-btn"
                :class="{ 'refreshing': loading }"
                :disabled="loading"
            >
                <span class="icon">{{ loading ? '⏳' : '↻' }}</span>
            </button>
        </div>
        
        <div class="widget-content">
            <!-- Search and Symbols Grid -->
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
                <div class="symbols-scroll-container">
                    <div class="symbols-scroll">
                        <div 
                            v-for="symbol in filteredSymbols" 
                            :key="symbol.id"
                            class="symbol-card"
                            :class="{ 'symbol-card-active': activeSymbol === symbol.symbol }"
                            @click="selectSymbol(symbol)"
                        >
                            <div class="symbol-header">
                                <div class="symbol-name">
                                    <span class="symbol-ticker">{{ symbol.symbol }}</span>
                                    <span class="symbol-fullname">{{ symbol.name }}</span>
                                </div>
                                <div class="symbol-price">
                                    <span class="price-amount">${{ formatPrice(symbol.price) }}</span>
                                    <span :class="['price-change', symbol.change >= 0 ? 'positive' : 'negative']">
                                        {{ symbol.change >= 0 ? '+' : '' }}{{ symbol.change.toFixed(2) }}%
                                    </span>
                                </div>
                            </div>
                            
                            <div class="symbol-chart">
                                <div class="mini-chart">
                                    <div 
                                        v-for="(point, index) in getChartPoints(symbol)" 
                                        :key="index"
                                        class="chart-point"
                                        :style="{
                                            height: `${point}%`,
                                            backgroundColor: symbol.change >= 0 ? '#10b981' : '#ef4444'
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Book -->
            <div v-if="activeSymbolData" class="order-book-container">
                <div class="order-book-header">
                    <h4>{{ activeSymbolData.symbol }} - {{ activeSymbolData.name }} Order Book</h4>
                    <div class="order-book-stats">
                        <div class="stat-item">
                            <span class="stat-label">Market Price:</span>
                            <span class="stat-value">${{ formatPrice(marketPrice) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Spread:</span>
                            <span class="stat-value">${{ formatPrice(spread) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="order-book-content">
                    <!-- Buy Orders (Bids) -->
                    <div class="order-book-side">
                        <div class="order-book-side-header">
                            <h5>Buy Orders (Bids)</h5>
                            <div class="side-total">
                                <span>Total: ${{ formatVolume(buyTotal) }}</span>
                            </div>
                        </div>
                        <div class="order-book-table-container">
                            <div class="order-book-table-header">
                                <div class="order-col">Price</div>
                                <div class="order-col">Amount</div>
                                <div class="order-col">Total</div>
                            </div>
                            <div class="order-book-table-body" ref="buyOrdersBody">
                                <div 
                                    v-for="(order, index) in buyOrders" 
                                    :key="`buy-${order.id || index}`"
                                    class="order-row buy-order-row"
                                    :class="{ 'order-flash': order.flash }"
                                    @animationend="removeFlash(order)"
                                >
                                    <div class="order-col price-col" :style="{ color: '#10b981' }">
                                        ${{ formatPrice(order.price) }}
                                    </div>
                                    <div class="order-col amount-col">
                                        {{ formatAmount(order.amount) }}
                                    </div>
                                    <div class="order-col total-col">
                                        ${{ formatPrice(order.total) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sell Orders (Asks) -->
                    <div class="order-book-side">
                        <div class="order-book-side-header">
                            <h5>Sell Orders (Asks)</h5>
                            <div class="side-total">
                                <span>Total: ${{ formatVolume(sellTotal) }}</span>
                            </div>
                        </div>
                        <div class="order-book-table-container">
                            <div class="order-book-table-header">
                                <div class="order-col">Price</div>
                                <div class="order-col">Amount</div>
                                <div class="order-col">Total</div>
                            </div>
                            <div class="order-book-table-body" ref="sellOrdersBody">
                                <div 
                                    v-for="(order, index) in sellOrders" 
                                    :key="`sell-${order.id || index}`"
                                    class="order-row sell-order-row"
                                    :class="{ 'order-flash': order.flash }"
                                    @animationend="removeFlash(order)"
                                >
                                    <div class="order-col price-col" :style="{ color: '#ef4444' }">
                                        ${{ formatPrice(order.price) }}
                                    </div>
                                    <div class="order-col amount-col">
                                        {{ formatAmount(order.amount) }}
                                    </div>
                                    <div class="order-col total-col">
                                        ${{ formatPrice(order.total) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import axios from 'axios'

const props = defineProps({
    initialSymbol: {
        type: String,
        default: 'BTC'
    }
})

const searchQuery = ref('')
const activeSymbol = ref(props.initialSymbol)
const loading = ref(false)
const symbols = ref([])
const buyOrders = ref([])
const sellOrders = ref([])
const buyOrdersBody = ref(null)
const sellOrdersBody = ref(null)

// Default symbols - will be populated from orderbook data
const defaultSymbols = [
    { id: 1, symbol: 'BTC', name: 'Bitcoin', price: 42580.50, change: 2.45 },
    { id: 2, symbol: 'ETH', name: 'Ethereum', price: 2150.75, change: 1.25 },
    { id: 3, symbol: 'SOL', name: 'Solana', price: 98.45, change: 5.25 },
    { id: 4, symbol: 'ADA', name: 'Cardano', price: 0.52, change: -1.45 },
    { id: 5, symbol: 'DOT', name: 'Polkadot', price: 7.25, change: 2.15 },
    { id: 6, symbol: 'XRP', name: 'Ripple', price: 0.62, change: 0.85 },
]

// Initialize with default symbols
symbols.value = defaultSymbols

// Fetch order book data
const fetchOrderBook = async () => {
    loading.value = true
    try {
        const response = await api.get('/orderbook', {
            params: { symbol: activeSymbol.value }
        })
        
        // Update market price for the active symbol
        updateSymbolPriceFromOrderBook(response.data)
        
        // Process buy orders (sort by price descending for bids)
        buyOrders.value = response.data.buy_orders
            .map(order => ({ ...order, flash: false }))
            .sort((a, b) => b.price - a.price)
        
        // Process sell orders (sort by price ascending for asks)
        sellOrders.value = response.data.sell_orders
            .map(order => ({ ...order, flash: false }))
            .sort((a, b) => a.price - b.price)
        
        // Scroll to top of order tables
        nextTick(() => {
            if (buyOrdersBody.value) buyOrdersBody.value.scrollTop = 0
            if (sellOrdersBody.value) sellOrdersBody.value.scrollTop = 0
        })
    } catch (error) {
        console.error('Error fetching order book:', error)
        // Use mock data for development
        useMockData()
    } finally {
        loading.value = false
    }
}

// Update symbol price based on order book data
const updateSymbolPriceFromOrderBook = (orderBookData) => {
    const symbolIndex = symbols.value.findIndex(s => s.symbol === activeSymbol.value)
    if (symbolIndex !== -1 && orderBookData.stats) {
        symbols.value[symbolIndex].price = orderBookData.stats.market_price || symbols.value[symbolIndex].price
        
        // Simulate price change based on order book activity
        const highestBid = orderBookData.stats.highest_bid || 0
        const lowestAsk = orderBookData.stats.lowest_ask || 0
        if (highestBid > 0 && lowestAsk > 0) {
            const spreadRatio = (lowestAsk - highestBid) / highestBid
            // Generate random change within reasonable bounds
            const change = (Math.random() * 10 - 5) * (1 - Math.abs(spreadRatio))
            symbols.value[symbolIndex].change = Number(change.toFixed(2))
        }
    }
}

// Mock data for development (remove when API is ready)
const useMockData = () => {
    // Update active symbol price with simulated change
    const symbolIndex = symbols.value.findIndex(s => s.symbol === activeSymbol.value)
    if (symbolIndex !== -1) {
        const change = (Math.random() * 10 - 5)
        symbols.value[symbolIndex].change = Number(change.toFixed(2))
        
        // Adjust price based on change
        const currentPrice = symbols.value[symbolIndex].price
        const newPrice = currentPrice * (1 + change / 100)
        symbols.value[symbolIndex].price = Number(newPrice.toFixed(2))
    }
    
    // Generate mock buy orders
    const mockBuyOrders = []
    const basePrice = symbols.value.find(s => s.symbol === activeSymbol.value)?.price || 42500
    let buyPrice = basePrice * 0.99 // Start 1% below market
    
    for (let i = 0; i < 20; i++) {
        const price = buyPrice - (Math.random() * basePrice * 0.01) // Random within 1% range
        const amount = Math.random() * 2
        mockBuyOrders.push({
            id: `buy-${i}`,
            price: price,
            amount: amount,
            total: price * amount,
            flash: false
        })
    }
    buyOrders.value = mockBuyOrders.sort((a, b) => b.price - a.price)
    
    // Generate mock sell orders
    const mockSellOrders = []
    let sellPrice = basePrice * 1.01 // Start 1% above market
    
    for (let i = 0; i < 20; i++) {
        const price = sellPrice + (Math.random() * basePrice * 0.01) // Random within 1% range
        const amount = Math.random() * 2
        mockSellOrders.push({
            id: `sell-${i}`,
            price: price,
            amount: amount,
            total: price * amount,
            flash: false
        })
    }
    sellOrders.value = mockSellOrders.sort((a, b) => a.price - b.price)
}

// Filter symbols based on search
const filteredSymbols = computed(() => {
    return symbols.value.filter(symbol => {
        return symbol.symbol.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
               symbol.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    })
})

// Get active symbol data
const activeSymbolData = computed(() => {
    return symbols.value.find(symbol => symbol.symbol === activeSymbol.value) || symbols.value[0]
})

// Calculate market statistics
const marketPrice = computed(() => {
    if (buyOrders.value.length > 0 && sellOrders.value.length > 0) {
        const highestBid = Math.max(...buyOrders.value.map(o => o.price))
        const lowestAsk = Math.min(...sellOrders.value.map(o => o.price))
        return (highestBid + lowestAsk) / 2
    }
    return activeSymbolData.value?.price || 0
})

const spread = computed(() => {
    if (buyOrders.value.length > 0 && sellOrders.value.length > 0) {
        const highestBid = Math.max(...buyOrders.value.map(o => o.price))
        const lowestAsk = Math.min(...sellOrders.value.map(o => o.price))
        return lowestAsk - highestBid
    }
    return 0
})

// Calculate totals
const buyTotal = computed(() => {
    return buyOrders.value.reduce((sum, order) => sum + (order.total || 0), 0)
})

const sellTotal = computed(() => {
    return sellOrders.value.reduce((sum, order) => sum + (order.total || 0), 0)
})

// Select symbol
const selectSymbol = (symbol) => {
    activeSymbol.value = symbol.symbol
    fetchOrderBook()
}

// Format helpers
const formatPrice = (price) => {
    if (!price) return '0.00'
    if (price < 1) return price.toFixed(6)
    if (price < 1000) return price.toFixed(2)
    return price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatVolume = (volume) => {
    if (!volume) return '0.00'
    if (volume >= 1000000000) return (volume / 1000000000).toFixed(1) + 'B'
    if (volume >= 1000000) return (volume / 1000000).toFixed(1) + 'M'
    if (volume >= 1000) return (volume / 1000).toFixed(1) + 'K'
    return volume.toFixed(2)
}

const formatAmount = (amount) => {
    if (!amount) return '0.00'
    if (amount < 0.000001) return amount.toFixed(12)
    if (amount < 0.001) return amount.toFixed(8)
    if (amount < 1) return amount.toFixed(6)
    if (amount < 1000) return amount.toFixed(2)
    return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// Generate chart points
const getChartPoints = (symbol) => {
    // Generate random chart points based on price and change
    const points = []
    const base = 50
    const volatility = Math.abs(symbol.change) * 2
    
    for (let i = 0; i < 10; i++) {
        const random = (Math.random() - 0.5) * volatility * 2
        points.push(Math.max(20, Math.min(80, base + random)))
    }
    
    // Ensure trend matches change direction
    if (symbol.change >= 0) {
        points.sort((a, b) => a - b) // Ascending for positive change
    } else {
        points.sort((a, b) => b - a) // Descending for negative change
    }
    
    return points
}

// Simulate real-time updates (replace with actual Pusher when ready)
const simulateRealTimeUpdates = () => {
    // Simulate new orders
    setInterval(() => {
        if (Math.random() > 0.7) {
            const isBuy = Math.random() > 0.5
            const basePrice = activeSymbolData.value.price
            const price = isBuy 
                ? basePrice * (0.99 - Math.random() * 0.01)
                : basePrice * (1.01 + Math.random() * 0.01)
            const amount = Math.random() * 0.5
            
            const newOrder = {
                id: `new-${Date.now()}`,
                price: price,
                amount: amount,
                total: price * amount,
                flash: true
            }
            
            if (isBuy) {
                buyOrders.value.unshift(newOrder)
                buyOrders.value.sort((a, b) => b.price - a.price)
                // Scroll to top
                nextTick(() => {
                    if (buyOrdersBody.value) buyOrdersBody.value.scrollTop = 0
                })
            } else {
                sellOrders.value.unshift(newOrder)
                sellOrders.value.sort((a, b) => a.price - b.price)
                // Scroll to top
                nextTick(() => {
                    if (sellOrdersBody.value) sellOrdersBody.value.scrollTop = 0
                })
            }
        }
        
        // Randomly remove some orders to simulate matching
        if (Math.random() > 0.5 && buyOrders.value.length > 5) {
            const indexToRemove = Math.floor(Math.random() * buyOrders.value.length)
            buyOrders.value.splice(indexToRemove, 1)
        }
        if (Math.random() > 0.5 && sellOrders.value.length > 5) {
            const indexToRemove = Math.floor(Math.random() * sellOrders.value.length)
            sellOrders.value.splice(indexToRemove, 1)
        }
        
        // Occasionally update symbol price
        if (Math.random() > 0.8) {
            const symbolIndex = symbols.value.findIndex(s => s.symbol === activeSymbol.value)
            if (symbolIndex !== -1) {
                const currentPrice = symbols.value[symbolIndex].price
                const change = (Math.random() * 2 - 1) // Small random change
                symbols.value[symbolIndex].change = Number(change.toFixed(2))
                symbols.value[symbolIndex].price = Number((currentPrice * (1 + change / 100)).toFixed(2))
            }
        }
    }, 3000) // Update every 3 seconds
}

// Remove flash effect after animation
const removeFlash = (order) => {
    order.flash = false
}

// Initialize
onMounted(async () => {
    await fetchOrderBook()
    
    // Start simulating real-time updates for demo
    simulateRealTimeUpdates()
})

// Cleanup
onUnmounted(() => {
    // Clear any intervals if needed
})
</script>

<style scoped>
.symbol-tracker-widget {
  background: white;
  border-radius: 10px;
  padding: 16px;
  box-shadow: 0 1px 8px rgba(120, 119, 198, 0.08);
  border: 1px solid #f0f0f5;
  position: relative;
  font-family: 'Inter', -apple-system, sans-serif;
  color: #333;
}

/* Header */
.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.icon {
  font-size: 16px;
  opacity: 0.8;
}

h3 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #5d5d8a;
  letter-spacing: 0.2px;
  text-transform: uppercase;
}

.refresh-btn {
  background: #f8f8fc;
  border: 1px solid #e6e6f0;
  border-radius: 6px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #7877c6;
  font-size: 12px;
}

.refresh-btn:hover:not(:disabled) {
  background: #7877c6;
  color: white;
  transform: rotate(90deg);
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.refresh-btn.refreshing {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Top Row Layout */
.top-row {
  display: flex;
  gap: 12px;
  margin-bottom: 12px;
}

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
    flex-direction: column;
    gap: 12px;
    flex-shrink: 0;
}

.search-input {
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

/* Symbols Scroll */
.symbols-scroll-container {
    overflow-x: auto;
    padding-bottom: 8px;
}

.symbols-scroll {
    display: flex;
    gap: 12px;
    min-width: min-content;
}

.symbol-card {
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 140px;
    flex-shrink: 0;
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
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}

.symbol-fullname {
    font-size: 11px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 80px;
}

.symbol-price {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.price-amount {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
}

.price-change {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 4px;
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
    height: 30px;
    display: flex;
    align-items: flex-end;
}

.mini-chart {
    display: flex;
    align-items: flex-end;
    gap: 1px;
    width: 100%;
    height: 100%;
}

.chart-point {
    flex: 1;
    border-radius: 1px;
    min-height: 1px;
    background: #10b981;
}

/* Order Book Container */
.order-book-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    flex: 1;
    overflow: hidden;
}

.order-book-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.order-book-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.order-book-stats {
    display: flex;
    gap: 16px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
}

.stat-label {
    color: #6b7280;
}

.stat-value {
    font-weight: 600;
    color: #111827;
}

.stat-value.positive {
    color: #10b981;
}

.stat-value.negative {
    color: #ef4444;
}

/* Order Book Content */
.order-book-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    flex: 1;
    min-height: 0;
}

.order-book-side {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.order-book-side-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 8px;
}

.order-book-side-header h5 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.side-total {
    font-size: 12px;
    color: #6b7280;
}

/* Order Book Tables */
.order-book-table-container {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.order-book-table-header {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    padding: 8px 12px;
    background: #f9fafb;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
}

.order-book-table-body {
    flex: 1;
    overflow-y: auto;
    max-height: 400px;
}

.order-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    padding: 8px 12px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 13px;
    transition: all 0.2s;
}

.order-row:hover {
    background: #f9fafb;
}

.order-col {
    font-weight: 500;
    color: #374151;
}

.price-col {
    font-weight: 600;
}

.total-col {
    text-align: right;
}

/* Flash animation for new orders */
.order-flash {
    animation: flashGreen 0.5s ease-in-out;
}

@keyframes flashGreen {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(16, 185, 129, 0.1); }
}

/* Responsive */
@media (max-width: 1024px) {
    .order-book-content {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .order-book-table-body {
        max-height: 250px;
    }
}

@media (max-width: 768px) {
    .widget-content {
        padding: 16px;
        gap: 16px;
    }
    
    .order-book-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .order-book-stats {
        width: 100%;
        justify-content: space-between;
    }
    
    .symbol-card {
        min-width: 120px;
    }
}
</style>
