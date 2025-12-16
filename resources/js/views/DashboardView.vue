<template>
    <DashboardLayout>
        <!-- Dashboard Content -->
        <div class="dashboard-view">
            <!-- Welcome Header -->
            <div class="welcome-section">
                <h1 class="welcome-title">Welcome back, {{ user.name }}!</h1>
                <p class="welcome-subtitle">Here's what's happening with your trading account today.</p>
            </div>

            <!-- Dashboard Widgets Placeholder -->
            <div class="dashboard-widgets">
                <!-- Account Summary Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <h3 class="widget-title">Account Summary</h3>
                    </div>
                    <div class="widget-content">
                        <div class="summary-item">
                            <span class="summary-label">Balance</span>
                            <span class="summary-value">$25,430.75</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Today's P/L</span>
                            <span class="summary-value positive">+$425.50</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Open Orders</span>
                            <span class="summary-value">3</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <h3 class="widget-title">Recent Activity</h3>
                    </div>
                    <div class="widget-content">
                        <div class="activity-placeholder">
                            <p>Your recent trading activity will appear here.</p>
                            <p>Click on "Orders" in the sidebar to view and manage your trades.</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <h3 class="widget-title">Quick Actions</h3>
                    </div>
                    <div class="widget-content">
                        <div class="quick-actions">
                            <button class="action-button" @click="onNewOrder">
                                New Order
                            </button>
                            <button class="action-button" @click="onViewReports">
                                View Reports
                            </button>
                            <button class="action-button" @click="onMarketData">
                                Market Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/components/Layout/DashboardLayout.vue';

// Mock user data
const user = ref({
    name: 'John'
});

// Placeholder functions for dashboard actions
const onNewOrder = () => {
    console.log('New Order clicked - to be implemented');
};

const onViewReports = () => {
    console.log('View Reports clicked - to be implemented');
};

const onMarketData = () => {
    console.log('Market Data clicked - to be implemented');
};
</script>

<style scoped>
.dashboard-view {
    max-width: 1200px;
    margin: 0 auto;
}

/* Welcome Section */
.welcome-section {
    margin-bottom: 32px;
}

.welcome-title {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 8px;
}

.welcome-subtitle {
    font-size: 16px;
    color: #6b7280;
}

/* Dashboard Widgets */
.dashboard-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}

/* Widget Styles */
.widget {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.widget-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.widget-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.widget-content {
    padding: 24px;
}

/* Summary Items */
.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.summary-item:last-child {
    border-bottom: none;
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

.summary-value.positive {
    color: #10b981;
}

/* Activity Placeholder */
.activity-placeholder {
    text-align: center;
    padding: 20px 0;
    color: #9ca3af;
    font-size: 14px;
}

.activity-placeholder p {
    margin: 8px 0;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.action-button {
    padding: 12px 16px;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
}

.action-button:hover {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-color: #d1d5db;
    color: #111827;
}

.action-button:active {
    background: #e5e7eb;
}

/* Responsive Design */
@media (max-width: 768px) {
    .welcome-title {
        font-size: 24px;
    }
    
    .welcome-subtitle {
        font-size: 14px;
    }
    
    .dashboard-widgets {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .widget-header {
        padding: 16px 20px;
    }
    
    .widget-content {
        padding: 20px;
    }
    
    .widget-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .welcome-section {
        margin-bottom: 24px;
    }
    
    .welcome-title {
        font-size: 20px;
    }
    
    .summary-item {
        padding: 10px 0;
    }
    
    .summary-label {
        font-size: 13px;
    }
    
    .summary-value {
        font-size: 14px;
    }
}
</style>
