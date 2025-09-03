/**
 * Registration Approval Management JavaScript
 * จัดการฟังก์ชั่น Interactive สำหรับระบบอนุมัติการสมัครสมาชิก
 */

class ApprovalManager {
    constructor() {
        // Initialize properties first
        this.selectedApprovals = new Set();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Then initialize the manager
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
    }

    setupEventListeners() {
        // Checkbox Selection
        this.setupCheckboxHandlers();
        
        // Bulk Action Buttons
        this.setupBulkActions();
        
        // Individual Action Buttons
        this.setupIndividualActions();
        
        // Search and Filter
        this.setupSearchAndFilter();
        
        // Modal Handlers
        this.setupModalHandlers();
    }

    setupCheckboxHandlers() {
        // Master checkbox (select all)
        const masterCheckbox = document.getElementById('selectAll');
        if (masterCheckbox) {
            masterCheckbox.addEventListener('change', (e) => {
                this.toggleAllCheckboxes(e.target.checked);
            });
        }

        // Individual checkboxes
        const checkboxes = document.querySelectorAll('.approval-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.handleCheckboxChange(e);
            });
        });
    }

    toggleAllCheckboxes(checked) {
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        const checkboxes = document.querySelectorAll('.approval-checkbox');
        checkboxes.forEach(checkbox => {
            // Only toggle enabled checkboxes
            if (!checkbox.disabled) {
                checkbox.checked = checked;
                const approvalId = checkbox.dataset.approvalId;
                
                if (checked) {
                    this.selectedApprovals.add(approvalId);
                } else {
                    this.selectedApprovals.delete(approvalId);
                }
            }
        });
        
        this.updateBulkActionsVisibility();
        this.updateSelectionCount();
    }

    handleCheckboxChange(event) {
        const checkbox = event.target;
        const approvalId = checkbox.dataset.approvalId;
        
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        if (checkbox.checked) {
            this.selectedApprovals.add(approvalId);
        } else {
            this.selectedApprovals.delete(approvalId);
            // Uncheck master checkbox if not all selected
            const masterCheckbox = document.getElementById('selectAll');
            if (masterCheckbox) {
                masterCheckbox.checked = false;
            }
        }
        
        this.updateBulkActionsVisibility();
        this.updateSelectionCount();
    }

    updateBulkActionsVisibility() {
        const bulkActionBar = document.getElementById('bulkActionBar');
        
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        const hasSelected = this.selectedApprovals.size > 0;
        
        if (bulkActionBar) {
            bulkActionBar.style.display = hasSelected ? 'block' : 'none';
        }
    }

    updateSelectionCount() {
        const countElement = document.getElementById('selectedCount');
        
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        if (countElement) {
            countElement.textContent = this.selectedApprovals.size;
        }
    }

    setupBulkActions() {
        // Bulk Approve Button
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');
        if (bulkApproveBtn) {
            bulkApproveBtn.addEventListener('click', () => {
                this.showBulkApproveModal();
            });
        }

        // Bulk Reject Button
        const bulkRejectBtn = document.getElementById('bulkRejectBtn');
        if (bulkRejectBtn) {
            bulkRejectBtn.addEventListener('click', () => {
                this.showBulkRejectModal();
            });
        }

        // Cancel Selection Button
        const cancelSelectionBtn = document.getElementById('cancelSelection');
        if (cancelSelectionBtn) {
            cancelSelectionBtn.addEventListener('click', () => {
                this.clearSelection();
            });
        }
    }

    setupIndividualActions() {
        // Single Approve Buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.approve-btn') || e.target.closest('.approve-btn')) {
                e.preventDefault();
                const button = e.target.matches('.approve-btn') ? e.target : e.target.closest('.approve-btn');
                const approvalId = button.dataset.approvalId;
                const userName = button.dataset.userName;
                this.showSingleApproveModal(approvalId, userName);
            }
        });

        // Single Reject Buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.reject-btn') || e.target.closest('.reject-btn')) {
                e.preventDefault();
                const button = e.target.matches('.reject-btn') ? e.target : e.target.closest('.reject-btn');
                const approvalId = button.dataset.approvalId;
                const userName = button.dataset.userName;
                this.showSingleRejectModal(approvalId, userName);
            }
        });
    }

    setupSearchAndFilter() {
        // Real-time Search
        const searchInput = document.getElementById('search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 500);
            });
        }

        // Status Filter
        const statusFilter = document.getElementById('status');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.applyFilter('status', e.target.value);
            });
        }

        // Date Range Filter
        const dateFromFilter = document.getElementById('date_from');
        const dateToFilter = document.getElementById('date_to');
        
        if (dateFromFilter && dateToFilter) {
            [dateFromFilter, dateToFilter].forEach(input => {
                input.addEventListener('change', () => {
                    this.applyDateRangeFilter();
                });
            });
        }
    }

    setupModalHandlers() {
        // Handle modal form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('#singleApproveForm')) {
                e.preventDefault();
                this.submitSingleApprove(e.target);
            }
            
            if (e.target.matches('#singleRejectForm')) {
                e.preventDefault();
                this.submitSingleReject(e.target);
            }
            
            if (e.target.matches('#bulkApproveForm')) {
                e.preventDefault();
                this.submitBulkApprove(e.target);
            }
            
            if (e.target.matches('#bulkRejectForm')) {
                e.preventDefault();
                this.submitBulkReject(e.target);
            }
        });
    }

    // Modal Management
    showSingleApproveModal(approvalId, userName) {
        const modal = new bootstrap.Modal(document.getElementById('singleApproveModal'));
        document.getElementById('singleApproveUserId').value = approvalId;
        document.getElementById('singleApproveUserName').textContent = userName;
        modal.show();
    }

    showSingleRejectModal(approvalId, userName) {
        const modal = new bootstrap.Modal(document.getElementById('singleRejectModal'));
        document.getElementById('singleRejectUserId').value = approvalId;
        document.getElementById('singleRejectUserName').textContent = userName;
        modal.show();
    }

    showBulkApproveModal() {
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        if (this.selectedApprovals.size === 0) {
            this.showAlert('กรุณาเลือกรายการที่ต้องการอนุมัติ', 'warning');
            return;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('bulkApproveModal'));
        const countElement = document.getElementById('bulkApproveCount');
        if (countElement) {
            countElement.textContent = this.selectedApprovals.size;
        }
        modal.show();
    }

    showBulkRejectModal() {
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        if (this.selectedApprovals.size === 0) {
            this.showAlert('กรุณาเลือกรายการที่ต้องการปฏิเสธ', 'warning');
            return;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('bulkRejectModal'));
        const countElement = document.getElementById('bulkRejectCount');
        if (countElement) {
            countElement.textContent = this.selectedApprovals.size;
        }
        modal.show();
    }

    // Form Submissions
    async submitSingleApprove(form) {
        const formData = new FormData(form);
        const approvalId = formData.get('approval_id');
        
        if (!approvalId) {
            this.showAlert('ไม่พบรหัสการอนุมัติ', 'danger');
            return;
        }
        
        try {
            this.showLoadingState(form);
            
            const response = await fetch(`/admin/approvals/${approvalId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            if (response.ok) {
                const result = await response.json();
                this.showAlert(result.message || 'อนุมัติเรียบร้อยแล้ว', 'success');
                this.closeModal('singleApproveModal');
                this.refreshPage();
            } else {
                const error = await response.json();
                throw new Error(error.message || 'เกิดข้อผิดพลาด');
            }
        } catch (error) {
            console.error('Approval error:', error);
            this.showAlert(error.message || 'เกิดข้อผิดพลาดในการอนุมัติ', 'danger');
        } finally {
            this.hideLoadingState(form);
        }
    }

    async submitSingleReject(form) {
        const formData = new FormData(form);
        const approvalId = formData.get('approval_id');
        const rejectionReason = formData.get('rejection_reason');
        
        if (!approvalId) {
            this.showAlert('ไม่พบรหัสการอนุมัติ', 'danger');
            return;
        }
        
        if (!rejectionReason || !rejectionReason.trim()) {
            this.showAlert('กรุณาระบุเหตุผลการปฏิเสธ', 'warning');
            return;
        }
        
        try {
            this.showLoadingState(form);
            
            const response = await fetch(`/admin/approvals/${approvalId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            if (response.ok) {
                const result = await response.json();
                this.showAlert(result.message || 'ปฏิเสธเรียบร้อยแล้ว', 'success');
                this.closeModal('singleRejectModal');
                this.refreshPage();
            } else {
                const error = await response.json();
                throw new Error(error.message || 'เกิดข้อผิดพลาด');
            }
        } catch (error) {
            console.error('Rejection error:', error);
            this.showAlert(error.message || 'เกิดข้อผิดพลาดในการปฏิเสธ', 'danger');
        } finally {
            this.hideLoadingState(form);
        }
    }

    async submitBulkApprove(form) {
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        const selectedIds = Array.from(this.selectedApprovals);
        
        if (selectedIds.length === 0) {
            this.showAlert('กรุณาเลือกรายการที่ต้องการอนุมัติ', 'warning');
            return;
        }
        
        try {
            this.showLoadingState(form);
            
            const response = await fetch('/admin/approvals/bulk-action', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    action: 'approve',
                    approval_ids: selectedIds
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                this.showAlert(result.message || `อนุมัติ ${selectedIds.length} รายการเรียบร้อยแล้ว`, 'success');
                this.closeModal('bulkApproveModal');
                this.refreshPage();
            } else {
                const error = await response.json();
                throw new Error(error.message || 'เกิดข้อผิดพลาด');
            }
        } catch (error) {
            console.error('Bulk approval error:', error);
            this.showAlert(error.message || 'เกิดข้อผิดพลาดในการอนุมัติ', 'danger');
        } finally {
            this.hideLoadingState(form);
        }
    }

    async submitBulkReject(form) {
        const formData = new FormData(form);
        const rejectionReason = formData.get('rejection_reason');
        
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        const selectedIds = Array.from(this.selectedApprovals);
        
        if (selectedIds.length === 0) {
            this.showAlert('กรุณาเลือกรายการที่ต้องการปฏิเสธ', 'warning');
            return;
        }
        
        if (!rejectionReason || !rejectionReason.trim()) {
            this.showAlert('กรุณาระบุเหตุผลการปฏิเสธ', 'warning');
            return;
        }
        
        try {
            this.showLoadingState(form);
            
            const response = await fetch('/admin/approvals/bulk-action', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    action: 'reject',
                    approval_ids: selectedIds,
                    rejection_reason: rejectionReason
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                this.showAlert(result.message || `ปฏิเสธ ${selectedIds.length} รายการเรียบร้อยแล้ว`, 'success');
                this.closeModal('bulkRejectModal');
                this.refreshPage();
            } else {
                const error = await response.json();
                throw new Error(error.message || 'เกิดข้อผิดพลาด');
            }
        } catch (error) {
            console.error('Bulk rejection error:', error);
            this.showAlert(error.message || 'เกิดข้อผิดพลาดในการปฏิเสธ', 'danger');
        } finally {
            this.hideLoadingState(form);
        }
    }

    // Search and Filter Functions
    performSearch(query) {
        const url = new URL(window.location);
        if (query && query.trim()) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('page'); // Reset pagination
        window.location.href = url.toString();
    }

    applyFilter(filterName, value) {
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set(filterName, value);
        } else {
            url.searchParams.delete(filterName);
        }
        url.searchParams.delete('page'); // Reset pagination
        window.location.href = url.toString();
    }

    applyDateRangeFilter() {
        const dateFrom = document.getElementById('date_from')?.value;
        const dateTo = document.getElementById('date_to')?.value;
        const url = new URL(window.location);
        
        if (dateFrom) url.searchParams.set('date_from', dateFrom);
        else url.searchParams.delete('date_from');
        
        if (dateTo) url.searchParams.set('date_to', dateTo);
        else url.searchParams.delete('date_to');
        
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    showLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังดำเนินการ...';
        }
    }

    hideLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            // Restore original text based on form
            if (form.id.includes('Approve')) {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> อนุมัติ';
            } else if (form.id.includes('Reject')) {
                submitBtn.innerHTML = '<i class="fas fa-times"></i> ปฏิเสธ';
            }
        }
    }

    closeModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    }

    refreshPage() {
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    // Utility Functions
    clearSelection() {
        // Ensure selectedApprovals is initialized
        if (!this.selectedApprovals) {
            this.selectedApprovals = new Set();
        }
        
        this.selectedApprovals.clear();
        
        // Uncheck all checkboxes
        const checkboxes = document.querySelectorAll('.approval-checkbox, #selectAll, #selectAllHeader');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        
        this.updateBulkActionsVisibility();
        this.updateSelectionCount();
    }

    showAlert(message, type = 'info') {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }

    initializeComponents() {
        // Initialize tooltips if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(tooltipTriggerEl => {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Update initial states
        this.updateBulkActionsVisibility();
        this.updateSelectionCount();
        
        // Log successful initialization
        console.log('ApprovalManager initialized successfully');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    try {
        window.approvalManager = new ApprovalManager();
        console.log('ApprovalManager instance created');
    } catch (error) {
        console.error('Failed to initialize ApprovalManager:', error);
    }
});

// Export for global access
window.ApprovalManager = ApprovalManager;