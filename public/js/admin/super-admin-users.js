/**
 * Super Admin Users Management JavaScript
 * Handles all user management operations for Super Admins
 */

let currentUserId = null;

$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize password strength checker
    $('#new_password').on('input', checkPasswordStrength);
    
    // Toggle password visibility
    $('#toggleNewPassword').click(function() {
        togglePasswordVisibility('#new_password', '#toggleNewPassword');
    });
    
    $('#toggleConfirmPassword').click(function() {
        togglePasswordVisibility('#new_password_confirmation', '#toggleConfirmPassword');
    });
    
    // Status change effects
    $('#new_status').change(function() {
        showStatusEffects($(this).val());
    });
    
    // Role change permissions
    $('#new_role').change(function() {
        showRolePermissions($(this).val());
    });
    
    // Form submissions
    setupFormHandlers();
    
    // Auto-refresh every 30 seconds for active sessions
    setInterval(function() {
        $('.badge:contains("0"), .badge:contains("1"), .badge:contains("2"), .badge:contains("3"), .badge:contains("4"), .badge:contains("5")')
            .closest('tr').find('[onclick*="terminateUserSessions"]').length > 0 && 
            window.location.reload();
    }, 30000);
});

/**
 * Show password reset modal
 */
function showResetPasswordModal(userId, userName) {
    currentUserId = userId;
    $('#resetUserName').text(userName);
    $('#passwordResetForm')[0].reset();
    $('#passwordStrength').hide();
    
    const modal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
    modal.show();
}

/**
 * Show status toggle modal
 */
function showStatusToggleModal(userId, userName, currentStatus) {
    currentUserId = userId;
    $('#statusUserName').text(userName);
    
    // Set current status display
    const statusLabels = {
        'active': 'ใช้งานได้',
        'inactive': 'ไม่ใช้งาน',
        'suspended': 'ระงับการใช้งาน',
        'pending': 'รออนุมัติ'
    };
    
    $('#currentStatus').html(`<span class="badge badge-${getStatusBadgeClass(currentStatus)}">${statusLabels[currentStatus]}</span>`);
    
    // Reset form
    $('#statusToggleForm')[0].reset();
    $('#statusEffects').hide();
    
    const modal = new bootstrap.Modal(document.getElementById('statusToggleModal'));
    modal.show();
}

/**
 * Show promote role modal
 */
function showPromoteRoleModal(userId, userName, currentRole) {
    currentUserId = userId;
    $('#promoteUserName').text(userName);
    
    // Set current role display
    const roleLabels = {
        'user': 'ผู้ใช้',
        'admin': 'Admin',
        'super_admin': 'Super Admin'
    };
    
    $('#currentRole').html(`<span class="badge badge-${getRoleBadgeClass(currentRole)}">${roleLabels[currentRole]}</span>`);
    
    // Reset form
    $('#promoteRoleForm')[0].reset();
    $('#rolePermissions').hide();
    
    // Remove options that are same or lower than current role
    $('#new_role option').show();
    if (currentRole === 'admin') {
        $('#new_role option[value="admin"]').hide();
    }
    
    const modal = new bootstrap.Modal(document.getElementById('promoteRoleModal'));
    modal.show();
}

/**
 * Generate random password
 */
function generatePassword() {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    let password = '';
    
    // Ensure at least one of each type
    password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)]; // lowercase
    password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)]; // uppercase
    password += '0123456789'[Math.floor(Math.random() * 10)]; // number
    password += '!@#$%^&*()'[Math.floor(Math.random() * 10)]; // special char
    
    // Fill the rest
    for (let i = password.length; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Shuffle the password
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    $('#new_password').val(password);
    $('#new_password_confirmation').val(password);
    checkPasswordStrength();
    
    // Show password temporarily
    $('#new_password, #new_password_confirmation').attr('type', 'text');
    setTimeout(() => {
        $('#new_password, #new_password_confirmation').attr('type', 'password');
    }, 3000);
}

/**
 * Check password strength
 */
