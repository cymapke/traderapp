<template>
    <div class="draggable-dashboard">
        <!-- Top Half -->
        <div class="dashboard-top" :style="{ height: topHeight + 'px' }">
            <!-- Left Widget (40%) -->
            <div class="widget-left">
                <WalletWidget />
            </div>
            
            <!-- Right Widget (60%) -->
            <div class="widget-right">
                <OrdersWidget />
            </div>
        </div>
        
        <!-- Draggable Divider -->
        <div 
            class="dashboard-divider" 
            @mousedown="startDrag"
            @touchstart="startDrag"
        >
            <div class="divider-handle">
                <i class="fas fa-grip-lines"></i>
            </div>
        </div>
        
        <!-- Bottom Half -->
        <div class="dashboard-bottom" :style="{ height: bottomHeight + 'px' }">
            <SymbolTrackerWidget />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import WalletWidget from '../Widgets/WalletWidget.vue'
import OrdersWidget from '../Widgets/OrdersWidget.vue'
import SymbolTrackerWidget from '../Widgets/SymbolTrackerWidget.vue'

const isDragging = ref(false)
const startY = ref(0)
const startTopHeight = ref(0)
const containerHeight = ref(0)

// Default heights (50% each)
const topHeight = ref(300)
const bottomHeight = ref(300)

// Minimum heights for widgets
const MIN_HEIGHT = 100

const startDrag = (e) => {
    isDragging.value = true
    startY.value = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY
    startTopHeight.value = topHeight.value
    
    document.addEventListener('mousemove', onDrag)
    document.addEventListener('touchmove', onDrag)
    document.addEventListener('mouseup', stopDrag)
    document.addEventListener('touchend', stopDrag)
    
    // Prevent text selection during drag
    document.body.style.userSelect = 'none'
    document.body.style.cursor = 'row-resize'
}

const onDrag = (e) => {
    if (!isDragging.value) return
    
    e.preventDefault()
    
    const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY
    const deltaY = clientY - startY.value
    
    // Calculate new height with constraints
    let newTopHeight = startTopHeight.value + deltaY
    
    // Ensure minimum heights
    if (newTopHeight < MIN_HEIGHT) newTopHeight = MIN_HEIGHT
    if (containerHeight.value - newTopHeight - 20 < MIN_HEIGHT) {
        newTopHeight = containerHeight.value - MIN_HEIGHT - 20
    }
    
    topHeight.value = newTopHeight
    bottomHeight.value = containerHeight.value - newTopHeight - 20 // 20px for divider
}

const stopDrag = () => {
    isDragging.value = false
    
    document.removeEventListener('mousemove', onDrag)
    document.removeEventListener('touchmove', onDrag)
    document.removeEventListener('mouseup', stopDrag)
    document.removeEventListener('touchend', stopDrag)
    
    // Restore cursor and selection
    document.body.style.userSelect = ''
    document.body.style.cursor = ''
}

const updateContainerHeight = () => {
    const container = document.querySelector('.main-widget')
    if (container) {
        containerHeight.value = container.clientHeight
        // Initialize with equal heights (minus divider)
        const availableHeight = containerHeight.value - 20
        topHeight.value = availableHeight * 0.5
        bottomHeight.value = availableHeight * 0.5
    }
}

onMounted(() => {
    updateContainerHeight()
    window.addEventListener('resize', updateContainerHeight)
})

onUnmounted(() => {
    window.removeEventListener('resize', updateContainerHeight)
})
</script>

<style scoped>
.draggable-dashboard {
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 0;
    overflow: hidden;
}

.dashboard-top {
    display: flex;
    gap: 20px;
    min-height: v-bind('MIN_HEIGHT + "px"');
    overflow: hidden;
}

.widget-left {
    flex: 0 0 40%;
    min-width: 0;
}

.widget-right {
    flex: 0 0 60%;
    min-width: 0;
}

.dashboard-divider {
    height: 20px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    cursor: row-resize;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    user-select: none;
    flex-shrink: 0;
}

.dashboard-divider:hover {
    background: #f3f4f6;
}

.dashboard-divider:active {
    background: #e5e7eb;
}

.divider-handle {
    color: #9ca3af;
    font-size: 12px;
}

.divider-handle i {
    transform: rotate(90deg);
}

.dashboard-bottom {
    min-height: v-bind('MIN_HEIGHT + "px"');
    overflow: hidden;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .dashboard-top {
        flex-direction: column;
        gap: 20px;
    }
    
    .widget-left,
    .widget-right {
        flex: 1;
    }
}

@media (max-width: 768px) {
    .dashboard-top {
        gap: 16px;
    }
}
</style>
