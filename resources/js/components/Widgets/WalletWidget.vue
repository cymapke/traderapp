<template>
    <div class="wallet-widget widget-container">
        <div class="widget-header">
            <div class="widget-title">
                <i class="fas fa-wallet widget-icon"></i>
                <h3>Wallet</h3>
            </div>
            <div class="widget-actions">
                <button class="widget-action-btn" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        
        <div class="widget-content">
            <!-- Balance Overview -->
            <div class="balance-section">
                <div class="total-balance">
                    <span class="balance-label">Total Balance</span>
                    <span class="balance-amount">$25,430.75</span>
                    <span class="balance-change positive">+2.45%</span>
                </div>
                
                <div class="balance-breakdown">
                    <div class="balance-item">
                        <span class="item-label">Available</span>
                        <span class="item-value">$18,245.50</span>
                    </div>
                    <div class="balance-item">
                        <span class="item-label">In Orders</span>
                        <span class="item-value">$7,185.25</span>
                    </div>
                </div>
            </div>
            
            <!-- Currency Breakdown -->
            <div class="currencies-section">
                <h4 class="section-title">Currencies</h4>
                <div class="currencies-list">
                    <div class="currency-item">
                        <div class="currency-info">
                            <span class="currency-symbol">USD</span>
                            <span class="currency-name">US Dollar</span>
                        </div>
                        <div class="currency-balance">
                            <span class="currency-amount">$18,245.50</span>
                            <span class="currency-percentage">71.7%</span>
                        </div>
                    </div>
                    <div class="currency-item">
                        <div class="currency-info">
                            <span class="currency-symbol">BTC</span>
                            <span class="currency-name">Bitcoin</span>
                        </div>
                        <div class="currency-balance">
                            <span class="currency-amount">0.5 BTC</span>
                            <span class="currency-percentage">18.3%</span>
                        </div>
                    </div>
                    <div class="currency-item">
                        <div class="currency-info">
                            <span class="currency-symbol">ETH</span>
                            <span class="currency-name">Ethereum</span>
                        </div>
                        <div class="currency-balance">
                            <span class="currency-amount">2.8 ETH</span>
                            <span class="currency-percentage">10.0%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="actions-section">
                <button class="action-btn deposit">
                    <i class="fas fa-arrow-down"></i>
                    <span>Deposit</span>
                </button>
                <button class="action-btn withdraw">
                    <i class="fas fa-arrow-up"></i>
                    <span>Withdraw</span>
                </button>
                <button class="action-btn transfer">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transfer</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
// Wallet widget component
</script>

<style scoped>
.wallet-widget {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.widget-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.widget-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.widget-icon {
    color: #482e92;
    font-size: 18px;
}

.widget-title h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.widget-actions {
    display: flex;
    gap: 8px;
}

.widget-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background: white;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.widget-action-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.widget-content {
    flex: 1;
    padding: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Balance Section */
.balance-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.total-balance {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.balance-label {
    font-size: 14px;
    color: #6b7280;
}

.balance-amount {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
}

.balance-change {
    font-size: 14px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
}

.balance-change.positive {
    background: #d1fae5;
    color: #065f46;
}

.balance-breakdown {
    display: flex;
    gap: 20px;
}

.balance-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
}

.item-label {
    font-size: 13px;
    color: #6b7280;
}

.item-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

/* Currencies Section */
.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 12px 0;
}

.currencies-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.currency-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-radius: 8px;
    background: #f9fafb;
    border: 1px solid #f3f4f6;
}

.currency-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.currency-symbol {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}

.currency-name {
    font-size: 12px;
    color: #6b7280;
}

.currency-balance {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.currency-amount {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.currency-percentage {
    font-size: 12px;
    color: #6b7280;
}

/* Actions Section */
.actions-section {
    display: flex;
    gap: 12px;
    margin-top: auto;
}

.action-btn {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #374151;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.action-btn.deposit {
    color: #065f46;
    border-color: #a7f3d0;
    background: #ecfdf5;
}

.action-btn.deposit:hover {
    background: #d1fae5;
}

.action-btn.withdraw {
    color: #92400e;
    border-color: #fed7aa;
    background: #fffbeb;
}

.action-btn.withdraw:hover {
    background: #fef3c7;
}

.action-btn.transfer {
    color: #3730a3;
    border-color: #c7d2fe;
    background: #eef2ff;
}

.action-btn.transfer:hover {
    background: #e0e7ff;
}

/* Responsive */
@media (max-width: 1024px) {
    .balance-amount {
        font-size: 24px;
    }
    
    .balance-breakdown {
        flex-direction: column;
        gap: 12px;
    }
}

@media (max-width: 768px) {
    .widget-content {
        padding: 16px;
        gap: 16px;
    }
    
    .actions-section {
        flex-direction: column;
    }
}
</style>
