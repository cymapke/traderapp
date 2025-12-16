<template>
    <div class="orders-view">
        <!-- Order Form Section -->
        <div class="order-form-section">
            <OrderForm />
        </div>
    </div>
</template>

<script setup>
import OrderForm from '@/components/Dashboard/OrderForm.vue'
</script>

<style scoped>
.orders-view {
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 24px;
    overflow: hidden;
}

.order-form-section {
    flex: 1;
    min-height: 0;
}

.recent-orders-section {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 24px;
    flex-shrink: 0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h3 i {
    color: #482e92;
}

.view-all-btn {
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.view-all-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.recent-orders {
    min-height: 200px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: #9ca3af;
    text-align: center;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    margin: 4px 0;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .orders-view {
        gap: 16px;
    }
    
    .order-form-section {
        min-height: 500px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .view-all-btn {
        align-self: flex-end;
    }
}
</style>
