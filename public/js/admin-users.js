/**
 * Admin User Management JavaScript
 */

// Global variables
let currentUserId = null;
let currentUserName = null;

/**
 * Initialize page
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Users JS Loaded');
    initializeEventListeners();
});

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Status toggle modal
    const confirmStatusToggle = document.getElementById('confirmStatusToggle');
    if (confirmStatusToggle) {
        confirmStatusToggle.addEventListener('click', function() {
            submitStatusToggle();
        });
    }
    
    // Password reset modal
    const confirmPasswordReset = document.getElementById('confirmPasswordReset');
    if (confirmPasswordReset) {
        confirmPasswordReset.addEventListener('click', function() {
            submitPasswordReset();
        });
    }
}

/**
 * Toggle user status
 */
function toggleStatus(userId, currentStatus) {
    currentUserId = userId;
    
    // Determine new status and modal content
    const statusConfig = getStatusConfig(currentStatus);
    
    // Update modal content
    updateStatusModal(statusConfig);
    
    // Set form action
    const form = document.getElementById('statusToggleForm');
    if (form) {
        form.action = `/admin/users/${userId}/toggle-status`;
        const newStatusInput = document.getElementById('new-status');
        if (newStatusInput) {
            newStatusInput.value = statusConfig.newStatus;
        }
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('statusToggleModal'));
    modal.show();
}

/**
 * Get status configuration
 */
function getStatusConfig(currentStatus) {
    const configs = {
        'active': {
            newStatus: 'inactive',
            icon: '<i class="bi bi-person-x text-warning" style="font-size: 3rem;"></i>',
            message: 'ปิดใช้งานผู้ใช้นี้?',
            description: 'ผู้ใช้จะไม่สามารถเข้าสู่ระบบได้จนกว่าจะเปิดใช้งานอีกครั้ง',
            buttonClass: 'btn-warning',
            buttonText: 'ปิดใช้งาน'
        },
        'inactive': {
            newStatus: 'active',
            icon: '<i class="bi bi-person-check text-success" style="font-size: 3rem;"></i>',
            message: 'เปิดใช้งานผู้ใช้นี้?',
            description: 'ผู้ใช้จะสามารถเข้าสู่ระบบและใช้งานได้ปกติ',
            buttonClass: 'btn-success',
            buttonText: 'เปิดใช้งาน'
        },
        'suspended': {
            newStatus: 'active',
            icon: '<i class="bi bi-person-check text-success" style="font-size: 3rem;"></i>',
            message: 'ยกเลิกการระงับผู้ใช้นี้?',
            description: 'ผู้ใช้จะสามารถเข้าสู่ระบบและใช้งานได้ปกติ',
            buttonClass: 'btn-success',
            buttonText: 'ยกเลิกระงับ'
        }
    };
    
    return configs[currentStatus] || configs['active'];
}

/**
 * Update status modal content
 */
function updateStatusModal(config) {
    const statusIcon = document.getElementById('status-icon');
    const statusMessage = document.getElementById('status-message');
    const statusDescription = document.getElementById('status-description');
    const confirmBtn = document.getElementById('confirmStatusToggle');
    
    if (statusIcon) statusIcon.innerHTML = config.icon;
    if (statusMessage) statusMessage.textContent = config.message;
    if (statusDescription) statusDescription.textContent = config.description;
    
    if (confirmBtn) {
        confirmBtn.className = `btn ${config.buttonClass}`;
        confirmBtn.innerHTML = `<i class="bi bi-check2 me-2"></i>${config.buttonText}`;
    }
}

/**
 * Submit status toggle
 */
function submitStatusToggle() {
    const form = document.getElementById('statusToggleForm');
    const submitBtn = document.getElementById('confirmStatusToggle');
    
    if (!form || !currentUserId) return;
    
    // Show loading
    showButtonLoading(submitBtn, 'กำลังดำเนินการ...');
    
    // Submit form via AJAX
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('สำเร็จ', data.message, 'success');
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('statusToggleModal')).hide();
            // Reload page
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('ข้อผิดพลาด', data.message || 'เกิดข้อผิดพลาดในการดำเนินการ', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
    })
    .finally(() => {
        hideButtonLoading(submitBtn);
    });
}

/**
 * Reset user password - ฟังก์ชันหลักที่เรียกจากปุ่ม
 */
