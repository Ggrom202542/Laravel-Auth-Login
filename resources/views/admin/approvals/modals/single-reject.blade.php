<!-- Single Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-circle me-2"></i>
                    ปฏิเสธการสมัคร
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.approvals.reject', $approval) }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center mb-3">
                        คุณต้องการปฏิเสธการสมัครสมาชิกของ<br>
                        <strong>{{ $approval->user->prefix }}{{ $approval->user->first_name }} {{ $approval->user->last_name }}</strong><br>
                        หรือไม่?
                    </p>

                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>ผลของการปฏิเสธ:</h6>
                        <ul class="mb-0">
                            <li>ผู้ใช้จะไม่สามารถเข้าสู่ระบบได้</li>
                            <li>ระบบจะส่งอีเมลแจ้งการปฏิเสธพร้อมเหตุผล</li>
                            <li>สถานะบัญชีจะคงเป็น "ไม่เปิดใช้งาน"</li>
                        </ul>
                    </div>

                    <div class="row mb-3">
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

                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">
                            <strong>เหตุผลการปฏิเสธ <span class="text-danger">*</span></strong>
                        </label>
                        <textarea class="form-control @error('rejection_reason') is-invalid @enderror" 
                                  id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4" 
                                  placeholder="กรุณาระบุเหตุผลการปฏิเสธ เช่น ข้อมูลไม่ครบถ้วน, เอกสารไม่ชัดเจน ฯลฯ"
                                  required>{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            เหตุผลนี้จะถูกส่งไปยังผู้สมัครผ่านทางอีเมล
                        </div>
                    </div>

                    <!-- Common rejection reasons -->
                    <div class="mb-3">
                        <label class="form-label"><strong>เหตุผลทั่วไป:</strong></label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm reason-btn" 
                                    data-reason="ข้อมูลไม่ครบถ้วนหรือไม่ชัดเจน">
                                ข้อมูลไม่ครบถ้วน
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm reason-btn" 
                                    data-reason="เบอร์โทรศัพท์หรือข้อมูลการติดต่อไม่ถูกต้อง">
                                ข้อมูลติดต่อไม่ถูกต้อง
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm reason-btn" 
                                    data-reason="พบข้อมูลซ้ำในระบบ">
                                ข้อมูลซ้ำ
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm reason-btn" 
                                    data-reason="ไม่ผ่านเงื่อนไขการสมัครสมาชิก">
                                ไม่ผ่านเงื่อนไข
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>ยืนยันการปฏิเสธ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle reason buttons
    const reasonButtons = document.querySelectorAll('.reason-btn');
    const textarea = document.getElementById('rejection_reason');
    
    reasonButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reason = this.dataset.reason;
            textarea.value = reason;
        });
    });
});
</script>
