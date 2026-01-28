// ============================================
// MODERN DASHBOARD - JAVASCRIPT
// Live timers, real-time updates, smooth animations
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Modern Dashboard initialized');
    
    // ========================================
    // LIVE TIMERS (Updates every second)
    // ========================================
    
    function updateLiveTimers() {
        const now = Math.floor(Date.now() / 1000);
        
        // Update order card timers
        document.querySelectorAll('.live-timer').forEach(timer => {
            const created = parseInt(timer.dataset.created);
            const seconds = now - created;
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            
            let timeStr;
            if (hours > 0) {
                timeStr = `${hours}h ${minutes % 60}m ago`;
            } else if (minutes > 0) {
                timeStr = `${minutes}m ago`;
            } else {
                timeStr = `${seconds}s ago`;
            }
            
            timer.textContent = timeStr;
            
            // Update urgency (optional - for dynamic color changes)
            const card = timer.closest('.order-card');
            if (card && minutes > 15) {
                card.classList.remove('fresh', 'warning');
                card.classList.add('urgent');
            } else if (card && minutes > 5) {
                card.classList.remove('fresh', 'urgent');
                card.classList.add('warning');
            }
        });
        
        // Update session duration timers
        document.querySelectorAll('.duration-cell .timer').forEach(timer => {
            const started = parseInt(timer.dataset.started);
            const duration = now - started;
            
            const hours = Math.floor(duration / 3600);
            const minutes = Math.floor((duration % 3600) / 60);
            const seconds = duration % 60;
            
            let timeStr = '';
            if (hours > 0) {
                timeStr = `${hours}h ${minutes}m`;
            } else if (minutes > 0) {
                timeStr = `${minutes}m ${seconds}s`;
            } else {
                timeStr = `${seconds}s`;
            }
            
            timer.textContent = timeStr;
        });
    }
    
    // Update timers every second
    setInterval(updateLiveTimers, 1000);
    updateLiveTimers(); // Initial call
    
    // ========================================
    // AUTO-REFRESH (Every 30 seconds)
    // ========================================
    
    let autoRefreshInterval;
    
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            console.log('🔄 Auto-refreshing dashboard...');
            if (window.Livewire) {
                // Livewire 3 syntax
                window.Livewire.dispatch('refreshDashboard');
            }
        }, 30000); // 30 seconds
    }
    
    startAutoRefresh();
    
    // Pause auto-refresh when user is inactive
    let isUserActive = true;
    let inactivityTimer;
    
    function resetInactivityTimer() {
        isUserActive = true;
        clearTimeout(inactivityTimer);
        
        inactivityTimer = setTimeout(() => {
            isUserActive = false;
            console.log('⏸️ User inactive - pausing auto-refresh');
            clearInterval(autoRefreshInterval);
        }, 300000); // 5 minutes
    }
    
    document.addEventListener('mousemove', resetInactivityTimer);
    document.addEventListener('keypress', resetInactivityTimer);
    document.addEventListener('click', resetInactivityTimer);
    
    resetInactivityTimer();
    
    // ========================================
    // REAL-TIME UPDATES (Pusher)
    // ========================================
    
    if (window.Echo && window.Pusher) {
        // Get IDs from meta tags or data attributes
        const outletId = document.querySelector('meta[name="outlet-id"]')?.content || 
                        document.querySelector('[data-outlet-id]')?.dataset.outletId;
        const tenantId = document.querySelector('meta[name="tenant-id"]')?.content ||
                        document.querySelector('[data-tenant-id]')?.dataset.tenantId;
        
        console.log('📡 Pusher - Listening for real-time updates...');
        console.log('Outlet:', outletId, 'Tenant:', tenantId);
        
        if (outletId && tenantId) {
            // Listen for new orders on dashboard channel
            window.Echo.private(`dashboard.${outletId}`)
                .listen('.order.new', (e) => {
                    console.log('🔔 New order received:', e);
                    
                    // Play notification sound
                    playNotificationSound();
                    
                    // Show toast
                    showToast('New Order! 🎉', `${e.order_number} from Table ${e.table_number}`, 'success');
                    
                    // FIXED: Use Livewire 3 dispatch
                    if (window.Livewire) {
                        window.Livewire.dispatch('refreshDashboard');
                    }
                    
                    // Pulse animation on new orders column
                    const pendingColumn = document.querySelector('.kanban-column.pending');
                    if (pendingColumn) {
                        pendingColumn.classList.add('pulse-highlight');
                        setTimeout(() => {
                            pendingColumn.classList.remove('pulse-highlight');
                        }, 2000);
                    }
                })
                .listen('.order.status.changed', (e) => {
                    console.log('📊 Order status changed:', e);
                    
                    showToast('Status Updated', `${e.order_number} → ${e.new_status}`, 'info');
                    
                    // Refresh dashboard
                    if (window.Livewire) {
                        window.Livewire.dispatch('refreshDashboard');
                    }
                })
                .listen('.session.created', (e) => {
                    console.log('📱 New session started:', e);
                    showToast('New Session', `Table ${e.table_number} started`, 'info');
                    if (window.Livewire) {
                        window.Livewire.dispatch('refreshDashboard');
                    }
                })
                .listen('.session.closed', (e) => {
                    console.log('📱 Session closed:', e);
                    showToast('Session Ended', `Table ${e.table_number} closed`, 'warning');
                    if (window.Livewire) {
                        window.Livewire.dispatch('refreshDashboard');
                    }
                });
                
            // Also listen on tenant.outlet channel (backup)
            window.Echo.private(`tenant.${tenantId}.outlet.${outletId}`)
                .listen('.order.status.changed', (e) => {
                    console.log('📊 [Tenant Channel] Order status changed:', e);
                    if (window.Livewire) {
                        window.Livewire.dispatch('refreshDashboard');
                    }
                });
                
            console.log('✅ Pusher channels subscribed successfully');
        } else {
            console.warn('⚠️ Outlet ID or Tenant ID not found - real-time disabled');
        }
    } else {
        console.warn('⚠️ Pusher or Laravel Echo not loaded - real-time disabled');
    }
    
    // ========================================
    // NOTIFICATION SOUND
    // ========================================
    
    function playNotificationSound() {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjGH0O/EZykFKHzN8t6EPgoUXrTp66hVFApGn+DyvmwhBjGH0O/EZykFKHzN8t6EP');
        audio.volume = 0.3;
        audio.play().catch(e => console.log('Audio play failed:', e));
    }
    
    // ========================================
    // TOAST NOTIFICATIONS
    // ========================================
    
    function showToast(title, message, type = 'info') {
        const colors = {
            success: '#51cf66',
            info: '#339af0',
            warning: '#ffd43b',
            error: '#ff6b6b'
        };
        
        const icons = {
            success: 'check_circle',
            info: 'info',
            warning: 'warning',
            error: 'error'
        };
        
        const toast = document.createElement('div');
        toast.className = 'modern-toast';
        toast.style.cssText = `
            position: fixed;
            top: 24px;
            right: 24px;
            background: white;
            color: #212529;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            z-index: 9999;
            min-width: 320px;
            max-width: 400px;
            border-left: 4px solid ${colors[type]};
            animation: slideInRight 0.3s ease-out;
        `;
        
        toast.innerHTML = `
            <div style="display: flex; align-items: start; gap: 12px;">
                <i class="material-icons" style="font-size: 24px; color: ${colors[type]};">
                    ${icons[type]}
                </i>
                <div style="flex: 1;">
                    <strong style="display: block; margin-bottom: 4px; font-size: 1rem;">${title}</strong>
                    <span style="font-size: 0.875rem; color: #6c757d;">${message}</span>
                </div>
                <button onclick="this.closest('.modern-toast').remove()" style="border: none; background: none; cursor: pointer; padding: 0; color: #999;">
                    <i class="material-icons" style="font-size: 20px;">close</i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    
    // Make showToast globally available
    window.showToast = showToast;
    
    // ========================================
    // ORDER DETAILS MODAL (Optional)
    // ========================================
    
    window.showOrderDetails = function(orderId) {
        console.log('📋 Show order details:', orderId);
        // Implement modal or redirect to order details page
        // For now, just log
        alert('Order details: ' + orderId);
    };
    
    // ========================================
    // TOGGLE NOTE EXPANSION
    // ========================================
    
    window.toggleNote = function(button) {
        const noteContainer = button.closest('.item-notes');
        const noteText = noteContainer.querySelector('.note-text');
        const fullNote = noteContainer.dataset.fullNote;
        
        if (noteContainer.classList.contains('expanded')) {
            // Collapse
            noteText.textContent = fullNote.substring(0, 50) + '...';
            noteContainer.classList.remove('expanded');
            button.classList.remove('expanded');
        } else {
            // Expand
            noteText.textContent = fullNote;
            noteContainer.classList.add('expanded');
            button.classList.add('expanded');
        }
    };
    
    // ========================================
    // SMOOTH SCROLL FOR COLUMNS
    // ========================================
    
    document.querySelectorAll('.column-content').forEach(column => {
        column.style.scrollBehavior = 'smooth';
    });
    
    console.log('✅ Dashboard scripts loaded successfully');
});

// Toast animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .pulse-highlight {
        animation: pulseColumn 2s;
    }
    
    @keyframes pulseColumn {
        0%, 100% {
            background: white;
        }
        50% {
            background: rgba(255, 107, 107, 0.05);
        }
    }
`;
document.head.appendChild(style);