function checkPasswordStrength() {
    const password = $('#new_password').val();
    if (password.length === 0) {
        $('#passwordStrength').hide();
        return;
    }
    
    let strength = 0;
    let feedback = [];
    
    // Length check
    if (password.length >= 8) strength += 20;
    else feedback.push('อย่างน้อย 8 ตัวอักษร');
    
    // Lowercase check
    if (/[a-z]/.test(password)) strength += 20;
    else feedback.push('ตัวอักษรพิมพ์เล็ก');
    
    // Uppercase check
    if (/[A-Z]/.test(password)) strength += 20;
    else feedback.push('ตัวอักษรพิมพ์ใหญ่');
    
    // Number check
    if (/[0-9]/.test(password)) strength += 20;
    else feedback.push('ตัวเลข');
    
    // Special character check
    if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
    else feedback.push('อักขระพิเศษ');
    
    // Update progress bar
    const strengthBar = $('#passwordStrengthBar');
    const strengthText = $('#passwordStrengthText');
    
    strengthBar.css('width', strength + '%').attr('aria-valuenow', strength);
    
    if (strength < 40) {
        strengthBar.removeClass().addClass('progress-bar bg-danger');
        strengthText.text('อย่างอ่อน - ต้องการ: ' + feedback.join(', '));
    } else if (strength < 60) {
        strengthBar.removeClass().addClass('progress-bar bg-warning');
        strengthText.text('ปานกลาง - ต้องการ: ' + feedback.join(', '));
    } else if (strength < 80) {
        strengthBar.removeClass().addClass('progress-bar bg-info');
        strengthText.text('ดี - ต้องการ: ' + feedback.join(', '));
    } else {
        strengthBar.removeClass().addClass('progress-bar bg-success');
        strengthText.text('แข็งแรง');
    }
    
    $('#passwordStrength').show();
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility(inputSelector, buttonSelector) {
    const input = $(inputSelector);
    const button = $(buttonSelector);
    const icon = button.find('i');
    
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
}

/**
 * Show status change effects
 */
function showStatusEffects(newStatus) {
    if (!newStatus) {
        $('#statusEffects').hide();
        return;
    }
    
    const effects = {
        'active': [
            'ผู้ใช้สามารถเข้าสู่ระบบได้',
            'ผู้ใช้สามารถใช้งานฟีเจอร์ทั้งหมดได้'
        ],
        'inactive': [
            'ผู้ใช้ไม่สามารถเข้าสู่ระบบได้',
            'เซสชันที่มีอยู่จะยังคงใช้งานได้จนหมดอายุ'
        ],
        'suspended': [
            'ผู้ใช้ไม่สามารถเข้าสู่ระบบได้',
            'เซสชันที่มีอยู่ทั้งหมดจะถูกยกเลิกทันที',
            'ผู้ใช้จะได้รับการแจ้งเตือนทางอีเมล'
        ]
    };
    
    const effectsList = $('#statusEffectsList');
    effectsList.empty();
    
    effects[newStatus].forEach(effect => {
        effectsList.append(`<li>${effect}</li>`);
    });
    
    const alertClass = newStatus === 'active' ? 'alert-success' : 
                     newStatus === 'suspended' ? 'alert-danger' : 'alert-warning';
    
    $('#statusEffects').removeClass('alert-success alert-warning alert-danger')
                       .addClass(alertClass)
                       .show();
}

/**
 * Show role permissions
 */
function showRolePermissions(newRole) {
    if (!newRole) {
        $('#rolePermissions').hide();
        return;
    }
    
    const permissions = {
        'admin': [
            'จัดการผู้ใช้ทั่วไป (ดู, สร้าง, แก้ไข)',
            'อนุมัติการสมัครสมาชิก',
            'ดูรายงานและสถิติ',
            'จัดการเนื้อหาและประกาศ'
        ],
        'super_admin': [
            'สิทธิ์ทั้งหมดของ Admin',
            'จัดการผู้ดูแลระบบอื่น ๆ',
            'เปลี่ยนบทบาทของผู้ใช้',
            'จัดการการตั้งค่าระบบ',
            'เข้าถึงข้อมูลความปลอดภัยขั้นสูง',
            'จัดการ Security Policies'
        ]
    };
    
    const permissionsList = $('#rolePermissionsList');
    permissionsList.empty();
    
    permissions[newRole].forEach(permission => {
        permissionsList.append(`<li>${permission}</li>`);
    });
    
    const alertClass = newRole === 'super_admin' ? 'alert-danger' : 'alert-warning';
    
    $('#rolePermissions').removeClass('alert-warning alert-danger')
                         .addClass(alertClass)
                         .show();
}

/**
 * Setup form handlers
 */
function setupFormHandlers() {
    // Password Reset Form
    $('#passwordResetForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            new_password: $('#new_password').val(),
            new_password_confirmation: $('#new_password_confirmation').val(),
            send_email: $('#send_email').is(':checked'),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Validate passwords match
        if (formData.new_password !== formData.new_password_confirmation) {
            showAlert('error', 'รหัสผ่านไม่ตรงกัน', 'กรุณาตรวจสอบรหัสผ่านที่กรอกใหม่');
            return;
        }
        
        $('#resetPasswordBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังดำเนินการ...');
        
        $.ajax({
            url: `/super-admin/users/${currentUserId}/reset-password`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordResetModal'));
                    modal.hide();
                    showAlert('success', 'รีเซ็ตรหัสผ่านสำเร็จ', response.message);
                } else {
                    showAlert('error', 'เกิดข้อผิดพลาด', response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            },
            complete: function() {
                $('#resetPasswordBtn').prop('disabled', false).html('<i class="fas fa-key"></i> รีเซ็ตรหัสผ่าน');
            }
        });
    });
    
    // Status Toggle Form
    $('#statusToggleForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            status: $('#new_status').val(),
            reason: $('#status_reason').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $('#changeStatusBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังดำเนินการ...');
        
        $.ajax({
            url: `/super-admin/users/${currentUserId}/toggle-status`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('statusToggleModal'));
                    modal.hide();
                    showAlert('success', 'เปลี่ยนสถานะสำเร็จ', response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', 'เกิดข้อผิดพลาด', response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            },
            complete: function() {
                $('#changeStatusBtn').prop('disabled', false).html('<i class="fas fa-toggle-on"></i> เปลี่ยนสถานะ');
            }
        });
    });
    
    // Promote Role Form
    $('#promoteRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            role: $('#new_role').val(),
            reason: $('#promote_reason').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $('#promoteRoleBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังดำเนินการ...');
        
        $.ajax({
            url: `/super-admin/users/${currentUserId}/promote-role`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('promoteRoleModal'));
                    modal.hide();
                    showAlert('success', 'เปลี่ยนบทบาทสำเร็จ', response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', 'เกิดข้อผิดพลาด', response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            },
            complete: function() {
                $('#promoteRoleBtn').prop('disabled', false).html('<i class="fas fa-arrow-up"></i> เปลี่ยนบทบาท');
            }
        });
    });
}

