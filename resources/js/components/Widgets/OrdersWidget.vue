<template>
    <div class="orders-widget widget-container">
        <!-- Header -->
        <div class="widget-header">
            <div class="header-left">
                <span class="icon">📋</span>
                <h3>Orders</h3>
                <div v-if="symbolFilter" class="filter-badge">
                    {{ symbolFilter }}
                    <button @click="clearFilter" class="clear-filter">
                        <span class="icon">×</span>
                    </button>
                </div>
            </div>
            <div class="header-actions">
                <button 
                    @click="refreshOrders" 
                    class="action-btn" 
                    :class="{ 'refreshing': loading }"
                    :disabled="loading"
                    title="Refresh Orders"
                >
                    <span class="icon">↻</span>
                </button>
                <button @click="openNewOrder" class="action-btn" title="New Order">
                    <span class="icon">+</span>
                </button>
                <div class="symbol-filter">
                    <input
                        v-model="symbolInput"
                        @input="onSymbolInput"
                        placeholder="Filter symbol..."
                        class="filter-input"
                        :disabled="loading"
                    />
                    <button 
                        v-if="symbolInput" 
                        @click="clearSymbolInput" 
                        class="clear-input"
                    >
                        ×
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="orders-tabs">
            <button 
                v-for="tab in tabs" 
                :key="tab.id"
                :class="['tab-btn', { active: activeTab === tab.id }]"
                @click="activeTab = tab.id"
                :disabled="loading"
            >
                {{ tab.label }}
                <span class="tab-badge">{{ tab.count }}</span>
            </button>
        </div>
        
        <!-- Orders List -->
        <div class="orders-list">
            <!-- Loading -->
            <div v-if="loading && allOrders.length === 0" class="loading-state">
                <div class="spinner">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
                <span class="loading-text">Loading orders...</span>
            </div>
            
            <!-- Orders -->
            <div 
                v-for="order in displayedOrders" 
                :key="order.id"
                class="order-item"
                :class="[order.status_text.toLowerCase(), order.side.toLowerCase()]"
            >
                <div class="order-main">
                    <div class="order-symbol">
                        <span class="symbol">{{ order.symbol }}</span>
                        <span class="order-side" :class="order.side.toLowerCase()">
                            {{ order.side_icon }} {{ order.side }}
                        </span>
                    </div>
                    <div class="order-details">
                        <span class="order-amount">{{ order.formatted_amount }} @ {{ order.formatted_price }}</span>
                        <span class="order-time">{{ formatTime(order.opened_at) }}</span>
                    </div>
                </div>
                <div class="order-status">
                    <span class="status-badge" :class="order.status_text.toLowerCase()">
                        {{ order.status_text }}
                    </span>
                    <div class="order-value">{{ order.formatted_total }}</div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="!loading && displayedOrders.length === 0" class="empty-state">
                <span class="icon">📭</span>
                <p>No {{ activeTab }} orders{{ symbolFilter ? ' for ' + symbolFilter : '' }}</p>
                <button v-if="symbolFilter" @click="clearFilter" class="clear-btn">
                    Clear filter
                </button>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="order-stats">
            <div class="stat-item">
                <span class="stat-label">Open</span>
                <span class="stat-value">{{ stats.open_count }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Today</span>
                <span class="stat-value">${{ formatNumber(stats.today_volume) }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Win Rate</span>
                <span class="stat-value" :class="{ positive: stats.win_rate > 50 }">
                    {{ stats.win_rate }}%
                </span>
            </div>
        </div>
        
        <!-- Last Updated -->
        <div class="last-updated">
            <span class="icon">🕒</span>
            <span>Updated {{ lastUpdated ? formatTimeAgo(lastUpdated) : 'just now' }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

const router = useRouter()

// State
const activeTab = ref('open')
const symbolFilter = ref('')
const symbolInput = ref('')
const loading = ref(false)
const allOrders = ref([])
const lastUpdated = ref(null)
const pollInterval = ref(null)

// Status mapping
const statusMap = {
    1: 'open',
    2: 'filled', 
    3: 'cancelled'
}

// Tabs configuration
const tabs = computed(() => [
    { id: 'open', label: 'Open', count: openOrdersCount.value },
    { id: 'filled', label: 'Filled', count: filledOrdersCount.value },
    { id: 'cancelled', label: 'Cancelled', count: cancelledOrdersCount.value },
    { id: 'all', label: 'All', count: filteredOrdersCount.value }
])

// Computed - Filtered orders
const filteredOrders = computed(() => {
    let filtered = [...allOrders.value]
    
    // Filter by symbol
    if (symbolFilter.value) {
        filtered = filtered.filter(order => 
            order.symbol.toLowerCase().includes(symbolFilter.value.toLowerCase())
        )
    }
    
    return filtered
})

// Computed - Displayed orders (with tab filtering)
const displayedOrders = computed(() => {
    if (activeTab.value === 'all') {
        return filteredOrders.value.sort((a, b) => new Date(b.opened_at) - new Date(a.opened_at))
    }
    
    const statusNumber = Object.keys(statusMap).find(key => statusMap[key] === activeTab.value)
    if (!statusNumber) return []
    
    return filteredOrders.value
        .filter(order => order.status === parseInt(statusNumber))
        .sort((a, b) => new Date(b.opened_at) - new Date(a.opened_at))
})

// Computed - Tab counts (dynamic based on filtered results)
const filteredOrdersCount = computed(() => filteredOrders.value.length)
const openOrdersCount = computed(() => 
    filteredOrders.value.filter(order => order.status === 1).length
)
const filledOrdersCount = computed(() => 
    filteredOrders.value.filter(order => order.status === 2).length
)
const cancelledOrdersCount = computed(() => 
    filteredOrders.value.filter(order => order.status === 3).length
)

// Computed - Stats
const stats = computed(() => {
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    
    const filledOrders = filteredOrders.value.filter(order => order.status === 2)
    const todayFilled = filledOrders.filter(order => {
        if (!order.filled_at) return false
        const filledDate = new Date(order.filled_at)
        return filledDate >= today
    })
    
    // Simplified win rate
    const winningTrades = filledOrders.filter(order => order.side === 'SELL').length
    const winRate = filledOrders.length > 0 
        ? Math.round((winningTrades / filledOrders.length) * 100) 
        : 0
    
    return {
        open_count: openOrdersCount.value,
        filled_count: filledOrders.length,
        cancelled_count: cancelledOrdersCount.value,
        today_volume: todayFilled.reduce((sum, order) => sum + order.total, 0),
        win_rate: winRate
    }
})

// Methods
const fetchOrders = async () => {
    if (loading.value) return
    
    loading.value = true
    try {
        // Fetch ALL orders
        const response = await api.get('/orders')
        
        if (response.data.success) {
            allOrders.value = response.data.data
            lastUpdated.value = new Date()
            console.log(`Loaded ${allOrders.value.length} orders`)
        }
    } catch (error) {
        console.error('Error fetching orders:', error)
    } finally {
        loading.value = false
    }
}

const refreshOrders = async () => {
    await fetchOrders()
}

const onSymbolInput = () => {
    // Debounce the filter
    clearTimeout(symbolInput.debounce)
    symbolInput.debounce = setTimeout(() => {
        symbolFilter.value = symbolInput.value.toUpperCase().trim()
    }, 300)
}

const clearSymbolInput = () => {
    symbolInput.value = ''
    symbolFilter.value = ''
}

const clearFilter = () => {
    symbolInput.value = ''
    symbolFilter.value = ''
}

const openNewOrder = () => {
    router.push('/orders')
}

const formatTime = (timestamp) => {
    if (!timestamp) return ''
    
    const date = new Date(timestamp)
    const now = new Date()
    const diffMs = now - date
    const diffMins = Math.floor(diffMs / 60000)
    
    if (diffMins < 60) {
        return `${diffMins}m ago`
    } else if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    } else {
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' })
    }
}

const formatTimeAgo = (date) => {
    if (!date) return 'just now'
    
    const seconds = Math.floor((new Date() - date) / 1000)
    
    if (seconds < 60) return 'just now'
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago'
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago'
    return Math.floor(seconds / 86400) + 'd ago'
}

const formatNumber = (value) => {
    if (!value && value !== 0) return '0'
    
    if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + 'M'
    } else if (value >= 1000) {
        return (value / 1000).toFixed(1) + 'K'
    }
    return value.toFixed(0)
}

// Lifecycle
onMounted(() => {
    fetchOrders()
    
    // Poll every 30 seconds
    pollInterval.value = setInterval(fetchOrders, 30000)
})

onUnmounted(() => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value)
    }
})
</script>

