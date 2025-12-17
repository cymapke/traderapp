<template>
  <div class="wallet-widget">
    <!-- Header with title and refresh button -->
    <div class="widget-header">
      <div class="header-left">
        <span class="icon">💰</span>
        <h3>Wallet Balance</h3>
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

    <!-- Top Row: Total Balance, Cash, Assets -->
    <div class="top-row">
      <!-- Total Balance Card -->
      <div class="total-balance-card" :class="{ 'pulse': isUpdating }">
        <div class="balance-label">Total Balance</div>
        <div class="balance-amount">{{ formatMoney(profileData.total_balance) }}</div>
        <div class="balance-subtext">Portfolio Value</div>
      </div>

      <!-- Cash & Assets Stack -->
      <div class="cash-assets-stack">
        <div class="cash-card stat-card">
          <div class="stat-content">
            <span class="stat-icon">💵</span>
            <div class="stat-details">
              <div class="stat-label">Cash</div>
              <div class="stat-value">{{ formatCompact(profileData.cash_balance) }}</div>
              <div class="stat-subtext">Available Funds</div>
            </div>
          </div>
        </div>

        <div class="assets-card stat-card">
          <div class="stat-content">
            <span class="stat-icon">📊</span>
            <div class="stat-details">
              <div class="stat-label">Assets</div>
              <div class="stat-value">{{ formatCompact(profileData.assets_value) }}</div>
              <div class="stat-subtext">Market Value</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Row: Available % and In Orders -->
    <div class="bottom-row">
      <div class="available-card stat-card">
        <div class="stat-content">
          <span class="stat-icon">✅</span>
          <div class="stat-details">
            <div class="stat-label">Available</div>
            <div class="available-details">
              <div class="available-amount">{{ formatCompact(getAvailableAmount()) }}</div>
              <div class="percentage-value">{{ profileData.available_percentage }}%</div>
            </div>
            <div class="percentage-bar">
              <div 
                class="percentage-fill" 
                :style="{ width: profileData.available_percentage + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <div class="orders-card stat-card">
        <div class="stat-content">
          <span class="stat-icon">⏳</span>
          <div class="stat-details">
            <div class="stat-label">In Orders</div>
            <div class="stat-value">{{ formatCompact(profileData.locked_assets_value) }}</div>
            <div class="stat-subtext">Locked Funds</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status & Last Updated -->
    <div class="widget-footer">
      <div class="update-time">
        <span class="icon">🕒</span>
        <span class="time-text">Updated {{ formatTimeAgo(lastUpdate) }}</span>
      </div>
      <div class="status-badge" :class="getStatusClass()">
        {{ getStatusText() }}
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <div class="spinner-dot"></div>
        <div class="spinner-dot"></div>
        <div class="spinner-dot"></div>
      </div>
      <span class="loading-text">Updating...</span>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export default {
  name: 'WalletWidget',
  
  setup() {
    const profileData = ref({
      total_balance: 0,
      cash_balance: 0,
      assets_value: 0,
      locked_assets_value: 0,
      available_percentage: 0
    });
    
    const loading = ref(false);
    const isUpdating = ref(false);
    const lastUpdate = ref(null);
    let updateTimeout = null;
    let pollInterval = null;

    // Calculate available amount
    const getAvailableAmount = () => {
      return profileData.value.assets_value - profileData.value.locked_assets_value;
    };

    // Fetch profile data
    const fetchProfileData = async () => {
      loading.value = true;
      try {
        const token = localStorage.getItem('token');
        
        if (!token) {
          console.warn('No authentication token found');
          loading.value = false;
          return;
        }

        const response = await axios.get('/api/profile', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.data.success) {
          updateProfileData(response.data.data);
          lastUpdate.value = new Date();
          triggerUpdateAnimation();
        }
      } catch (error) {
        console.error('Error fetching profile data:', error);
      } finally {
        loading.value = false;
      }
    };

    // Update profile data
    const updateProfileData = (newData) => {
      profileData.value = {
        ...profileData.value,
        ...newData
      };
    };

    // Trigger update animation
    const triggerUpdateAnimation = () => {
      isUpdating.value = true;
      if (updateTimeout) clearTimeout(updateTimeout);
      updateTimeout = setTimeout(() => {
        isUpdating.value = false;
      }, 1000);
    };

    // Manual refresh
    const refreshProfile = async () => {
      triggerUpdateAnimation();
      await fetchProfileData();
    };

    // Format money
    const formatMoney = (amount) => {
      if (!amount && amount !== 0) return '$0';
      
      if (amount >= 1000000) {
        return '$' + (amount / 1000000).toFixed(1) + 'M';
      } else if (amount >= 1000) {
        return '$' + (amount / 1000).toFixed(1) + 'K';
      }
      return '$' + amount.toFixed(0);
    };

    // Format compact numbers
    const formatCompact = (amount) => {
      if (!amount && amount !== 0) return '0';
      
      if (amount >= 1000000) {
        return (amount / 1000000).toFixed(1) + 'M';
      } else if (amount >= 1000) {
        return (amount / 1000).toFixed(1) + 'K';
      }
      return amount.toFixed(0);
    };

    // Format time ago
    const formatTimeAgo = (date) => {
    console.log(date)
      if (!date) return 'just now';
      
      const seconds = Math.floor((new Date() - date) / 1000);
      
      if (seconds < 60) return 'just now';
      if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
      if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
      return Math.floor(seconds / 86400) + 'd ago';
    };

    // Get status class
    const getStatusClass = () => {
      if (profileData.value.total_balance === 0) return 'status-empty';
      if (profileData.value.available_percentage > 75) return 'status-good';
      if (profileData.value.available_percentage > 50) return 'status-ok';
      return 'status-low';
    };

    // Get status text
    const getStatusText = () => {
      if (profileData.value.total_balance === 0) return 'No Funds';
      if (profileData.value.available_percentage > 75) return 'Healthy';
      if (profileData.value.available_percentage > 50) return 'Moderate';
      return 'Limited';
    };

    onMounted(() => {
      fetchProfileData();
      
      // Auto-refresh every 5 minutes (300,000 ms)
      pollInterval = setInterval(fetchProfileData, 300000);
      
      onUnmounted(() => {
        if (updateTimeout) clearTimeout(updateTimeout);
        if (pollInterval) clearInterval(pollInterval);
      });
    });

    return {
      profileData,
      loading,
      isUpdating,
      lastUpdate,
      getAvailableAmount,
      fetchProfileData,
      refreshProfile,
      formatMoney,
      formatCompact,
      formatTimeAgo,
      getStatusClass,
      getStatusText
    };
  }
};
</script>

