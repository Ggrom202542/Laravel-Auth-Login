<!-- Password Reset Modal -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" role="dialog" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="passwordResetModalLabel">
                    <i class="fas fa-key"></i> รีเซ็ตรหัสผ่าน
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="passwordResetForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>คำเตือน:</strong> การดำเนินการนี้จะเปลี่ยนรหัสผ่านของผู้ใช้ และจะไม่สามารถยกเลิกได้
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>ผู้ใช้:</strong></label>
                        <p id="resetUserName" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   required minlength="8" placeholder="กรอกรหัสผ่านใหม่">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร</small>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" 
                                   required minlength="8" placeholder="กรอกรหัสผ่านอีกครั้ง">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_email" name="send_email" checked>
                            <label class="form-check-label" for="send_email">
                                ส่งรหัสผ่านใหม่ทางอีเมลให้ผู้ใช้
                            </label>
                        </div>
                    </div>

                    <!-- Password Generator -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="generatePassword()">
                            <i class="fas fa-random"></i> สร้างรหัสผ่านอัตโนมัติ
                        </button>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div id="passwordStrength" class="mb-3" style="display: none;">
                        <label class="form-label">ความแข็งแรงของรหัสผ่าน:</label>
                        <div class="progress" style="height: 10px;">
                            <div id="passwordStrengthBar" class="progress-bar" role="progressbar" 
                                 style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small id="passwordStrengthText" class="form-text"></small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning" id="resetPasswordBtn">
                        <i class="fas fa-key"></i> รีเซ็ตรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
