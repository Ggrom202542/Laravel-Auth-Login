<!-- Single Approve Modal -->
<div class="modal fade" id="singleApproveModal" tabindex="-1" aria-labelledby="singleApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="singleApproveModalLabel">
                    <i class="fas fa-check-circle me-2"></i>อนุมัติการสมัครสมาชิก
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="singleApproveForm" method="POST">
                @csrf
                <input type="hidden" name="approval_id" id="singleApproveUserId">
                
                <div class="modal-body">
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="fas fa-info-circle me-3 fs-4"></i>
                        <div>
                            <strong>คุณกำลังจะอนุมัติการสมัครสมาชิกของ</strong>
                            <p class="mb-0 mt-1">
                                <span id="singleApproveUserName" class="fw-bold text-primary"></span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-success mb-3">
                                <i class="fas fa-check-circle fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">ผลการอนุมัติ</div>
                                    <small class="text-muted">สถานะจะเปลี่ยนเป็น "อนุมัติแล้ว"</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-primary mb-3">
                                <i class="fas fa-user-check fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">สิทธิ์ผู้ใช้</div>
                                    <small class="text-muted">สามารถเข้าสู่ระบบได้</small>
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
                                    <small class="text-muted">ส่งอีเมลอัตโนมัติ</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-secondary mb-3">
                                <i class="fas fa-history fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">บันทึกประวัติ</div>
                                    <small class="text-muted">เก็บ log การอนุมัติ</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="confirmSingleApprove" required>
                        <label class="form-check-label fw-bold" for="confirmSingleApprove">
                            ฉันยืนยันที่จะอนุมัติการสมัครสมาชิกนี้
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>อนุมัติ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
