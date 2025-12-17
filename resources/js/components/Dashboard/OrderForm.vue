<template>
    <div class="order-form">
        <div class="form-header">
            <h2 class="form-title">
                <i class="fas fa-exchange-alt"></i>
                Place Order
            </h2>
            <p class="form-subtitle">Execute buy or sell orders for your preferred assets</p>
        </div>

        <!-- Quick Symbols - Shows top 6 popular for BUY or user's sellable holdings for SELL -->
        <div class="quick-tickers">
            <button 
                v-for="symbol in quickSymbols" 
                :key="symbol.symbol"
                :class="['ticker-btn', { active: form.symbol === symbol.symbol }]"
                @click="selectQuickSymbol(symbol)"
            >
                <span class="symbol">{{ symbol.symbol }}</span>
                <span v-if="form.type === 'sell'" class="holding-amount">
                    ({{ formatAmount(symbol.available) }})
                </span>
            </button>
        </div>

        <!-- Order Form -->
        <form @submit.prevent="placeOrder" class="order-form-content">
            <!-- Symbol Selection -->
            <div class="form-group">
                <label class="form-label">Symbol</label>
                <div class="symbol-input">
                    <input
                        type="text"
                        v-model="form.symbol"
                        class="form-input"
                        placeholder="Search symbol..."
                        @input="searchSymbols"
                        required
                    />
                    <button type="button" class="search-btn" @click="searchSymbols">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <!-- Search Results Dropdown -->
                <div v-if="showSymbolDropdown && filteredSymbols.length > 0" class="symbol-dropdown">
                    <div 
                        v-for="symbol in filteredSymbols" 
                        :key="symbol.symbol"
                        class="dropdown-item"
                        @click="selectSymbol(symbol)"
                    >
                        <span class="symbol">{{ symbol.symbol }}</span>
                        <span v-if="form.type === 'sell'" class="available">
                            Available: {{ formatAmount(symbol.available) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Type -->
            <div class="form-group">
                <label class="form-label">Order Type</label>
                <div class="order-type-selector">
                    <button
                        type="button"
                        :class="['type-btn', 'buy-btn', { active: form.type === 'buy' }]"
                        @click="setOrderType('buy')"
                    >
                        <i class="fas fa-arrow-up"></i>
                        Buy
                    </button>
                    <button
                        type="button"
                        :class="['type-btn', 'sell-btn', { active: form.type === 'sell' }]"
                        @click="setOrderType('sell')"
                    >
                        <i class="fas fa-arrow-down"></i>
                        Sell
                    </button>
                </div>
            </div>

            <!-- Order Details -->
            <div class="form-row">
                <!-- Quantity -->
                <div class="form-group">
                    <label class="form-label">
                        Quantity
                        <span v-if="form.type === 'sell' && selectedSymbolData" class="available-label">
                            Available: {{ formatAmount(selectedSymbolData.available) }}
                        </span>
                    </label>
                    <div class="input-with-buttons">
                        <input
                            type="number"
                            v-model="form.quantity"
                            class="form-input"
                            :placeholder="`0.${'0'.repeat(form.type === 'buy' ? 8 : 18)}`"
                            :step="form.type === 'buy' ? '0.00000001' : '0.000000000000000001'"
                            min="0"
                            required
                            @input="validateQuantity"
                        />
                        <div class="quantity-buttons">
                            <button type="button" class="qty-btn" @click="adjustQuantity(0.1)">+0.1</button>
                            <button type="button" class="qty-btn" @click="adjustQuantity(1)">+1</button>
                            <button type="button" class="qty-btn" @click="adjustQuantity(10)">+10</button>
                        </div>
                    </div>
                    <div v-if="quantityError" class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ quantityError }}
                    </div>
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label class="form-label">
                        Price (USD)
                        <span class="market-price" v-if="marketPrice">
                            Market: ${{ formatPrice(marketPrice) }}
                        </span>
                    </label>
                    <input
                        type="number"
                        v-model="form.price"
                        class="form-input"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        required
                    />
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-item">
                    <span class="summary-label">Order Value</span>
                    <span class="summary-value">${{ formatPrice(orderValue) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Fee (1.5%)</span>
                    <span class="summary-value">${{ formatPrice(orderFee) }}</span>
                </div>
                <div class="summary-item total">
                    <span class="summary-label">Total {{ form.type === 'buy' ? 'Cost' : 'Receive' }}</span>
                    <span class="summary-value">${{ formatPrice(totalAmount) }}</span>
                </div>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="submit-btn"
                :class="form.type"
                :disabled="isSubmitting || !isFormValid"
            >
                <i class="fas" :class="form.type === 'buy' ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                {{ isSubmitting ? 'Placing Order...' : `${form.type === 'buy' ? 'Buy' : 'Sell'} ${form.symbol || 'Symbol'}` }}
            </button>

            <!-- Form Status -->
            <div v-if="orderStatus" class="order-status" :class="orderStatus.type">
                <i class="fas" :class="orderStatus.icon"></i>
                <span>{{ orderStatus.message }}</span>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'

// Define all available symbols
const allSymbols = [
    'BTC',   // Bitcoin
    'ETH',   // Ethereum
    'XRP',   // Ripple
    'BNB',   // Binance Coin
    'ADA',   // Cardano
    'SOL',   // Solana
    'DOT',   // Polkadot
    'DOGE',  // Dogecoin
    'AVAX',  // Avalanche
    'LTC',   // Litecoin
    'MATIC', // Polygon
    'ATOM',  // Cosmos
    'LINK',  // Chainlink
    'UNI',   // Uniswap
    'XLM',   // Stellar
    'ETC',   // Ethereum Classic
    'ALGO',  // Algorand
    'VET',   // VeChain
    'XTZ',   // Tezos
    'FIL',   // Filecoin
]

// Popular symbols (top 6 for BUY)
const popularSymbols = ['BTC', 'ETH', 'SOL', 'ADA', 'DOT', 'XRP']

// Form state
const form = reactive({
    symbol: 'BTC',
    type: 'buy',
    quantity: 0.1,
    price: 42580,
})

// UI state
const isSubmitting = ref(false)
const orderStatus = ref(null)
const showSymbolDropdown = ref(false)
const quantityError = ref('')

// Data
const marketPrice = ref(42580)
const availableBalance = ref(18425.50)
const userHoldings = ref([]) // Will be populated from API
const filteredSymbols = ref([])

// Computed properties
const quickSymbols = computed(() => {
    if (form.type === 'buy') {
        // Return top 6 popular symbols with mock market prices
        return popularSymbols.map(symbol => ({
            symbol,
            name: getSymbolName(symbol),
            price: getMockPrice(symbol),
            available: 0 // Not applicable for buy
        }))
    } else {
        // Return user's sellable holdings (filtered where available > 0)
        const sellableHoldings = userHoldings.value
            .filter(h => h.available > 0)
            .sort((a, b) => b.value - a.value) // Sort by value descending
            .slice(0, 6) // Take top 6
        
        return sellableHoldings.map(h => ({
            symbol: h.symbol,
            name: getSymbolName(h.symbol),
            price: h.current_price,
            available: h.available
        }))
    }
})

const selectedSymbolData = computed(() => {
    if (form.type === 'buy') {
        return quickSymbols.value.find(s => s.symbol === form.symbol) || null
    } else {
        return userHoldings.value.find(h => h.symbol === form.symbol) || null
    }
})

const orderValue = computed(() => {
    return (form.quantity * form.price) || 0
})

const orderFee = computed(() => {
    return orderValue.value * 0.015 // 1.5% fee
})

const totalAmount = computed(() => {
    if (form.type === 'buy') {
        return orderValue.value + orderFee.value
    } else {
        return orderValue.value - orderFee.value
    }
})

const isFormValid = computed(() => {
    const hasSymbol = form.symbol && form.symbol.trim() !== ''
    const hasValidQuantity = form.quantity > 0 && !quantityError.value
    const hasValidPrice = form.price > 0
    
    if (form.type === 'buy') {
        // For buy: total amount must be <= available balance
        return hasSymbol && hasValidQuantity && hasValidPrice && totalAmount.value <= availableBalance.value
    } else {
        // For sell: quantity must be <= available amount
        const symbolData = selectedSymbolData.value
        return hasSymbol && hasValidQuantity && hasValidPrice && 
               symbolData && form.quantity <= symbolData.available
    }
})

// Methods
const getSymbolName = (symbol) => {
    const names = {
        'BTC': 'Bitcoin',
        'ETH': 'Ethereum',
        'XRP': 'Ripple',
        'BNB': 'Binance Coin',
        'ADA': 'Cardano',
        'SOL': 'Solana',
        'DOT': 'Polkadot',
        'DOGE': 'Dogecoin',
        'AVAX': 'Avalanche',
        'LTC': 'Litecoin',
        'MATIC': 'Polygon',
        'ATOM': 'Cosmos',
        'LINK': 'Chainlink',
        'UNI': 'Uniswap',
        'XLM': 'Stellar',
        'ETC': 'Ethereum Classic',
        'ALGO': 'Algorand',
        'VET': 'VeChain',
        'XTZ': 'Tezos',
        'FIL': 'Filecoin'
    }
    return names[symbol] || symbol
}

const getMockPrice = (symbol) => {
    const prices = {
        'BTC': 42580,
        'ETH': 2150,
        'XRP': 0.62,
        'BNB': 320,
        'ADA': 0.52,
        'SOL': 98,
        'DOT': 7.25,
        'DOGE': 0.15,
        'AVAX': 42,
        'LTC': 85,
        'MATIC': 0.85,
        'ATOM': 12,
        'LINK': 16,
        'UNI': 7.5,
        'XLM': 0.13,
        'ETC': 28,
        'ALGO': 0.18,
        'VET': 0.03,
        'XTZ': 1.05,
        'FIL': 5.8
    }
    return prices[symbol] || 100
}

const fetchUserHoldings = async () => {
    try {
        const response = await axios.get('/api/user/holdings')
        userHoldings.value = response.data.holdings || []
    } catch (error) {
        console.error('Error fetching user holdings:', error)
        // Mock data for development
        userHoldings.value = [
            { symbol: 'BTC', available: 0.5, current_price: 42580, value: 21290 },
            { symbol: 'ETH', available: 2.5, current_price: 2150, value: 5375 },
            { symbol: 'SOL', available: 15, current_price: 98, value: 1470 },
            { symbol: 'ADA', available: 1000, current_price: 0.52, value: 520 },
            { symbol: 'DOT', available: 50, current_price: 7.25, value: 362.5 },
            { symbol: 'XRP', available: 500, current_price: 0.62, value: 310 },
        ]
    }
}

const fetchAvailableBalance = async () => {
    try {
        const response = await axios.get('/api/user/balance')
        availableBalance.value = response.data.available_balance || 18425.50
    } catch (error) {
        console.error('Error fetching balance:', error)
        // Use default value
    }
}

const setOrderType = (type) => {
    form.type = type
    form.symbol = '' // Clear symbol when switching type
    form.quantity = 0.1
    quantityError.value = ''
    updateMarketPrice()
}

const selectQuickSymbol = (symbol) => {
    form.symbol = symbol.symbol
    form.price = symbol.price
    marketPrice.value = symbol.price
    showSymbolDropdown.value = false
    validateQuantity()
}

const searchSymbols = () => {
    const searchTerm = form.symbol.toLowerCase().trim()
    
    if (!searchTerm) {
        filteredSymbols.value = []
        showSymbolDropdown.value = false
        return
    }
    
    if (form.type === 'buy') {
        // For buy: show all symbols that match search
        filteredSymbols.value = allSymbols
            .filter(symbol => symbol.toLowerCase().includes(searchTerm))
            .map(symbol => ({
                symbol,
                name: getSymbolName(symbol),
                price: getMockPrice(symbol),
                available: 0
            }))
    } else {
        // For sell: only show user's holdings that match search
        filteredSymbols.value = userHoldings.value
            .filter(h => 
                h.symbol.toLowerCase().includes(searchTerm) && 
                h.available > 0
            )
            .map(h => ({
                symbol: h.symbol,
                name: getSymbolName(h.symbol),
                price: h.current_price,
                available: h.available
            }))
    }
    
    showSymbolDropdown.value = filteredSymbols.value.length > 0
}

const selectSymbol = (symbol) => {
    form.symbol = symbol.symbol
    form.price = symbol.price
    marketPrice.value = symbol.price
    showSymbolDropdown.value = false
    validateQuantity()
}

const adjustQuantity = (amount) => {
    form.quantity = parseFloat((parseFloat(form.quantity) + amount).toFixed(8))
    validateQuantity()
}

const validateQuantity = () => {
    quantityError.value = ''
    
    if (form.quantity <= 0) {
        quantityError.value = 'Quantity must be greater than 0'
        return
    }
    
    if (form.type === 'sell') {
        const symbolData = selectedSymbolData.value
        if (symbolData && form.quantity > symbolData.available) {
            quantityError.value = `Maximum available: ${formatAmount(symbolData.available)}`
        }
    }
    
    if (form.type === 'buy') {
        if (totalAmount.value > availableBalance.value) {
            quantityError.value = 'Insufficient balance'
        }
    }
}

const updateMarketPrice = () => {
    // Fetch current market price for selected symbol
    if (form.symbol) {
        marketPrice.value = getMockPrice(form.symbol)
        form.price = marketPrice.value
    }
}

const formatPrice = (price) => {
    if (!price) return '0.00'
    if (price < 1) return price.toFixed(6)
    if (price < 1000) return price.toFixed(2)
    return price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatAmount = (amount) => {
    if (!amount) return '0.00'
    if (amount < 0.000001) return amount.toFixed(12)
    if (amount < 0.001) return amount.toFixed(8)
    if (amount < 1) return amount.toFixed(6)
    if (amount < 1000) return amount.toFixed(2)
    return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const placeOrder = async () => {
    if (!isFormValid.value) {
        showStatus('error', 'Please fill all fields correctly')
        return
    }

    isSubmitting.value = true
    showStatus('info', 'Placing order...')

    try {
        const orderData = {
            symbol: form.symbol,
            side: form.type.toUpperCase(),
            price: parseFloat(form.price),
            amount: parseFloat(form.quantity),
            // Note: fee calculation is done client-side for display only
            // The backend will handle actual fee calculation and balance updates
        }

        const response = await axios.post('/api/orders', orderData)
        
        if (response.data.success) {
            // Show success toast
            showStatus('success', `Order #${response.data.order.id} placed successfully!`)
            
            // Show order summary
            const order = response.data.order
            const toastMessage = `
                ${order.side === 'BUY' ? '🟢 Buy' : '🔴 Sell'} Order Placed
                Symbol: ${order.symbol}
                Quantity: ${formatAmount(order.amount)}
                Price: $${formatPrice(order.price)}
                Total: $${formatPrice(order.amount * order.price)}
                Status: ${order.status_text}
            `
            
            // You can trigger a toast notification here
            // For now, we'll just show in the form status
            showStatus('success', toastMessage)
            
            // Reset form
            resetForm()
            
            // Refresh user data
            await Promise.all([
                fetchUserHoldings(),
                fetchAvailableBalance()
            ])
        } else {
            showStatus('error', response.data.message || 'Failed to place order')
        }
        
    } catch (error) {
        console.error('Order error:', error)
        const errorMessage = error.response?.data?.message || 'Failed to place order. Please try again.'
        showStatus('error', errorMessage)
    } finally {
        isSubmitting.value = false
    }
}

const resetForm = () => {
    const defaultSymbol = form.type === 'buy' ? 'BTC' : (quickSymbols.value[0]?.symbol || '')
    form.symbol = defaultSymbol
    form.quantity = 0.1
    updateMarketPrice()
    quantityError.value = ''
}

const showStatus = (type, message) => {
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle'
    }
    
    orderStatus.value = {
        type,
        icon: icons[type],
        message
    }
    
    // Clear status after 5 seconds
    setTimeout(() => {
        orderStatus.value = null
    }, 5000)
}

// Watch for changes
watch(() => form.symbol, (newSymbol) => {
    if (newSymbol) {
        updateMarketPrice()
        validateQuantity()
    }
})

watch(() => form.quantity, () => {
    validateQuantity()
})

watch(() => form.price, () => {
    validateQuantity()
})

watch(() => form.type, () => {
    // When order type changes, update quick symbols and reset form
    resetForm()
    searchSymbols()
})

// Initialize
onMounted(async () => {
    await Promise.all([
        fetchUserHoldings(),
        fetchAvailableBalance()
    ])
    resetForm()
})
</script>

<style scoped>
.order-form {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.form-header {
    padding: 24px;
    border-bottom: 1px solid #f3f4f6;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.form-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 700;
    color: #111827;
}

.form-title i {
    color: #482e92;
}

.form-subtitle {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

/* Quick Symbols */
.quick-tickers {
    padding: 16px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.ticker-btn {
    padding: 10px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.ticker-btn:hover {
    border-color: #9ca3af;
    background: #f9fafb;
}

.ticker-btn.active {
    background: #482e92;
    color: white;
    border-color: #482e92;
}

.ticker-btn.active .symbol {
    font-weight: 600;
}

.holding-amount {
    font-size: 12px;
    opacity: 0.9;
}

/* Order Form Content */
.order-form-content {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
}

.form-label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.market-price {
    font-size: 12px;
    color: #6b7280;
    font-weight: 400;
}

.available-label {
    font-size: 12px;
    color: #059669;
    font-weight: 500;
}

/* Symbol Input */
.symbol-input {
    display: flex;
    gap: 8px;
}

.form-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #482e92;
    box-shadow: 0 0 0 3px rgba(72, 46, 146, 0.1);
}

.search-btn {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.search-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

/* Symbol Dropdown */
.symbol-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-top: 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dropdown-item {
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: #f9fafb;
}

.dropdown-item .symbol {
    font-weight: 600;
    color: #111827;
}

.dropdown-item .available {
    font-size: 12px;
    color: #6b7280;
}

/* Order Type Selector */
.order-type-selector {
    display: flex;
    gap: 12px;
}

.type-btn {
    flex: 1;
    padding: 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.type-btn:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

.type-btn.active {
    border-width: 2px;
}

.buy-btn {
    border-color: #10b981;
}

.buy-btn.active {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.sell-btn {
    border-color: #ef4444;
}

.sell-btn.active {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

/* Input with buttons */
.input-with-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.quantity-buttons {
    display: flex;
    gap: 8px;
}

.qty-btn {
    flex: 1;
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    color: #374151;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

/* Error Message */
.error-message {
    font-size: 12px;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 4px;
}

.error-message i {
    font-size: 14px;
}

/* Order Summary */
.order-summary {
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 16px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.summary-item:not(:last-child) {
    border-bottom: 1px solid #e5e7eb;
}

.summary-item.total {
    padding-top: 12px;
    margin-top: 4px;
    border-top: 2px solid #e5e7eb;
}

.summary-label {
    font-size: 14px;
    color: #6b7280;
}

.summary-item.total .summary-label {
    font-weight: 600;
    color: #111827;
}

.summary-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.summary-item.total .summary-value {
    font-size: 18px;
    color: #482e92;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    transition: all 0.2s;
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.submit-btn.buy {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.submit-btn.buy:not(:disabled):hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.submit-btn.sell {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.submit-btn.sell:not(:disabled):hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Order Status */
.order-status {
    padding: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
}

.order-status.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.order-status.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.order-status.info {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}
</style>
