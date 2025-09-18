/**
 * Enhanced Notification Badge Management
 * จัดการการแสดงผล badge numbers และ animations
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================================================
    // BADGE NUMBER FORMATTING
    // ==========================================================================
    
    /**
     * Format badge numbers เพื่อให้แสดงผลสวยงาม
     */
    function formatBadgeNumbers() {
        const badges = document.querySelectorAll('.badge, .badge-counter');
        
        badges.forEach(badge => {
            const text = badge.textContent.trim();
            const number = parseInt(text);
            
            if (!isNaN(number)) {
                // ถ้าเกิน 99 แสดงเป็น 99+
                if (number > 99) {
                    badge.textContent = '99+';
                    badge.classList.add('overflow');
                    badge.setAttribute('title', `${number} รายการ`);
                }
                
                // เพิ่ม data attribute สำหรับ styling
                if (number > 10) {
                    badge.setAttribute('data-count', 'high');
                }
                
                // เพิ่ม accessibility
                badge.setAttribute('aria-label', `${number} การแจ้งเตือนใหม่`);
            }
        });
    }
    
    // ==========================================================================
    // BADGE ANIMATIONS
    // ==========================================================================
    
    /**
     * เพิ่ม animation เมื่อมี notification ใหม่
     */
    function animateNewNotification(badgeElement) {
        badgeElement.style.animation = 'none';
        badgeElement.offsetHeight; // Force reflow
        badgeElement.style.animation = 'newNotificationBounce 0.6s ease-out';
    }
    
    /**
     * อัปเดต badge count พร้อม animation
     */
    function updateBadgeCount(badgeId, newCount) {
        const badge = document.getElementById(badgeId);
        if (!badge) return;
        
        const currentCount = parseInt(badge.textContent) || 0;
        
        if (newCount > currentCount) {
            // มี notification ใหม่
            animateNewNotification(badge);
        }
        
        // อัปเดตตัวเลข
        badge.textContent = newCount;
        
        // แสดง/ซ่อน badge
        if (newCount > 0) {
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
        
        // Format ตัวเลขใหม่
        formatBadgeNumbers();
    }
    
    // ==========================================================================
    // DROPDOWN INTERACTIONS
    // ==========================================================================
    
    /**
     * จัดการเมื่อเปิด notification dropdown
     */
    function handleNotificationDropdownOpen() {
        const alertsBadge = document.querySelector('#alertsDropdown .badge, #alertsDropdown .badge-counter');
        if (alertsBadge) {
            // หยุด animation เมื่อเปิด dropdown
            alertsBadge.style.animation = 'none';
        }
    }
    
    /**
     * จัดการเมื่อปิด notification dropdown
     */
    function handleNotificationDropdownClose() {
        const alertsBadge = document.querySelector('#alertsDropdown .badge, #alertsDropdown .badge-counter');
        if (alertsBadge && parseInt(alertsBadge.textContent) > 0) {
            // เริ่ม animation ใหม่เมื่อปิด dropdown
            alertsBadge.style.animation = 'badgePulse 2s infinite';
        }
    }
    
    // ==========================================================================
    // EVENT LISTENERS
    // ==========================================================================
    
    // Dropdown events
    const alertsDropdown = document.getElementById('alertsDropdown');
    if (alertsDropdown) {
        alertsDropdown.addEventListener('show.bs.dropdown', handleNotificationDropdownOpen);
        alertsDropdown.addEventListener('hide.bs.dropdown', handleNotificationDropdownClose);
    }
    
    const messagesDropdown = document.getElementById('messagesDropdown');
    if (messagesDropdown) {
        messagesDropdown.addEventListener('show.bs.dropdown', function() {
            const messagesBadge = this.querySelector('.badge, .badge-counter');
            if (messagesBadge) {
                messagesBadge.style.animation = 'none';
            }
        });
        
        messagesDropdown.addEventListener('hide.bs.dropdown', function() {
            const messagesBadge = this.querySelector('.badge, .badge-counter');
            if (messagesBadge && parseInt(messagesBadge.textContent) > 0) {
                messagesBadge.style.animation = 'badgePulse 2s infinite';
            }
        });
    }
    
    // ==========================================================================
    // REAL-TIME UPDATES (WebSocket/AJAX)
    // ==========================================================================
    
    /**
     * Simulate real-time notification updates
     * ในระบบจริงจะเชื่อมต่อกับ WebSocket หรือ polling
     */
    function initializeRealTimeUpdates() {
        // ตัวอย่างการอัปเดต notification count
        setInterval(function() {
            // จำลองการได้รับ notification ใหม่
            // ในระบบจริงจะมาจาก server
            
            // สามารถเรียกใช้ updateBadgeCount เมื่อได้รับข้อมูลใหม่
            // updateBadgeCount('notification-badge', newCount);
        }, 30000); // ตรวจสอบทุก 30 วินาที
    }
    
    // ==========================================================================
    // INITIALIZATION
    // ==========================================================================
    
    // Format badge numbers ตอนโหลดหน้า
    formatBadgeNumbers();
    
    // เริ่มต้น real-time updates (optional)
    // initializeRealTimeUpdates();
    
    // ==========================================================================
    // UTILITY FUNCTIONS
    // ==========================================================================
    
    /**
     * Mark all notifications as read
     */
    window.markAllNotificationsRead = function() {
        // อัปเดต badge เป็น 0
        const alertsBadge = document.querySelector('#alertsDropdown .badge, #alertsDropdown .badge-counter');
        if (alertsBadge) {
            updateBadgeCount(alertsBadge.id || 'alerts-badge', 0);
        }
        
        // ส่งคำขอไปยัง server
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // อัปเดต UI เพิ่มเติม
                console.log('Notifications marked as read');
            }
        })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
        });
    };
    
    /**
     * Add CSS animations
     */
    function addCustomAnimations() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes newNotificationBounce {
                0% { transform: scale(1); }
                50% { transform: scale(1.3); }
                100% { transform: scale(1); }
            }
            
            @keyframes badgeShake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
                20%, 40%, 60%, 80% { transform: translateX(3px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // เพิ่ม animations
    addCustomAnimations();
    
});

