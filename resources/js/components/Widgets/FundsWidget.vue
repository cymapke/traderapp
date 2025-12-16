<template>
    <div class="funds-widget widget-container">
        <div class="widget-header">
            <div class="widget-title">
                <i class="fas fa-credit-card widget-icon"></i>
                <h3>Funds Management</h3>
            </div>
            <div class="widget-actions">
                <button class="widget-action-btn" title="Refresh" @click="refreshData">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        
        <div class="widget-content">
            <!-- Card on File -->
            <div class="card-on-file">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-credit-card"></i>
                        Card on File
                    </h4>
                    <button class="card-action-btn" @click="showCardModal = true">
                        <i class="fas fa-edit"></i> Update
                    </button>
                </div>
                <div class="card-details">
                    <div class="card-brand">
                        <i class="fab fa-cc-visa"></i>
                        <span>Visa</span>
                    </div>
                    <div class="card-number">
                        <span class="card-label">Card Number</span>
                        <span class="card-value">66 •••• •••• 7890</span>
                    </div>
                    <div class="card-expiry">
                        <span class="card-label">Expires</span>
                        <span class="card-value">12/2026</span>
                    </div>
                </div>
            </div>
            
            <!-- Deposit & Withdraw Forms -->
            <div class="funds-forms">
                <!-- Deposit Form -->
                <div class="fund-form deposit-form">
                    <div class="form-header">
                        <i class="fas fa-arrow-down"></i>
                        <h4>Deposit Funds</h4>
                    </div>
                    
                    <form @submit.prevent="handleDeposit" class="fund-form-content">
                        <div class="form-group">
                            <label class="form-label">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input
                                    type="number"
                                    v-model="depositForm.amount"
                                    class="form-input"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="1"
                                    max="10000"
                                    required
                                />
                            </div>
                            <div class="quick-amounts">
                                <button 
                                    type="button" 
                                    v-for="amount in quickAmounts" 
                                    :key="amount"
                                    class="quick-amount-btn"
                                    :class="{ active: depositForm.amount == amount }"
                                    @click="depositForm.amount = amount"
                                >
                                    ${{ amount }}
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Funding Source</label>
                            <div class="source-selector">
                                <div class="source-option" :class="{ active: depositForm.source === 'card' }">
                                    <input 
                                        type="radio" 
                                        id="source-card" 
                                        v-model="depositForm.source" 
                                        value="card"
                                        class="radio-input"
                                    />
                                    <label for="source-card" class="source-label">
                                        <i class="fas fa-credit-card"></i>
                                        <div class="source-info">
                                            <span class="source-name">Card on File</span>
                                            <span class="source-details">Visa ending in 7890</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="source-option" :class="{ active: depositForm.source === 'bank' }">
                                    <input 
                                        type="radio" 
                                        id="source-bank" 
                                        v-model="depositForm.source" 
                                        value="bank"
                                        class="radio-input"
                                    />
                                    <label for="source-bank" class="source-label">
                                        <i class="fas fa-university"></i>
                                        <div class="source-info">
                                            <span class="source-name">Bank Transfer</span>
                                            <span class="source-details">1-3 business days</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-summary">
                            <div class="summary-item">
                                <span class="summary-label">Deposit Amount</span>
                                <span class="summary-value">${{ depositForm.amount || '0.00' }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Processing Fee</span>
                                <span class="summary-value">${{ depositFee.toFixed(2) }}</span>
                            </div>
                            <div class="summary-item total">
                                <span class="summary-label">Total to Credit</span>
                                <span class="summary-value">${{ totalDeposit.toFixed(2) }}</span>
                            </div>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="submit-btn deposit-btn"
                            :disabled="isDepositing || !isDepositValid"
                        >
                            <i class="fas fa-arrow-down"></i>
                            {{ isDepositing ? 'Processing...' : 'Deposit Funds' }}
                        </button>
                        
                        <div class="form-note">
                            <i class="fas fa-info-circle"></i>
                            Funds are typically available within 1-2 minutes
                        </div>
                    </form>
                </div>
                
                <!-- Withdraw Form -->
                <div class="fund-form withdraw-form">
                    <div class="form-header">
                        <i class="fas fa-arrow-up"></i>
                        <h4>Withdraw Funds</h4>
                    </div>
                    
                    <form @submit.prevent="handleWithdraw" class="fund-form-content">
                        <div class="form-group">
                            <label class="form-label">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input
                                    type="number"
                                    v-model="withdrawForm.amount"
                                    class="form-input"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="10"
                                    :max="availableBalance"
                                    required
                                />
                            </div>
                            <div class="balance-info">
                                <span class="balance-label">Available Balance</span>
                                <span class="balance-amount">${{ availableBalance.toLocaleString() }}</span>
                                <button 
                                    type="button" 
                                    class="max-btn"
                                    @click="withdrawForm.amount = availableBalance"
                                >
                                    Max
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Destination</label>
                            <div class="destination-selector">
                                <div class="destination-option" :class="{ active: withdrawForm.destination === 'card' }">
                                    <input 
                                        type="radio" 
                                        id="dest-card" 
                                        v-model="withdrawForm.destination" 
                                        value="card"
                                        class="radio-input"
                                    />
                                    <label for="dest-card" class="destination-label">
                                        <i class="fas fa-credit-card"></i>
                                        <div class="destination-info">
                                            <span class="destination-name">Card on File</span>
                                            <span class="destination-details">Visa ending in 7890</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="destination-option" :class="{ active: withdrawForm.destination === 'bank' }">
                                    <input 
                                        type="radio" 
                                        id="dest-bank" 
                                        v-model="withdrawForm.destination" 
                                        value="bank"
                                        class="radio-input"
                                    />
                                    <label for="dest-bank" class="destination-label">
                                        <i class="fas fa-university"></i>
                                        <div class="destination-info">
                                            <span class="destination-name">Bank Account</span>
                                            <span class="destination-details">•••• 4321 (Checking)</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-summary">
                            <div class="summary-item">
                                <span class="summary-label">Withdrawal Amount</span>
                                <span class="summary-value">${{ withdrawForm.amount || '0.00' }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Processing Fee</span>
                                <span class="summary-value">${{ withdrawFee.toFixed(2) }}</span>
                            </div>
                            <div class="summary-item total">
                                <span class="summary-label">Total to Receive</span>
                                <span class="summary-value">${{ totalWithdraw.toFixed(2) }}</span>
                            </div>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="submit-btn withdraw-btn"
                            :disabled="isWithdrawing || !isWithdrawValid"
                        >
                            <i class="fas fa-arrow-up"></i>
                            {{ isWithdrawing ? 'Processing...' : 'Withdraw Funds' }}
                        </button>
                        
                        <div class="form-note">
                            <i class="fas fa-clock"></i>
                            Withdrawals take 2-5 business days to process
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="recent-transactions">
                <div class="section-header">
                    <h4>
                        <i class="fas fa-history"></i>
                        Recent Transactions
                    </h4>
                    <router-link to="/dashboard/transactions" class="view-all-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </router-link>
                </div>
                
                <div class="transactions-list">
                    <div 
                        v-for="transaction in recentTransactions" 
                        :key="transaction.id"
                        class="transaction-item"
                        :class="transaction.type"
                    >
                        <div class="transaction-icon">
                            <i :class="getTransactionIcon(transaction)"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-info">
                                <span class="transaction-type">{{ transaction.type }}</span>
                                <span class="transaction-method">{{ transaction.method }}</span>
                            </div>
                            <div class="transaction-meta">
                                <span class="transaction-date">{{ formatDate(transaction.date) }}</span>
                                <span class="transaction-status" :class="transaction.status">
                                    {{ transaction.status }}
                                </span>
                            </div>
                        </div>
                        <div class="transaction-amount">
                            <span class="amount-value" :class="getAmountClass(transaction)">
                                ${{ Math.abs(transaction.amount).toLocaleString() }}
                            </span>
                            <span class="amount-id">#{{ transaction.id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card Modal -->
        <div v-if="showCardModal" class="modal-overlay" @click.self="showCardModal = false">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Update Card Details</h3>
                    <button class="modal-close" @click="showCardModal = false">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Card update form would appear here</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

// State
const showCardModal = ref(false)
const isDepositing = ref(false)
const isWithdrawing = ref(false)

// Available balance
const availableBalance = ref(18425.50)

// Deposit form
const depositForm = reactive({
    amount: '',
    source: 'card'
})

// Withdraw form
const withdrawForm = reactive({
    amount: '',
    destination: 'card'
})

// Quick amounts for deposit
const quickAmounts = [100, 500, 1000, 2000, 5000]

// Recent transactions
const recentTransactions = ref([
    {
        id: 'TRX001',
        type: 'deposit',
        method: 'Card ****7890',
        amount: 1000,
        date: '2024-01-15T10:30:00',
        status: 'completed'
    },
    {
        id: 'TRX002',
        type: 'withdrawal',
        method: 'Card ****7890',
        amount: -500,
        date: '2024-01-14T14:20:00',
        status: 'processing'
    },
    {
        id: 'TRX003',
        type: 'deposit',
        method: 'Bank Transfer',
        amount: 2000,
        date: '2024-01-12T09:15:00',
        status: 'completed'
    },
    {
        id: 'TRX004',
        type: 'withdrawal',
        method: 'Bank Account',
        amount: -1000,
        date: '2024-01-10T16:45:00',
        status: 'completed'
    },
    {
        id: 'TRX005',
        type: 'deposit',
        method: 'Card ****7890',
        amount: 750,
        date: '2024-01-08T11:20:00',
        status: 'completed'
    }
])

// Computed
const depositFee = computed(() => {
    const amount = parseFloat(depositForm.amount) || 0
    // 2.9% + $0.30 fee for card deposits
    return depositForm.source === 'card' ? (amount * 0.029) + 0.30 : 0
})

const withdrawFee = computed(() => {
    const amount = parseFloat(withdrawForm.amount) || 0
    // $25 flat fee for withdrawals
    return 25.00
})

const totalDeposit = computed(() => {
    const amount = parseFloat(depositForm.amount) || 0
    return amount - depositFee.value
})

const totalWithdraw = computed(() => {
    const amount = parseFloat(withdrawForm.amount) || 0
    return amount - withdrawFee.value
})

const isDepositValid = computed(() => {
    const amount = parseFloat(depositForm.amount)
    return amount >= 1 && amount <= 10000
})

const isWithdrawValid = computed(() => {
    const amount = parseFloat(withdrawForm.amount)
    return amount >= 10 && amount <= availableBalance.value
})

// Methods
const refreshData = () => {
    console.log('Refreshing funds data...')
}

const handleDeposit = async () => {
    if (!isDepositValid.value) return
    
    isDepositing.value = true
    
    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500))
        
        // Update balance
        const amount = parseFloat(depositForm.amount)
        availableBalance.value += amount
        
        // Add transaction
        const newTransaction = {
            id: 'TRX' + Math.floor(Math.random() * 10000),
            type: 'deposit',
            method: depositForm.source === 'card' ? 'Card ****7890' : 'Bank Transfer',
            amount: amount,
            date: new Date().toISOString(),
            status: 'completed'
        }
        
        recentTransactions.value.unshift(newTransaction)
        
        // Reset form
        depositForm.amount = ''
        
        alert(`Successfully deposited $${amount.toLocaleString()}`)
        
    } catch (error) {
        console.error('Deposit error:', error)
        alert('Deposit failed. Please try again.')
    } finally {
        isDepositing.value = false
    }
}

