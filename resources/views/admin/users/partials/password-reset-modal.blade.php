<!-- Password Reset Modal -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="passwordResetForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="passwordResetModalLabel">
                        <i class="fas fa-key me-2"></i>
                        รีเซ็ตรหัสผ่าน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- User Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="reset-user-info">กำลังรีเซ็ตรหัสผ่าน...</span>
                    </div>
                    
                    <!-- Warning -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>คำเตือน:</strong> การรีเซ็ตรหัสผ่านจะสร้างรหัสผ่านใหม่ และผู้ใช้จะต้องเปลี่ยนรหัสผ่านในการเข้าสู่ระบบครั้งแรก
                    </div>
                    
                    <!-- Notification Options -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-bell me-2"></i>
                                เลือกช่องทางการแจ้งเตือน
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- SMS Option -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="send-sms" name="send_sms" value="1">
                                <label class="form-check-label d-flex align-items-center" for="send-sms">
                                    <i class="fas fa-sms me-2 text-success"></i>
                                    <div>
                                        <strong>ส่งข้อความ SMS</strong>
                                        <div id="sms-info" class="small text-muted">ตรวจสอบเบอร์โทรศัพท์...</div>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Email Option -->
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send-email" name="send_email" value="1">
                                <label class="form-check-label d-flex align-items-center" for="send-email">
                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                    <div>
                                        <strong>ส่งอีเมล</strong>
                                        <div id="email-info" class="small text-muted">ตรวจสอบอีเมล...</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Requirements Note -->
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            ต้องเลือกอย่างน้อย 1 ช่องทางในการแจ้งเตือน
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        ยกเลิก
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmPasswordReset">
                        <i class="fas fa-key me-1"></i>
                        รีเซ็ตรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
