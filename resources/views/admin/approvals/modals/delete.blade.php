<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ยืนยันการลบข้อมูล
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">คุณต้องการลบข้อมูลการสมัครของ</h6>
                <div class="text-center">
                    <strong id="deleteUserName" class="text-danger"></strong>
                </div>
                <div class="alert alert-warning mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>คำเตือน:</strong> การดำเนินการนี้ไม่สามารถยกเลิกได้ ข้อมูลจะถูกลบถาวร
                    @if(auth()->user()->role === 'super_admin')
                        <br><small class="text-muted mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            เฉพาะ Super Admin เท่านั้นที่สามารถลบข้อมูลได้
                        </small>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>ยกเลิก
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>ลบข้อมูล
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteModal(approvalId, userName) {
    // Set user name in modal
    document.getElementById('deleteUserName').textContent = userName;
    
    // Set form action URL - ใช้ Laravel route helper ในการสร้าง URL
    const form = document.getElementById('deleteForm');
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/super-admin/')) {
        // Super Admin route
        form.action = `{{ url('/super-admin/approvals') }}/${approvalId}`;
    } else {
        // Admin route  
        form.action = `{{ url('/admin/approvals') }}/${approvalId}`;
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