<style scoped>
.last-updated {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 10px;
    color: #999;
    padding-top: 8px;
    border-top: 1px solid #f0f0f5;
    flex-shrink: 0;
}

/* Add refresh button animation */
.action-btn.refreshing .icon {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.orders-widget {
    background: white;
    border-radius: 10px;
    padding: 16px;
    box-shadow: 0 1px 8px rgba(120, 119, 198, 0.08);
    border: 1px solid #f0f0f5;
    position: relative;
    font-family: 'Inter', -apple-system, sans-serif;
    color: #333;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Header */
.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-shrink: 0;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.header-left .icon {
    font-size: 18px;
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

.filter-badge {
    background: #f0f0f5;
    border: 1px solid #e6e6f0;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 11px;
    color: #7877c6;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}

.clear-filter {
    background: none;
    border: none;
    padding: 0;
    font-size: 14px;
    color: #999;
    cursor: pointer;
    line-height: 1;
}

.clear-filter:hover {
    color: #666;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-btn {
    background: #7877c6;
    color: white;
    border: none;
    border-radius: 6px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #5d5d8a;
    transform: scale(1.1);
}

.symbol-filter {
    position: relative;
}

.filter-input {
    width: 120px;
    padding: 6px 28px 6px 10px;
    border: 1px solid #e6e6f0;
    border-radius: 6px;
    font-size: 12px;
    color: #333;
    background: #fafafc;
    transition: all 0.2s;
}

.filter-input:focus {
    outline: none;
    border-color: #7877c6;
    background: white;
    width: 140px;
}

.filter-input::placeholder {
    color: #999;
}

.clear-input {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    font-size: 16px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.clear-input:hover {
    color: #666;
}

/* Tabs */
.orders-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 12px;
    flex-shrink: 0;
    border-bottom: 1px solid #f0f0f5;
    padding-bottom: 8px;
}

.tab-btn {
    flex: 1;
    padding: 6px 8px;
    border: none;
    background: #fafafc;
    border-radius: 4px;
    color: #666;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.tab-btn:hover {
    background: #f0f0f5;
    color: #482e92;
}

.tab-btn.active {
    background: #482e92;
    color: white;
}

.tab-badge {
    font-size: 10px;
    padding: 1px 4px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.2);
    min-width: 18px;
    text-align: center;
}

.tab-btn.active .tab-badge {
    background: rgba(255, 255, 255, 0.3);
}

/* Orders List */
.orders-list {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

/* Loading State */
.loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    flex: 1;
}

.spinner {
    display: flex;
    gap: 3px;
}

.dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #7877c6;
    animation: bounce 1.4s infinite ease-in-out both;
}

.dot:nth-child(1) { animation-delay: -0.32s; }
.dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
    0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
    40% { transform: scale(1); opacity: 1; }
}

/* Order Item */
.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #f0f0f5;
    background: #fafafc;
    transition: all 0.2s;
    font-size: 12px;
}

