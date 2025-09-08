import './bootstrap';
import './security-notifications';

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Real-time security updates
window.refreshSecurityData = function() {
    // Refresh security dashboard components
    if (window.SecurityNotifications) {
        window.SecurityNotifications.refreshSecurityScore();
    }
    
    // Refresh other security components as needed
    console.log('Security data refreshed');
};
