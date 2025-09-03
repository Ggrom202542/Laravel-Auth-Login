<!-- Single Reject Modal -->
<div class="modal fade" id="singleRejectModal" tabindex="-1" aria-labelledby="singleRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="singleRejectModalLabel">
                    <i class="fas fa-times-circle me-2"></i>ปฏิเสธการสมัครสมาชิก
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="singleRejectForm" method="POST">
                @csrf
                <input type="hidden" name="approval_id" id="singleRejectUserId">
                
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                        <div>
                            <strong>คุณกำลังจะปฏิเสธการสมัครสมาชิกของ</strong>
                            <p class="mb-0 mt-1">
                                <span id="singleRejectUserName" class="fw-bold text-danger"></span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="singleRejectionReason" class="form-label fw-bold">
                            <i class="fas fa-comment-alt me-2"></i>เหตุผลการปฏิเสธ
                            <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="singleRejectionReason" 
                            name="rejection_reason" 
                            rows="4" 
                            required 
                            placeholder="กรุณาระบุเหตุผลการปฏิเสธที่ชัดเจนและสุภาพ เช่น ข้อมูลไม่ครบถ้วน, เอกสารไม่ชัดเจน, ไม่ตรงตามเงื่อนไขการสมัคร เป็นต้น"
                        ></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            เหตุผลนี้จะถูกส่งไปยังผู้สมัครผ่านอีเมล กรุณาใช้ถ้อยคำที่สุภาพและให้ข้อมูลที่เป็นประโยชน์
                        </div>
                        <div class="character-count mt-1">
                            <small class="text-muted">
                                <span id="singleReasonCharCount">0</span>/1000 อักษร
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-danger mb-3">
                                <i class="fas fa-times-circle fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">ผลการปฏิเสธ</div>
                                    <small class="text-muted">สถานะจะเปลี่ยนเป็น "ปฏิเสธ"</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-secondary mb-3">
                                <i class="fas fa-user-times fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">สิทธิ์ผู้ใช้</div>
                                    <small class="text-muted">ไม่สามารถเข้าสู่ระบบได้</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-info mb-3">
                                <i class="fas fa-envelope fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">การแจ้งเตือน</div>
                                    <small class="text-muted">ส่งอีเมลพร้อมเหตุผล</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-warning mb-3">
                                <i class="fas fa-redo fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">โอกาสใหม่</div>
                                    <small class="text-muted">สามารถสมัครใหม่ได้</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="confirmSingleReject" required>
                        <label class="form-check-label fw-bold" for="confirmSingleReject">
                            ฉันยืนยันที่จะปฏิเสธการสมัครสมาชิกนี้ และได้ระบุเหตุผลที่เหมาะสมแล้ว
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>ปฏิเสธ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Character count for single rejection reason
document.addEventListener('DOMContentLoaded', function() {
    const reasonTextarea = document.getElementById('singleRejectionReason');
    const charCount = document.getElementById('singleReasonCharCount');
    
    if (reasonTextarea && charCount) {
        reasonTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            // Change color based on character count
            if (count > 800) {
                charCount.classList.remove('text-muted', 'text-warning');
                charCount.classList.add('text-danger');
            } else if (count > 600) {
                charCount.classList.remove('text-muted', 'text-danger');
                charCount.classList.add('text-warning');
            } else {
                charCount.classList.remove('text-warning', 'text-danger');
                charCount.classList.add('text-muted');
            }
        });
    }
});
</script>
