<!-- Status Toggle Modal -->
<div class="modal fade" id="statusToggleModal" tabindex="-1" aria-labelledby="statusToggleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusToggleModalLabel">
                    <i class="bi bi-person-gear me-2"></i>
                    เปลี่ยนสถานะผู้ใช้
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div id="status-icon" class="mb-3">
                        <!-- Dynamic icon will be inserted here -->
                    </div>
                    <h5 id="status-message">
                        <!-- Dynamic message will be inserted here -->
                    </h5>
                    <p class="text-muted" id="status-description">
                        <!-- Dynamic description will be inserted here -->
                    </p>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>หมายเหตุ:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>ใช้งานได้:</strong> ผู้ใช้สามารถเข้าสู่ระบบและใช้งานได้ปกติ</li>
                        <li><strong>ไม่ใช้งาน:</strong> ผู้ใช้ไม่สามารถเข้าสู่ระบบได้ (ปิดใช้งานชั่วคราว)</li>
                        <li><strong>ถูกระงับ:</strong> บัญชีถูกระงับเนื่องจากการละเมิดกฎระเบียบ</li>
                    </ul>
                </div>
                
                <form id="statusToggleForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="new-status" name="status" value="">
                    
                    <div class="mb-3">
                        <label for="status-reason" class="form-label">เหตุผลในการเปลี่ยนสถานะ (ไม่บังคับ)</label>
                        <textarea class="form-control" id="status-reason" name="reason" rows="3" 
                                  placeholder="ระบุเหตุผลในการเปลี่ยนสถานะ..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-2"></i>ยกเลิก
                </button>
                <button type="button" class="btn" id="confirmStatusToggle">
                    <i class="bi bi-check2 me-2"></i>ยืนยัน
                </button>
            </div>
        </div>
    </div>
</div>