// ==========================================================================
// GLOBAL FUNCTIONS สำหรับเรียกใช้จากที่อื่น
// ==========================================================================

/**
 * เพิ่ม notification badge count
 */
window.incrementNotificationCount = function(type = 'notification') {
    const selector = type === 'message' ? '#messagesDropdown .badge, #messagesDropdown .badge-counter' : '#alertsDropdown .badge, #alertsDropdown .badge-counter';
    const badge = document.querySelector(selector);
    
    if (badge) {
        const currentCount = parseInt(badge.textContent) || 0;
        const newCount = currentCount + 1;
        
        badge.textContent = newCount;
        badge.style.display = 'flex';
        
        // Animation สำหรับ notification ใหม่
        badge.style.animation = 'newNotificationBounce 0.6s ease-out';
        
        // Format ตัวเลข
        if (newCount > 99) {
            badge.textContent = '99+';
            badge.classList.add('overflow');
        }
        
        // กลับไป animation ปกติ
        setTimeout(() => {
            badge.style.animation = 'badgePulse 2s infinite';
        }, 600);
    }
};

/**
 * ลด notification badge count
 */
window.decrementNotificationCount = function(type = 'notification') {
    const selector = type === 'message' ? '#messagesDropdown .badge, #messagesDropdown .badge-counter' : '#alertsDropdown .badge, #alertsDropdown .badge-counter';
    const badge = document.querySelector(selector);
    
    if (badge) {
        const currentCount = parseInt(badge.textContent) || 0;
        const newCount = Math.max(0, currentCount - 1);
        
        if (newCount === 0) {
            badge.style.display = 'none';
        } else {
            badge.textContent = newCount;
            badge.classList.remove('overflow');
        }
    }
};