<style scoped>
.wallet-widget {
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

/* Total Balance Card */
.total-balance-card {
  flex: 1;
  background: linear-gradient(135deg, #f8f8fc 0%, #f0f0f5 100%);
  border-radius: 8px;
  padding: 14px;
  border: 1px solid #e6e6f0;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.total-balance-card.pulse {
  animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.9; }
}

.balance-label {
  font-size: 11px;
  color: #7877c6;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  font-weight: 600;
  margin-bottom: 4px;
  opacity: 0.8;
}

.balance-amount {
  font-size: 28px;
  font-weight: 700;
  color: #5d5d8a;
  line-height: 1;
  margin-bottom: 2px;
  background: linear-gradient(90deg, #7877c6, #5d5d8a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.balance-subtext {
  font-size: 10px;
  color: #999;
  opacity: 0.7;
}

/* Cash & Assets Stack */
.cash-assets-stack {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Stat Cards with Horizontal Layout */
.stat-card {
  background: #fafafc;
  border: 1px solid #f0f0f5;
  border-radius: 6px;
  padding: 10px;
  transition: all 0.2s ease;
  height: 68px;
  display: flex;
  align-items: center;
}

.stat-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(120, 119, 198, 0.1);
  border-color: #e0e0f0;
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.stat-icon {
  font-size: 24px;
  width: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-details {
  flex: 1;
}

.stat-label {
  font-size: 11px;
  color: #666;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 18px;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 2px;
}

.stat-subtext {
  font-size: 10px;
  color: #999;
  opacity: 0.7;
  white-space: nowrap;
}

/* Specific Card Colors */
.cash-card .stat-value { color: #4caf50; } /* Green */
.assets-card .stat-value { color: #2196f3; } /* Blue */
.orders-card .stat-value { color: #ff9800; } /* Orange */

/* Bottom Row */
.bottom-row {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}

.available-card, .orders-card {
  flex: 1;
}

/* Available Card Specific Styles */
.available-details {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin: 2px 0 8px 0;
  gap: 8px;
}

.available-amount {
  font-size: 18px;
  font-weight: 700;
  color: #482e92;
  line-height: 1;
  flex: 1;
}

.percentage-value {
  font-size: 16px;
  font-weight: 600;
  color: #482e92;
  line-height: 1;
  text-align: right;
  min-width: 45px;
}

.percentage-bar {
  height: 4px;
  background: #e6e6f0;
  border-radius: 2px;
  overflow: hidden;
  margin-top: 4px;
}

.percentage-fill {
  height: 100%;
  background: linear-gradient(90deg, #482e92, #3a2374);
  border-radius: 2px;
  transition: width 0.5s ease;
}

/* Footer */
.widget-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px solid #f0f0f5;
}

.update-time {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  color: #999;
}

.time-text {
  opacity: 0.8;
}

.status-badge {
  font-size: 10px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 10px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.status-good {
  background: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #c8e6c9;
}

.status-ok {
  background: #fff3e0;
  color: #f57c00;
  border: 1px solid #ffe0b2;
}

.status-low {
  background: #ffebee;
  color: #d32f2f;
  border: 1px solid #ffcdd2;
}

.status-empty {
  background: #f5f5f5;
  color: #757575;
  border: 1px solid #e0e0e0;
}

/* Loading Overlay */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  z-index: 10;
}

.loading-spinner {
  display: flex;
  gap: 3px;
  margin-bottom: 8px;
}

.spinner-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #7877c6;
  animation: bounce 1.4s infinite ease-in-out both;
}

.spinner-dot:nth-child(1) { animation-delay: -0.32s; }
.spinner-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
  40% { transform: scale(1); opacity: 1; }
}

.loading-text {
  font-size: 12px;
  color: #7877c6;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .wallet-widget {
    padding: 12px;
  }
  
  .top-row {
    flex-direction: column;
    gap: 8px;
  }
  
  .cash-assets-stack {
    flex-direction: row;
    gap: 8px;
  }
  
  .bottom-row {
    flex-direction: column;
    gap: 8px;
  }
  
  .balance-amount {
    font-size: 24px;
  }
  
  .stat-value, .available-amount {
    font-size: 16px;
  }
  
  .percentage-value {
    font-size: 14px;
  }
  
  .stat-icon {
    font-size: 20px;
    width: 32px;
  }
  
  .stat-content {
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .cash-assets-stack {
    flex-direction: column;
  }
  
  .widget-footer {
    flex-direction: column;
    gap: 6px;
    align-items: flex-start;
  }
  
  .status-badge {
    align-self: flex-start;
  }
  
  .available-details {
    flex-direction: column;
    gap: 2px;
    align-items: flex-start;
  }
  
  .percentage-value {
    text-align: left;
  }
}
</style>
