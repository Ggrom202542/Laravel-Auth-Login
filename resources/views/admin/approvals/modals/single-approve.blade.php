<!-- Single Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-circle me-2"></i>
                    ยืนยันการอนุมัติ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.approvals.approve', $approval) }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center mb-3">
                        คุณต้องการอนุมัติการสมัครสมาชิกของ<br>
                        <strong>{{ $approval->user->prefix }}{{ $approval->user->first_name }} {{ $approval->user->last_name }}</strong><br>
                        หรือไม่?
                    </p>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>ข้อมูลการอนุมัติ:</h6>
                        <ul class="mb-0">
                            <li>ผู้ใช้จะสามารถเข้าสู่ระบบได้ทันที</li>
                            <li>ระบบจะส่งอีเมลแจ้งการอนุมัติ</li>
                            <li>สถานะบัญชีจะเปลี่ยนเป็น "ใช้งานได้"</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Username:</strong> {{ $approval->user->username }}
                        </div>
                        <div class="col-md-6">
                            <strong>เบอร์โทร:</strong> {{ $approval->user->phone }}
                        </div>
                        <div class="col-12 mt-2">
                            <strong>อีเมล:</strong> {{ $approval->user->email ?? 'ไม่ได้ระบุ' }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>ยืนยันการอนุมัติ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
