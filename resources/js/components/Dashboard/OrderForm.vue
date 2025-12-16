<template>
    <div class="order-form">
        <div class="form-header">
            <h2 class="form-title">
                <i class="fas fa-exchange-alt"></i>
                Place Order
            </h2>
            <p class="form-subtitle">Execute buy or sell orders for your preferred assets</p>
        </div>

        <!-- Quick Tickers -->
        <div class="quick-tickers">
            <button 
                v-for="ticker in quickTickers" 
                :key="ticker"
                :class="['ticker-btn', { active: form.symbol === ticker }]"
                @click="form.symbol = ticker"
            >
                {{ ticker }}
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
                        placeholder="e.g., BTC/USD, AAPL"
                        required
                    />
                    <button type="button" class="search-btn" @click="searchSymbol">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Order Type -->
            <div class="form-group">
                <label class="form-label">Order Type</label>
                <div class="order-type-selector">
                    <button
                        type="button"
                        :class="['type-btn', { active: form.type === 'buy' }]"
                        @click="form.type = 'buy'"
                    >
                        <i class="fas fa-arrow-up"></i>
                        Buy
                    </button>
                    <button
                        type="button"
                        :class="['type-btn', { active: form.type === 'sell' }]"
                        @click="form.type = 'sell'"
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
                    <label class="form-label">Quantity</label>
                    <div class="input-with-buttons">
                        <input
                            type="number"
                            v-model="form.quantity"
                            class="form-input"
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                            required
                        />
                        <div class="quantity-buttons">
                            <button type="button" class="qty-btn" @click="adjustQuantity(0.1)">+0.1</button>
                            <button type="button" class="qty-btn" @click="adjustQuantity(1)">+1</button>
                            <button type="button" class="qty-btn" @click="adjustQuantity(10)">+10</button>
                        </div>
                    </div>
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label class="form-label">
                        Price
                        <span class="market-price" v-if="marketPrice">
                            Market: ${{ marketPrice.toLocaleString() }}
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
                    <span class="summary-label">Total Cost</span>
                    <span class="summary-value">${{ totalCost.toLocaleString() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Available Balance</span>
                    <span class="summary-value">${{ availableBalance.toLocaleString() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Estimated Fee</span>
                    <span class="summary-value">${{ estimatedFee.toLocaleString() }}</span>
                </div>
            </div>

            <!-- Advanced Options -->
            <div class="advanced-options">
                <button type="button" class="advanced-toggle" @click="showAdvanced = !showAdvanced">
                    <i class="fas" :class="showAdvanced ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    Advanced Options
                </button>
                
                <div v-if="showAdvanced" class="advanced-content">
                    <div class="form-group">
                        <label class="form-label">Order Type</label>
                        <select v-model="form.orderType" class="form-select">
                            <option value="market">Market Order</option>
                            <option value="limit">Limit Order</option>
                            <option value="stop">Stop Order</option>
                            <option value="stop_limit">Stop-Limit Order</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Time in Force</label>
                        <select v-model="form.timeInForce" class="form-select">
                            <option value="day">Day</option>
                            <option value="gtc">Good Till Cancelled</option>
                            <option value="ioc">Immediate or Cancel</option>
                            <option value="fok">Fill or Kill</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" v-model="form.postOnly" class="checkbox" />
                            <span>Post Only</span>
                        </label>
                    </div>
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
import { ref, reactive, computed, watch } from 'vue'

// Quick ticker buttons
const quickTickers = ref(['BTC/USD', 'ETH/USD', 'AAPL', 'TSLA', 'MSFT', 'GOOGL'])

// Form state
const form = reactive({
    symbol: 'BTC/USD',
    type: 'buy',
    quantity: 0.1,
    price: 42580,
    orderType: 'limit',
    timeInForce: 'gtc',
    postOnly: false
})

// UI state
const showAdvanced = ref(false)
const isSubmitting = ref(false)
const orderStatus = ref(null)

// Market data (mock)
const marketPrice = ref(42580)
const availableBalance = ref(18425.50)

// Computed values
const totalCost = computed(() => {
    return (form.quantity * form.price) || 0
})

const estimatedFee = computed(() => {
    // 0.1% trading fee
    return totalCost.value * 0.001
})

const isFormValid = computed(() => {
    return form.symbol && 
           form.quantity > 0 && 
           form.price > 0 && 
           totalCost.value <= availableBalance.value
})

// Methods
const adjustQuantity = (amount) => {
    form.quantity = parseFloat((form.quantity + amount).toFixed(2))
}

const searchSymbol = () => {
    // Mock symbol search
    if (form.symbol) {
        const symbol = form.symbol.toUpperCase()
        // Mock market price based on symbol
        const mockPrices = {
            'BTC/USD': 42580,
            'ETH/USD': 2150,
            'AAPL': 182.45,
            'TSLA': 210.75,
            'MSFT': 385.20,
            'GOOGL': 142.80
        }
        
        if (mockPrices[symbol]) {
            form.price = mockPrices[symbol]
            marketPrice.value = mockPrices[symbol]
            showStatus('success', 'Symbol found and price updated')
        } else {
            showStatus('error', 'Symbol not found. Using default price.')
        }
    }
}

const placeOrder = async () => {
    if (!isFormValid.value) {
        showStatus('error', 'Please fill all fields correctly')
        return
    }

    isSubmitting.value = true
    showStatus('info', 'Placing order...')

    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500))
        
        // Mock order response
        const orderId = Math.floor(Math.random() * 1000000)
        
        showStatus('success', `Order #${orderId} placed successfully!`)
        
        // Reset form after successful order (keep symbol)
        const currentSymbol = form.symbol
        Object.assign(form, {
            symbol: currentSymbol,
            type: 'buy',
            quantity: 0.1,
            price: marketPrice.value,
            orderType: 'limit',
            timeInForce: 'gtc',
            postOnly: false
        })
        
    } catch (error) {
        console.error('Order error:', error)
        showStatus('error', 'Failed to place order. Please try again.')
    } finally {
        isSubmitting.value = false
    }
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

// Watch for symbol changes to update price
watch(() => form.symbol, (newSymbol) => {
    if (newSymbol) {
        searchSymbol()
    }
})

// Initialize
searchSymbol()
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

/* Quick Tickers */
.quick-tickers {
    padding: 16px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.ticker-btn {
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
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

.type-btn.active.buy {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

.type-btn.active.sell {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
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

.summary-label {
    font-size: 14px;
    color: #6b7280;
}

.summary-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

/* Advanced Options */
.advanced-options {
    border-top: 1px solid #e5e7eb;
    padding-top: 16px;
}

.advanced-toggle {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.advanced-toggle:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.advanced-content {
    margin-top: 16px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.form-select {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 14px;
    width: 100%;
    cursor: pointer;
}

.form-select:focus {
    outline: none;
    border-color: #482e92;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #374151;
}

.checkbox {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    cursor: pointer;
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
