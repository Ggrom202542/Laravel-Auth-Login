<!-- Promote Role Modal -->
<div class="modal fade" id="promoteRoleModal" tabindex="-1" role="dialog" aria-labelledby="promoteRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="promoteRoleModalLabel">
                    <i class="bi bi-arrow-up-circle"></i> เปลี่ยนบทบาทผู้ใช้
                </h5>
            </div>
            <form id="promoteRoleForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>คำเตือน:</strong> การเปลี่ยนบทบาทจะมอบสิทธิ์เพิ่มเติมให้กับผู้ใช้ กรุณาพิจารณาอย่างรอบคอบ
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>ผู้ใช้:</strong></label>
                        <p id="promoteUserName" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>บทบาทปัจจุบัน:</strong></label>
                        <p id="currentRole" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label for="new_role" class="form-label">บทบาทใหม่ <span class="text-danger">*</span></label>
                        <select class="form-control" id="new_role" name="role" required>
                            <option value="">-- เลือกบทบาท --</option>
                            <option value="admin">
                                🛡️ Admin
                            </option>
                            <option value="super_admin">
                                👑 Super Admin
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="promote_reason" class="form-label">เหตุผลในการเปลี่ยนบทบาท (ไม่บังคับ)</label>
                        <textarea class="form-control" id="promote_reason" name="reason" rows="3" 
                                  placeholder="ระบุเหตุผลในการเปลี่ยนบทบาท..."></textarea>
                        <small class="form-text text-muted">เหตุผลนี้จะถูกบันทึกในระบบเพื่อการตรวจสอบ</small>
                    </div>

                    <!-- Role Permissions Info -->
                    <div id="rolePermissions" class="alert" role="alert" style="display: none;">
                        <h6><i class="bi bi-key-fill"></i> สิทธิ์ที่จะได้รับ:</h6>
                        <ul id="rolePermissionsList"></ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-success" id="promoteRoleBtn">
                        <i class="bi bi-arrow-up-circle"></i> เปลี่ยนบทบาท
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