const handleWithdraw = async () => {
    if (!isWithdrawValid.value) return
    
    isWithdrawing.value = true
    
    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500))
        
        // Update balance
        const amount = parseFloat(withdrawForm.amount)
        availableBalance.value -= amount
        
        // Add transaction
        const newTransaction = {
            id: 'TRX' + Math.floor(Math.random() * 10000),
            type: 'withdrawal',
            method: withdrawForm.destination === 'card' ? 'Card ****7890' : 'Bank Account',
            amount: -amount,
            date: new Date().toISOString(),
            status: 'processing'
        }
        
        recentTransactions.value.unshift(newTransaction)
        
        // Reset form
        withdrawForm.amount = ''
        
        alert(`Withdrawal request for $${amount.toLocaleString()} submitted successfully.`)
        
    } catch (error) {
        console.error('Withdrawal error:', error)
        alert('Withdrawal failed. Please try again.')
    } finally {
        isWithdrawing.value = false
    }
}

const getTransactionIcon = (transaction) => {
    return transaction.type === 'deposit' ? 'fas fa-arrow-down' : 'fas fa-arrow-up'
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric'
    })
}

const getAmountClass = (transaction) => {
    return transaction.type === 'deposit' ? 'positive' : 'negative'
}
</script>

<style scoped>
.funds-widget {
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
    padding: 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Card on File */
.card-on-file {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 20px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.card-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header h4 i {
    color: #482e92;
}

.card-action-btn {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.card-action-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.card-details {
    display: flex;
    align-items: center;
    gap: 24px;
}

.card-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 20px;
    color: #1a1f71; /* Visa blue */
}

.card-brand span {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.card-number,
.card-expiry {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.card-label {
    font-size: 12px;
    color: #6b7280;
}

.card-value {
    font-size: 15px;
    font-weight: 600;
    color: #111827;
}

/* Funds Forms */
.funds-forms {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

@media (max-width: 1024px) {
    .funds-forms {
        grid-template-columns: 1fr;
    }
}

.fund-form {
    background: #f9fafb;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 20px;
}

.form-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.form-header i {
    font-size: 20px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.deposit-form .form-header i {
    background: #d1fae5;
    color: #065f46;
}

.withdraw-form .form-header i {
    background: #fee2e2;
    color: #991b1b;
}

.form-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.fund-form-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Form Groups */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.form-label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

/* Amount Input */
.amount-input {
    display: flex;
    align-items: center;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    overflow: hidden;
}

.currency-symbol {
    padding: 0 16px;
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
    height: 48px;
    display: flex;
    align-items: center;
    border-right: 1px solid #e5e7eb;
}

.form-input {
    flex: 1;
    padding: 12px 16px;
    border: none;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    outline: none;
}

.form-input::placeholder {
    color: #9ca3af;
}

/* Quick Amounts */
.quick-amounts {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.quick-amount-btn {
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

.quick-amount-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.quick-amount-btn.active {
    background: #482e92;
    color: white;
    border-color: #482e92;
}

/* Balance Info */
.balance-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
}

.balance-label {
    color: #6b7280;
}

.balance-amount {
    font-weight: 600;
    color: #111827;
}

.max-btn {
    padding: 4px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    color: #482e92;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.max-btn:hover {
    background: #f3f4f6;
    border-color: #482e92;
}

/* Source/Destination Selector */
.source-selector,
.destination-selector {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.source-option,
.destination-option {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    overflow: hidden;
    transition: all 0.2s;
}

.source-option.active,
.destination-option.active {
    border-color: #482e92;
    background: #f5f3ff;
}

.radio-input {
    display: none;
}

.source-label,
.destination-label {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    user-select: none;
}

.source-label i,
.destination-label i {
    font-size: 20px;
    color: #6b7280;
}

.source-option.active .source-label i,
.destination-option.active .destination-label i {
    color: #482e92;
}

.source-info,
.destination-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.source-name,
.destination-name {
    font-size: 14px;
    font-weight: 500;
    color: #111827;
}

.source-details,
.destination-details {
    font-size: 12px;
    color: #6b7280;
}

/* Form Summary */
.form-summary {
    background: white;
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
    border-top: 2px solid #e5e7eb;
}

.summary-label {
    font-size: 14px;
    color: #374151;
}

.summary-item.total .summary-label {
    font-weight: 600;
    color: #111827;
}

.summary-value {
    font-size: 15px;
    font-weight: 600;
    color: #111827;
}

/* Submit Buttons */
.submit-btn {
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.2s;
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.deposit-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.deposit-btn:not(:disabled):hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.withdraw-btn {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.withdraw-btn:not(:disabled):hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Form Note */
.form-note {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.form-note i {
    color: #482e92;
}

/* Recent Transactions */
.recent-transactions {
    border-top: 1px solid #e5e7eb;
    padding-top: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.section-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h4 i {
    color: #482e92;
}

.view-all-link {
    color: #482e92;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-all-link:hover {
    text-decoration: underline;
}

.transactions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.transaction-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    transition: all 0.2s;
}

.transaction-item:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.transaction-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.transaction-item.deposit .transaction-icon {
    background: #d1fae5;
    color: #065f46;
}

.transaction-item.withdrawal .transaction-icon {
    background: #fee2e2;
    color: #991b1b;
}

.transaction-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.transaction-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.transaction-type {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    text-transform: capitalize;
}

.transaction-method {
    font-size: 13px;
    color: #6b7280;
}

.transaction-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.transaction-date {
    font-size: 12px;
    color: #9ca3af;
}

.transaction-status {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.transaction-status.completed {
    background: #d1fae5;
    color: #065f46;
}

.transaction-status.processing {
    background: #fef3c7;
    color: #92400e;
}

.transaction-amount {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.amount-value {
    font-size: 16px;
    font-weight: 700;
}

.amount-value.positive {
    color: #065f46;
}

.amount-value.negative {
    color: #991b1b;
}

.amount-id {
    font-size: 12px;
    color: #6b7280;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    min-width: 400px;
    max-width: 90vw;
    max-height: 90vh;
    overflow: auto;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #111827;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: #f3f4f6;
    border-radius: 6px;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: #e5e7eb;
}

.modal-body {
    padding: 24px;
}

/* Responsive */
@media (max-width: 768px) {
    .widget-content {
        padding: 16px;
        gap: 16px;
    }
    
    .card-details {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .quick-amounts {
        justify-content: space-between;
    }
    
    .quick-amount-btn {
        flex: 1;
        text-align: center;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .view-all-link {
        align-self: flex-end;
    }
    
    .transaction-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .transaction-amount {
        align-items: flex-start;
        width: 100%;
    }
}
</style>
