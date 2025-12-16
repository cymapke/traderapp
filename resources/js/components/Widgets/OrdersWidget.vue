<template>
    <div class="orders-widget widget-container">
        <div class="widget-header">
            <div class="widget-title">
                <i class="fas fa-clipboard-list widget-icon"></i>
                <h3>Orders</h3>
            </div>
            <div class="widget-actions">
                <button class="widget-action-btn" title="New Order">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="widget-action-btn" title="Filter">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
        
        <div class="widget-content">
            <!-- Orders Tabs -->
            <div class="orders-tabs">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    :class="['tab-btn', { active: activeTab === tab.id }]"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                    <span class="tab-count">{{ tab.count }}</span>
                </button>
            </div>
            
            <!-- Orders List -->
            <div class="orders-list">
                <div class="order-item" v-for="order in filteredOrders" :key="order.id">
                    <div class="order-info">
                        <div class="order-symbol">
                            <span class="symbol">{{ order.symbol }}</span>
                            <span :class="['order-type', order.type]">{{ order.type }}</span>
                        </div>
                        <div class="order-details">
                            <span class="order-quantity">{{ order.quantity }} @ ${{ order.price }}</span>
                        </div>
                    </div>
                    <div class="order-status">
                        <span :class="['status-badge', order.status]">{{ order.status }}</span>
                        <div class="order-value">${{ order.value.toLocaleString() }}</div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-if="filteredOrders.length === 0" class="empty-state">
                    <i class="fas fa-inbox empty-icon"></i>
                    <p>No {{ activeTab }} orders</p>
                </div>
            </div>
            
            <!-- Order Stats -->
            <div class="order-stats">
                <div class="stat-item">
                    <span class="stat-label">Open Orders</span>
                    <span class="stat-value">3</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Today's Volume</span>
                    <span class="stat-value">$42,580</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Win Rate</span>
                    <span class="stat-value positive">68%</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const activeTab = ref('open')

const tabs = [
    { id: 'open', label: 'Open', count: 3 },
    { id: 'pending', label: 'Pending', count: 2 },
    { id: 'completed', label: 'Completed', count: 12 },
    { id: 'cancelled', label: 'Cancelled', count: 5 }
]

// Sample orders data
const orders = ref([
    { id: 1, symbol: 'BTC/USD', type: 'buy', quantity: 0.5, price: '42,580', status: 'open', value: 21290 },
    { id: 2, symbol: 'ETH/USD', type: 'sell', quantity: 2.8, price: '2,150', status: 'open', value: 6020 },
    { id: 3, symbol: 'AAPL', type: 'buy', quantity: 10, price: '182.45', status: 'open', value: 1824.5 },
    { id: 4, symbol: 'MSFT', type: 'sell', quantity: 5, price: '385.20', status: 'pending', value: 1926 },
    { id: 5, symbol: 'GOOGL', type: 'buy', quantity: 3, price: '142.80', status: 'pending', value: 428.4 },
    { id: 6, symbol: 'TSLA', type: 'sell', quantity: 8, price: '210.75', status: 'completed', value: 1686 },
    { id: 7, symbol: 'NVDA', type: 'buy', quantity: 15, price: '125.40', status: 'completed', value: 1881 },
    { id: 8, symbol: 'AMZN', type: 'sell', quantity: 2, price: '178.90', status: 'cancelled', value: 357.8 }
])

const filteredOrders = computed(() => {
    return orders.value.filter(order => order.status === activeTab.value)
})
</script>

<style scoped>
.orders-widget {
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

/* Orders Tabs */
.orders-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 12px;
    flex-shrink: 0;
}

.tab-btn {
    padding: 8px 16px;
    border: 1px solid transparent;
    background: none;
    border-radius: 6px;
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.tab-btn:hover {
    background: #f3f4f6;
    color: #374151;
}

.tab-btn.active {
    background: #482e92;
    color: white;
    border-color: #482e92;
}

.tab-count {
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.2);
}

.tab-btn.active .tab-count {
    background: rgba(255, 255, 255, 0.3);
}

/* Orders List */
.orders-list {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    transition: all 0.2s;
}

.order-item:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.order-symbol {
    display: flex;
    align-items: center;
    gap: 8px;
}

.symbol {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.order-type {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.order-type.buy {
    background: #d1fae5;
    color: #065f46;
}

.order-type.sell {
    background: #fee2e2;
    color: #991b1b;
}

.order-details {
    font-size: 13px;
    color: #6b7280;
}

.order-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.status-badge {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.open {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.completed {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.cancelled {
    background: #f3f4f6;
    color: #6b7280;
}

.order-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

/* Empty State */
.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    padding: 40px 20px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Order Stats */
.order-stats {
    display: flex;
    gap: 16px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.stat-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-align: center;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
}

.stat-value {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.stat-value.positive {
    color: #065f46;
}

/* Responsive */
@media (max-width: 1024px) {
    .orders-tabs {
        flex-wrap: wrap;
    }
    
    .order-stats {
        flex-direction: column;
        gap: 12px;
    }
}

@media (max-width: 768px) {
    .widget-content {
        padding: 16px;
        gap: 16px;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .order-status {
        align-items: flex-start;
        width: 100%;
    }
}
</style>