/**
 * Terminate user sessions
 */
function terminateUserSessions(userId, userName) {
    Swal.fire({
        title: 'ยกเลิกเซสชัน?',
        text: `คุณต้องการยกเลิกเซสชันทั้งหมดของ "${userName}" หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ยกเลิกเซสชัน',
        cancelButtonText: 'ไม่'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/users/${userId}/terminate-sessions`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'ยกเลิกเซสชันสำเร็จ', response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', 'เกิดข้อผิดพลาด', response.message);
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr);
                }
            });
        }
    });
}

/**
 * Delete user
 */
function deleteUser(userId, userName) {
    Swal.fire({
        title: 'ลบผู้ใช้?',
        text: `คุณต้องการลบผู้ใช้ "${userName}" หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/users/${userId}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'ลบผู้ใช้สำเร็จ', response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', 'เกิดข้อผิดพลาด', response.message);
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr);
                }
            });
        }
    });
}

/**
 * Helper functions
 */
function getStatusBadgeClass(status) {
    const classes = {
        'active': 'success',
        'inactive': 'secondary',
        'suspended': 'danger',
        'pending': 'info'
    };
    return classes[status] || 'secondary';
}

function getRoleBadgeClass(role) {
    const classes = {
        'user': 'secondary',
        'admin': 'warning',
        'super_admin': 'danger'
    };
    return classes[role] || 'secondary';
}

function showAlert(type, title, message) {
    const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';
    Swal.fire({
        icon: icon,
        title: title,
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

function handleAjaxError(xhr) {
    let message = 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์';
    
    if (xhr.responseJSON) {
        if (xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            message = errors.join('<br>');
        }
    }
    
    showAlert('error', 'เกิดข้อผิดพลาด', message);
}
