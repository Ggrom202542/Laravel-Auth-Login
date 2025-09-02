/*!
 * Dashboard JavaScript
 * Laravel RBAC Authentication System
 * Version 2.0
 * Description: Interactive Dashboard Functionality
 */

// Dashboard Application
const Dashboard = {
    // Initialize Dashboard
    init: function() {
        this.initSidebar();
        this.initScrollToTop();
        this.initAlerts();
        this.initForms();
        this.initTooltips();
        this.initCharts();
    },

    // Sidebar Toggle Functionality
    initSidebar: function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarToggleTop = document.getElementById('sidebarToggleTop');
        const sidebar = document.querySelector('.sidebar');
        const body = document.body;

        function toggleSidebar() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                body.classList.toggle('sidebar-toggled');
                sidebar.classList.toggle('toggled');
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        
        if (sidebarToggleTop) {
            sidebarToggleTop.addEventListener('click', toggleSidebar);
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isToggleButton = event.target.closest('#sidebarToggle, #sidebarToggleTop');
                
                if (!isClickInsideSidebar && !isToggleButton && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
            }
        });
    },

    // Scroll to Top Button
    initScrollToTop: function() {
        const scrollToTopBtn = document.querySelector('.scroll-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                if (scrollToTopBtn) scrollToTopBtn.style.display = 'flex';
            } else {
                if (scrollToTopBtn) scrollToTopBtn.style.display = 'none';
            }
        });

        if (scrollToTopBtn) {
            scrollToTopBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    },

    // Auto-hide Alerts
    initAlerts: function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });

        // Manual close alert functionality
        const closeButtons = document.querySelectorAll('.alert .btn-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = this.closest('.alert');
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        });
    },

    // Form Enhancement
    initForms: function() {
        // Add loading state to forms
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>กำลังประมวลผล...';
                    
                    // Re-enable after 5 seconds as fallback
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 5000);
                }
            });
        });

        // Form validation enhancement
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                this.classList.add('was-validated');
            });
            
            input.addEventListener('focus', function() {
                this.classList.remove('is-invalid');
            });
        });
    },

    // Initialize Bootstrap Tooltips
    initTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    // Initialize Charts (placeholder for future chart integration)
    initCharts: function() {
        // Chart initialization will go here
        // This is a placeholder for when we integrate Chart.js or similar
        console.log('Charts initialized');
    },

    // Utility Functions
    utils: {
        // Show loading spinner
        showLoading: function(element) {
            if (element) {
                const spinner = document.createElement('div');
                spinner.className = 'spinner-border spinner-border-sm me-2';
                spinner.setAttribute('role', 'status');
                element.prepend(spinner);
            }
        },

        // Hide loading spinner
        hideLoading: function(element) {
            if (element) {
                const spinner = element.querySelector('.spinner-border');
                if (spinner) {
                    spinner.remove();
                }
            }
        },

        // Show toast notification
        showToast: function(message, type = 'success') {
            // Create toast container if it doesn't exist
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            // Create toast element
            const toastId = 'toast-' + Date.now();
            const toastHTML = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${type} text-white">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong class="me-auto">แจ้งเตือน</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;

            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        },

        // Format numbers with Thai comma separator
        formatNumber: function(num) {
            return new Intl.NumberFormat('th-TH').format(num);
        },

        // Format date to Thai format
        formatDate: function(date) {
            return new Intl.DateTimeFormat('th-TH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }).format(new Date(date));
        },

        // Debounce function for search inputs
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    },

    // AJAX Helper Functions
    ajax: {
        // GET request
        get: function(url, callback) {
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => callback(data))
            .catch(error => {
                console.error('Error:', error);
                Dashboard.utils.showToast('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'danger');
            });
        },

        // POST request
        post: function(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => callback(data))
            .catch(error => {
                console.error('Error:', error);
                Dashboard.utils.showToast('เกิดข้อผิดพลาดในการส่งข้อมูล', 'danger');
            });
        }
    }
};

// Initialize Dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    Dashboard.init();
});

// Make Dashboard globally available
window.Dashboard = Dashboard;
