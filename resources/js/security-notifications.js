import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize Echo with Pusher - Temporarily disabled to prevent errors
window.Pusher = Pusher;

// Disable WebSocket connection temporarily
/*
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY || 'your-pusher-key',
    cluster: process.env.MIX_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: true
});
*/

// Mock Echo for compatibility
window.Echo = {
    channel: () => ({
        listen: () => {},
        stopListening: () => {}
    }),
    private: () => ({
        listen: () => {},
        stopListening: () => {}
    }),
    disconnect: () => {}
};

class SecurityNotificationManager {
    constructor() {
        this.notifications = [];
        this.channels = [];
        this.init();
    }

    init() {
        this.createNotificationContainer();
        this.setupEventListeners();
        this.listenForSecurityEvents();
        this.loadExistingNotifications();
    }

    createNotificationContainer() {
        if (!document.getElementById('security-notifications')) {
            const container = document.createElement('div');
            container.id = 'security-notifications';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
    }

    setupEventListeners() {
        // Close notification buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-close-notification')) {
                const notification = e.target.closest('.toast');
                if (notification) {
                    this.dismissNotification(notification);
                }
            }
        });

        // Mark as read buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-mark-read')) {
                const notificationId = e.target.dataset.notificationId;
                this.markAsRead(notificationId);
            }
        });
    }

    listenForSecurityEvents() {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        
        if (!userId) {
            console.warn('ไม่พบ User ID สำหรับการแจ้งเตือนความปลอดภัย');
            return;
        }

        // Listen to private security channel
        const securityChannel = window.Echo.private(`security.${userId}`);
        this.channels.push(securityChannel);

        // Security events
        securityChannel.listen('.security.event', (event) => {
            this.handleSecurityEvent(event);
        });

        // Login attempts
        securityChannel.listen('.login.attempt', (event) => {
            this.handleLoginAttempt(event);
        });

        // Device events
        securityChannel.listen('.device.created', (event) => {
            this.handleDeviceEvent(event, 'Device Registered');
        });

        securityChannel.listen('.device.trusted', (event) => {
            this.handleDeviceEvent(event, 'Device Trusted');
        });

        securityChannel.listen('.device.untrusted', (event) => {
            this.handleDeviceEvent(event, 'Device Untrusted');
        });

        securityChannel.listen('.device.removed', (event) => {
            this.handleDeviceEvent(event, 'Device Removed');
        });

        // Security notifications
        securityChannel.notification((notification) => {
            this.handleNotification(notification);
        });
    }

    handleSecurityEvent(event) {
        const severity = event.severity || 'info';
        const title = this.getEventTitle(event.type);
        
        this.showNotification({
            id: `event-${Date.now()}`,
            type: event.type,
            title: title,
            message: event.message,
            severity: severity,
            timestamp: event.timestamp,
            data: event.data
        });

        // Update security dashboard if visible
        this.updateSecurityDashboard(event);
    }

    handleLoginAttempt(event) {
        if (event.is_suspicious || !event.successful) {
            const title = event.successful ? 'เข้าสู่ระบบสำเร็จ' : 'ความพยายามเข้าสู่ระบบล้มเหลว';
            const severity = event.is_suspicious ? 'warning' : 'info';
            
            this.showNotification({
                id: `login-${event.id}`,
                type: 'login_attempt',
                title: title,
                message: `ความพยายามเข้าสู่ระบบจาก ${event.ip_address}${event.location ? ` (${event.location})` : ''}`,
                severity: severity,
                timestamp: event.timestamp,
                data: event
            });
        }

        // Update login history if visible
        this.updateLoginHistory(event);
    }

    handleDeviceEvent(event, title) {
        const device = event.device;
        
        this.showNotification({
            id: `device-${device.id}-${Date.now()}`,
            type: 'device_event',
            title: title,
            message: `${device.device_name} (${device.device_type})`,
            severity: 'info',
            timestamp: event.timestamp,
            data: event
        });

        // Update device list if visible
        this.updateDeviceList(event);
    }

    handleNotification(notification) {
        this.showNotification({
            id: notification.id,
            type: notification.type,
            title: notification.title,
            message: notification.message,
            severity: notification.severity,
            timestamp: notification.timestamp,
            data: notification.data,
            actionUrl: notification.action_url,
            persistent: notification.severity === 'critical'
        });
    }

    showNotification(notification) {
        const container = document.getElementById('security-notifications');
        if (!container) return;

        const toast = this.createToastElement(notification);
        container.appendChild(toast);

        // Auto-dismiss non-critical notifications
        if (notification.severity !== 'critical' && !notification.persistent) {
            setTimeout(() => {
                this.dismissNotification(toast);
            }, 10000); // 10 seconds
        }

        // Add to notifications array
        this.notifications.unshift(notification);

        // Limit to 50 notifications
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }

        // Update notification badge
        this.updateNotificationBadge();
    }

    createToastElement(notification) {
        const toast = document.createElement('div');
        toast.className = `toast security-notification security-${notification.severity}`;
        toast.setAttribute('role', 'alert');
        toast.dataset.notificationId = notification.id;

        const severityColors = {
            info: 'text-bg-info',
            warning: 'text-bg-warning',
            high: 'text-bg-warning',
            critical: 'text-bg-danger'
        };

        const severityIcons = {
            info: 'fas fa-info-circle',
            warning: 'fas fa-exclamation-triangle',
            high: 'fas fa-exclamation-triangle',
            critical: 'fas fa-exclamation-circle'
        };

        toast.innerHTML = `
            <div class="toast-header ${severityColors[notification.severity] || 'text-bg-secondary'}">
                <i class="${severityIcons[notification.severity] || 'fas fa-bell'} me-2"></i>
                <strong class="me-auto">${notification.title}</strong>
                <small class="text-muted">${this.formatTimestamp(notification.timestamp)}</small>
                <button type="button" class="btn-close btn-close-notification" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${notification.message}
                ${notification.actionUrl ? `
                    <div class="mt-2">
                        <a href="${notification.actionUrl}" class="btn btn-sm btn-outline-primary">View Details</a>
                    </div>
                ` : ''}
            </div>
        `;

        return toast;
    }

    dismissNotification(toastElement) {
        toastElement.style.opacity = '0';
        toastElement.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            if (toastElement.parentNode) {
                toastElement.parentNode.removeChild(toastElement);
            }
        }, 300);
    }

    markAsRead(notificationId) {
        // Send AJAX request to mark notification as read
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        }).catch(console.error);
    }

    updateSecurityDashboard(event) {
        // Update security score and alerts if dashboard is visible
        const securityScore = document.getElementById('security-score');
        const alertsCount = document.getElementById('alerts-count');
        
        if (securityScore && event.data && event.data.risk_score) {
            // Refresh security score
            this.refreshSecurityScore();
        }
        
        if (alertsCount && event.severity === 'critical') {
            // Increment alerts count
            const current = parseInt(alertsCount.textContent) || 0;
            alertsCount.textContent = current + 1;
        }
    }

    updateLoginHistory(event) {
        const loginHistoryTable = document.getElementById('login-history-table');
        if (loginHistoryTable) {
            // Add new row to login history table
            this.addLoginHistoryRow(event);
        }
    }

    updateDeviceList(event) {
        const devicesList = document.getElementById('devices-list');
        if (devicesList) {
            // Update device list based on event type
            this.updateDeviceItem(event);
        }
    }

    refreshSecurityScore() {
        // Fetch updated security score
        fetch('/user/security/score', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const scoreElement = document.getElementById('security-score');
            if (scoreElement && data.score !== undefined) {
                scoreElement.textContent = data.score;
                scoreElement.className = `h2 mb-1 text-${this.getScoreColor(data.score)}`;
            }
        })
        .catch(console.error);
    }

    getScoreColor(score) {
        if (score >= 80) return 'success';
        if (score >= 60) return 'warning';
        return 'danger';
    }

    addLoginHistoryRow(event) {
        // Implementation for adding login history row
        const tableBody = document.querySelector('#login-history-table tbody');
        if (!tableBody) return;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <span class="badge bg-${event.successful ? 'success' : 'danger'}">
                    ${event.successful ? 'Success' : 'Failed'}
                </span>
            </td>
            <td>${event.ip_address}</td>
            <td>${event.location || 'Unknown'}</td>
            <td>${this.formatTimestamp(event.timestamp)}</td>
            <td>
                ${event.is_suspicious ? '<span class="badge bg-warning">Suspicious</span>' : ''}
            </td>
        `;

        tableBody.insertBefore(row, tableBody.firstChild);
    }

    updateDeviceItem(event) {
        // Implementation for updating device list item
        console.log('Device event:', event);
    }

    updateNotificationBadge() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            const unreadCount = this.notifications.filter(n => !n.read).length;
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'inline' : 'none';
        }
    }

    loadExistingNotifications() {
        // Load unread notifications from server
        fetch('/notifications/security/unread', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications) {
                data.notifications.forEach(notification => {
                    this.notifications.push(notification);
                });
                this.updateNotificationBadge();
            }
        })
        .catch(error => {
            console.error('Failed to load security notifications:', error);
        });
    }

    getEventTitle(type) {
        const titles = {
            'high_risk_login': 'การเข้าสู่ระบบเสี่ยงสูง',
            'device_registered': 'อุปกรณ์ใหม่',
            'device_trusted': 'อุปกรณ์ที่เชื่อถือ',
            'device_untrusted': 'อุปกรณ์ไม่เชื่อถือ',
            'device_removed': 'อุปกรณ์ถูกลบ',
            'ip_blocked': 'IP Address ถูกบล็อก',
            'account_locked': 'บัญชีถูกล็อก',
            'password_changed': 'เปลี่ยนรหัสผ่าน',
            '2fa_enabled': 'เปิดใช้ 2FA',
            '2fa_disabled': 'ปิดใช้ 2FA'
        };

        return titles[type] || 'เหตุการณ์ความปลอดภัย';
    }

    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));

        if (diffInMinutes < 1) return 'เมื่อสักครู่';
        if (diffInMinutes < 60) return `${diffInMinutes} นาทีที่แล้ว`;
        if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} ชั่วโมงที่แล้ว`;
        
        return date.toLocaleDateString('th-TH');
    }

    disconnect() {
        this.channels.forEach(channel => {
            window.Echo.leave(channel.name);
        });
        this.channels = [];
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('meta[name="user-id"]')) {
        window.SecurityNotifications = new SecurityNotificationManager();
    }
});

export default SecurityNotificationManager;