function resetPassword(userId, userName, userPhone = null, userEmail = null) {
    console.log('resetPassword called:', {userId, userName, userPhone, userEmail});
    
    currentUserId = userId;
    currentUserName = userName;
    
    // Update modal content
    const resetUserInfo = document.getElementById('reset-user-info');
    if (resetUserInfo) {
        resetUserInfo.innerHTML = `ผู้ใช้: <strong>${userName}</strong>`;
    }
    
    // Update notification options
    updateNotificationOptions(userPhone, userEmail);
    
    // Set form action
    const form = document.getElementById('passwordResetForm');
    if (form) {
        form.action = `/admin/users/${userId}/reset-password`;
    }
    
    // Show modal
    const modalElement = document.getElementById('passwordResetModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        console.error('Password reset modal not found');
    }
}

/**
 * Update notification options in modal
 */
function updateNotificationOptions(userPhone, userEmail) {
    console.log('updateNotificationOptions called:', {userPhone, userEmail});
    
    const smsCheckbox = document.getElementById('send-sms');
    const emailCheckbox = document.getElementById('send-email');
    const smsInfo = document.getElementById('sms-info');
    const emailInfo = document.getElementById('email-info');
    
    console.log('Elements found:', {smsCheckbox, emailCheckbox, smsInfo, emailInfo});
    
    // SMS option
    if (smsCheckbox && smsInfo) {
        if (userPhone && userPhone !== 'null' && userPhone.trim() !== '') {
            smsCheckbox.disabled = false;
            smsCheckbox.checked = true;
            smsInfo.textContent = `จะส่งไปยัง: ${userPhone}`;
            smsInfo.className = 'text-muted small';
            console.log('SMS enabled for:', userPhone);
        } else {
            smsCheckbox.disabled = true;
            smsCheckbox.checked = false;
            smsInfo.textContent = 'ไม่มีเบอร์โทรศัพท์';
            smsInfo.className = 'text-danger small';
            console.log('SMS disabled - no phone');
        }
    } else {
        console.error('SMS elements not found');
    }
    
    // Email option
    if (emailCheckbox && emailInfo) {
        if (userEmail && userEmail !== 'null' && userEmail.trim() !== '') {
            emailCheckbox.disabled = false;
            emailCheckbox.checked = true;
            emailInfo.textContent = `จะส่งไปยัง: ${userEmail}`;
            emailInfo.className = 'text-muted small';
            console.log('Email enabled for:', userEmail);
        } else {
            emailCheckbox.disabled = true;
            emailCheckbox.checked = false;
            emailInfo.textContent = 'ไม่มีอีเมล';
            emailInfo.className = 'text-danger small';
            console.log('Email disabled - no email');
        }
    } else {
        console.error('Email elements not found');
    }
}

/**
 * Submit password reset
 */