.order-item:hover {
    background: #f5f5fa;
    border-color: #e6e6f0;
}

.order-item.buy {
    border-left: 2px solid #4caf50;
}

.order-item.sell {
    border-left: 2px solid #f44336;
}

.order-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.order-symbol {
    display: flex;
    align-items: center;
    gap: 6px;
}

.symbol {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

.order-side {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.order-side.buy {
    background: #e8f5e9;
    color: #2e7d32;
}

.order-side.sell {
    background: #ffebee;
    color: #c62828;
}

.order-details {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.order-amount {
    font-weight: 500;
}

.order-time {
    font-size: 10px;
    color: #999;
}

.order-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.status-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-badge.open {
    background: #e3f2fd;
    color: #1565c0;
}

.status-badge.filled {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.cancelled {
    background: #f5f5f5;
    color: #757575;
}

.order-value {
    font-size: 13px;
    font-weight: 700;
    color: #333;
}

/* Empty State */
.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    color: #999;
    text-align: center;
}

.empty-state .icon {
    font-size: 32px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0 0 8px 0;
    font-size: 12px;
}

.clear-btn {
    background: none;
    border: 1px solid #e6e6f0;
    border-radius: 4px;
    padding: 4px 12px;
    font-size: 11px;
    color: #7877c6;
    cursor: pointer;
    transition: all 0.2s;
}

.clear-btn:hover {
    background: #f0f0f5;
    border-color: #7877c6;
}

/* Stats */
.order-stats {
    display: flex;
    gap: 12px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f5;
    flex-shrink: 0;
}

.stat-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
    text-align: center;
}

.stat-label {
    font-size: 10px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.stat-value {
    font-size: 14px;
    font-weight: 700;
    color: #333;
}

.stat-value.positive {
    color: #4caf50;
}

/* Toast */
.toast {
    position: absolute;
    bottom: 16px;
    left: 16px;
    right: 16px;
    background: white;
    border-radius: 8px;
    padding: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #7877c6;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease-out;
    z-index: 100;
}

.toast.success {
    border-left-color: #4caf50;
}

.toast.error {
    border-left-color: #f44336;
}

.toast.warning {
    border-left-color: #ff9800;
}

.toast-icon {
    font-size: 20px;
}

.toast-content {
    flex: 1;
}

.toast-title {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
}

.toast-message {
    font-size: 12px;
    color: #666;
}

.toast-close {
    background: none;
    border: none;
    color: #999;
    font-size: 18px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.toast-close:hover {
    color: #666;
}

@keyframes slideIn {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .orders-widget {
        padding: 12px;
    }
    
    .widget-header {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    
    .header-actions {
        justify-content: space-between;
    }
    
    .filter-input {
        width: 100px;
    }
    
    .filter-input:focus {
        width: 120px;
    }
    
    .order-item {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    
    .order-status {
        align-items: flex-start;
        flex-direction: row;
        justify-content: space-between;
    }
    
    .order-stats {
        flex-wrap: wrap;
    }
    
    .stat-item {
        min-width: calc(33.333% - 8px);
    }
}

@media (max-width: 480px) {
    .orders-tabs {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        min-width: calc(50% - 2px);
    }
    
    .stat-item {
        min-width: calc(50% - 6px);
    }
}
</style>
