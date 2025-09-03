<!-- Password Reset Modal -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordResetModalLabel">
                    <i class="bi bi-key me-2"></i>
                    รีเซ็ตรหัสผ่าน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-key-fill" style="font-size: 2rem;"></i>
                    </div>
                    <h5 id="reset-message">คุณต้องการรีเซ็ตรหัสผ่านของผู้ใช้นี้หรือไม่?</h5>
                    <p class="text-muted" id="reset-user-info">
                        <!-- Dynamic user info will be inserted here -->
                    </p>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>คำเตือน:</strong> การรีเซ็ตรหัสผ่านจะส่งผลกระทบต่อผู้ใช้ดังนี้
                    <ul class="mb-0 mt-2">
                        <li>ระบบจะสร้างรหัสผ่านใหม่แบบสุ่ม (8 ตัวอักษร)</li>
                        <li>ผู้ใช้จะต้องเปลี่ยนรหัสผ่านหลังจากเข้าสู่ระบบ</li>
                        <li>การเข้าสู่ระบบด้วยรหัสผ่านเก่าจะไม่สามารถทำได้</li>
                    </ul>
                </div>
                
                <form id="passwordResetForm" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-3">
                        <label for="reset-reason" class="form-label">เหตุผลในการรีเซ็ตรหัสผ่าน (ไม่บังคับ)</label>
                        <textarea class="form-control" id="reset-reason" name="reason" rows="3" 
                                  placeholder="ระบุเหตุผลในการรีเซ็ตรหัสผ่าน เช่น ผู้ใช้ลืมรหัสผ่าน..."></textarea>
                    </div>
                    
                    <!-- Notification Options -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bell me-2"></i>
                                ตัวเลือกการแจ้งเตือน
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="send-sms" name="send_sms" value="1">
                                        <label class="form-check-label" for="send-sms">
                                            <i class="bi bi-phone me-1"></i>
                                            ส่งรหัสผ่านทาง SMS
                                        </label>
                                    </div>
                                    <small class="text-muted" id="sms-info">
                                        <!-- Phone number will be shown here -->
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="send-email" name="send_email" value="1">
                                        <label class="form-check-label" for="send-email">
                                            <i class="bi bi-envelope me-1"></i>
                                            ส่งรหัสผ่านทาง Email
                                        </label>
                                    </div>
                                    <small class="text-muted" id="email-info">
                                        <!-- Email will be shown here -->
                                    </small>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3 mb-0">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    หากไม่เลือกตัวเลือกใด รหัสผ่านจะแสดงในหน้าเว็บแทน (ไม่แนะนำเพื่อความปลอดภัย)
                                </small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-2"></i>ยกเลิก
                </button>
                <button type="button" class="btn btn-danger" id="confirmPasswordReset">
                    <i class="bi bi-key me-2"></i>รีเซ็ตรหัสผ่าน
                </button>
            </div>
        </div>
    </div>
</div>
