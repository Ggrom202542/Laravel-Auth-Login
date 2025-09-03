<!-- Status Toggle Modal -->
<div class="modal fade" id="statusToggleModal" tabindex="-1" role="dialog" aria-labelledby="statusToggleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="statusToggleModalLabel">
                    <i class="fas fa-toggle-on"></i> เปลี่ยนสถานะผู้ใช้
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="statusToggleForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>ผู้ใช้:</strong></label>
                        <p id="statusUserName" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>สถานะปัจจุบัน:</strong></label>
                        <p id="currentStatus" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label for="new_status" class="form-label">สถานะใหม่ <span class="text-danger">*</span></label>
                        <select class="form-control" id="new_status" name="status" required>
                            <option value="">-- เลือกสถานะ --</option>
                            <option value="active">
                                <i class="fas fa-check-circle text-success"></i> ใช้งานได้
                            </option>
                            <option value="inactive">
                                <i class="fas fa-pause-circle text-secondary"></i> ไม่ใช้งาน
                            </option>
                            <option value="suspended">
                                <i class="fas fa-ban text-danger"></i> ระงับการใช้งาน
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status_reason" class="form-label">เหตุผลในการเปลี่ยนสถานะ (ไม่บังคับ)</label>
                        <textarea class="form-control" id="status_reason" name="reason" rows="3" 
                                  placeholder="ระบุเหตุผลในการเปลี่ยนสถานะ..."></textarea>
                        <small class="form-text text-muted">เหตุผลนี้จะถูกบันทึกในระบบเพื่อการตรวจสอบ</small>
                    </div>

                    <!-- Status Change Effects -->
                    <div id="statusEffects" class="alert" role="alert" style="display: none;">
                        <h6><i class="fas fa-info-circle"></i> ผลที่จะเกิดขึ้น:</h6>
                        <ul id="statusEffectsList"></ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-info" id="changeStatusBtn">
                        <i class="fas fa-toggle-on"></i> เปลี่ยนสถานะ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