function submitPasswordReset() {
    const form = document.getElementById('passwordResetForm');
    const submitBtn = document.getElementById('confirmPasswordReset');
    const smsCheckbox = document.getElementById('send-sms');
    const emailCheckbox = document.getElementById('send-email');
    
    if (!form || !currentUserId) {
        console.error('Form or userId not found');
        return;
    }
    
    // Validate at least one notification method is selected
    const smsChecked = smsCheckbox && smsCheckbox.checked && !smsCheckbox.disabled;
    const emailChecked = emailCheckbox && emailCheckbox.checked && !emailCheckbox.disabled;
    
    if (!smsChecked && !emailChecked) {
        showToast('แจ้งเตือน', 'กรุณาเลือกช่องทางการแจ้งเตือนอย่างน้อย 1 ช่องทาง', 'warning');
        return;
    }
    
    // Show loading
    showButtonLoading(submitBtn, 'กำลังรีเซ็ต...');
    
    // Prepare form data
    const formData = new FormData(form);
    formData.append('send_sms', smsChecked ? '1' : '0');
    formData.append('send_email', emailChecked ? '1' : '0');
    formData.append('_method', 'PATCH'); // Laravel method spoofing
    
    // Submit form via AJAX
    fetch(form.action, {
        method: 'POST', // ยังคงเป็น POST แต่ใช้ _method เพื่อ spoofing
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Password reset response:', data);
        
        if (data.success) {
            // Close modal first
            const modalElement = document.getElementById('passwordResetModal');
            if (modalElement) {
                bootstrap.Modal.getInstance(modalElement).hide();
            }
            
            // Show detailed success message
            showPasswordResetSuccess(data, smsChecked, emailChecked);
            
            // Reload page after 3 seconds
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            
        } else {
            showToast('ข้อผิดพลาด', data.message || 'ไม่สามารถรีเซ็ตรหัสผ่านได้', 'error');
        }
    })
    .catch(error => {
        console.error('Password reset error:', error);
        showToast('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error.message, 'error');
    })
    .finally(() => {
        hideButtonLoading(submitBtn);
    });
}

/**
 * Show password reset success message
 */
function showPasswordResetSuccess(data, smsChecked, emailChecked) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'รีเซ็ตรหัสผ่านสำเร็จ',
            html: `
                <div class="text-start">
                    <div class="alert alert-info">
                        <strong>รหัสผ่านใหม่:</strong> 
                        <code class="fs-5 text-primary">${data.new_password}</code>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('${data.new_password}')">
                            <i class="fas fa-copy"></i> คัดลอก
                        </button>
                    </div>
                    <hr>
                    <p><strong>สถานะการแจ้งเตือน:</strong></p>
                    <ul class="list-unstyled">
                        ${data.sms_sent ? 
                            '<li class="text-success mb-2"><i class="fas fa-check-circle"></i> SMS ส่งสำเร็จ</li>' : 
                            (smsChecked ? '<li class="text-danger mb-2"><i class="fas fa-times-circle"></i> SMS ส่งไม่สำเร็จ</li>' : '<li class="text-muted mb-2"><i class="fas fa-minus-circle"></i> ไม่ส่ง SMS</li>')
                        }
                        ${data.email_sent ? 
                            '<li class="text-success mb-2"><i class="fas fa-check-circle"></i> Email ส่งสำเร็จ</li>' : 
                            (emailChecked ? '<li class="text-danger mb-2"><i class="fas fa-times-circle"></i> Email ส่งไม่สำเร็จ</li>' : '<li class="text-muted mb-2"><i class="fas fa-minus-circle"></i> ไม่ส่ง Email</li>')
                        }
                    </ul>
                    <div class="alert alert-warning mt-3">
                        <small><i class="fas fa-exclamation-triangle"></i> 
                        ผู้ใช้จะต้องเปลี่ยนรหัสผ่านใหม่ในการเข้าสู่ระบบครั้งถัดไป</small>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'รับทราบ',
            width: '500px',
            customClass: {
                htmlContainer: 'text-start'
            }
        });
    } else {
        alert(`รีเซ็ตรหัสผ่านสำเร็จ!\nรหัสผ่านใหม่: ${data.new_password}`);
    }
}

/**
 * Delete user
 */
function deleteUser(userId, userName) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'ยืนยันการลบ',
            html: `คุณต้องการลบผู้ใช้ <strong>${userName}</strong> หรือไม่?<br><small class="text-danger">การดำเนินการนี้ไม่สามารถย้อนกลับได้</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                performDeleteUser(userId);
            }
        });
    } else {
        if (confirm(`คุณต้องการลบผู้ใช้ ${userName} หรือไม่?`)) {
            performDeleteUser(userId);
        }
    }
}

/**
 * Perform user deletion
 */
function performDeleteUser(userId) {
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/users/${userId}`;
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    // Add method spoofing
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('สำเร็จ', 'คัดลอกรหัสผ่านแล้ว', 'success');
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            // Fallback
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(text);
    }
}

/**
 * Fallback copy function
 */
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('สำเร็จ', 'คัดลอกรหัสผ่านแล้ว', 'success');
    } catch (err) {
        console.error('Fallback copy failed: ', err);
        showToast('ข้อผิดพลาด', 'ไม่สามารถคัดลอกได้', 'error');
    }
    
    document.body.removeChild(textArea);
}

/**
 * Show button loading state
 */
function showButtonLoading(button, text = 'กำลังโหลด...') {
    if (!button) return;
    
    button.disabled = true;
    button.setAttribute('data-original-text', button.innerHTML);
    button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
}

/**
 * Hide button loading state
 */
function hideButtonLoading(button) {
    if (!button) return;
    
    button.disabled = false;
    const originalText = button.getAttribute('data-original-text');
    if (originalText) {
        button.innerHTML = originalText;
        button.removeAttribute('data-original-text');
    }
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    // If SweetAlert2 is available, use it
    if (typeof Swal !== 'undefined') {
        const icons = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        Swal.fire({
            icon: icons[type] || 'info',
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        // Fallback to alert
        alert(`${title}: ${message}`);
    }
}

// Export functions to global scope for onclick handlers
window.resetPassword = resetPassword;
window.toggleStatus = toggleStatus;
window.deleteUser = deleteUser;
window.copyToClipboard = copyToClipboard;
