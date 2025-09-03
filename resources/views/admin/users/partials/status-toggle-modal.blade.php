<!-- Status Toggle Modal -->
<div class="modal fade" id="statusToggleModal" tabindex="-1" aria-labelledby="statusToggleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="statusToggleForm" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="new_status" id="new-status">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="statusToggleModalLabel">
                        <i class="fas fa-user-cog me-2"></i>
                        เปลี่ยนสถานะผู้ใช้
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body text-center">
                    <!-- Status Icon -->
                    <div class="mb-3" id="status-icon">
                        <i class="fas fa-user text-secondary" style="font-size: 3rem;"></i>
                    </div>
                    
                    <!-- Status Message -->
                    <h5 id="status-message">เปลี่ยนสถานะผู้ใช้</h5>
                    <p class="text-muted" id="status-description">คำอธิบายการเปลี่ยนสถานะ</p>
                    
                    <!-- Warning -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        การเปลี่ยนสถานะจะมีผลทันที และผู้ใช้จะได้รับการแจ้งเตือนทางอีเมล
                    </div>
                </div>
                
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        ยกเลิก
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmStatusToggle">
                        <i class="fas fa-check me-1"></i>
                        ยืนยัน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
