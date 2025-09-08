<!-- Password Expiration Warning Component -->
@if(session('password_warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>แจ้งเตือนรหัสผ่าน:</strong> {{ session('password_warning') }}
    <a href="{{ route('password.change') }}" class="btn btn-sm btn-outline-warning ms-2">
        <i class="bi bi-key me-1"></i>
        เปลี่ยนรหัสผ่าน
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
    use App\Services\PasswordExpirationService;
    $passwordService = app(PasswordExpirationService::class);
    $user = auth()->user();
    
    if ($user) {
        $daysLeft = $passwordService->getDaysUntilExpiration($user);
        $shouldShowWarning = $passwordService->shouldShowWarning($user);
    }
@endphp

@if(isset($shouldShowWarning) && $shouldShowWarning && isset($daysLeft))
<div class="card border-warning mb-3">
    <div class="card-header bg-warning text-dark">
        <h6 class="card-title mb-0">
            <i class="bi bi-clock me-2"></i>
            การแจ้งเตือนรหัสผ่าน
        </h6>
    </div>
    <div class="card-body">
        @if($daysLeft > 0)
            <p class="mb-2">
                <i class="bi bi-info-circle text-warning me-2"></i>
                รหัสผ่านของคุณจะหมดอายุใน <strong>{{ $daysLeft }} วัน</strong>
            </p>
            <p class="text-muted small mb-3">
                เพื่อความปลอดภัยของบัญชีผู้ใช้ กรุณาเปลี่ยนรหัสผ่านใหม่ก่อนที่จะหมดอายุ
            </p>
        @else
            <p class="mb-2">
                <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                รหัสผ่านของคุณจะหมดอายุ<strong>วันนี้</strong>
            </p>
            <p class="text-muted small mb-3">
                กรุณาเปลี่ยนรหัสผ่านใหม่ทันทีเพื่อความปลอดภัยของบัญชีผู้ใช้
            </p>
        @endif
        
        <div class="d-flex gap-2">
            <a href="{{ route('password.change') }}" class="btn btn-warning">
                <i class="bi bi-key me-2"></i>
                เปลี่ยนรหัสผ่านตอนนี้
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="dismissPasswordWarning()">
                <i class="bi bi-x me-2"></i>
                ปิดการแจ้งเตือน
            </button>
        </div>
    </div>
</div>

<script>
function dismissPasswordWarning() {
    // Hide the warning for this session
    document.querySelector('.card.border-warning').style.display = 'none';
    
    // Optional: Make an AJAX call to remember user's choice
    fetch('/api/user/dismiss-password-warning', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).catch(error => {
        console.log('Could not save dismissal preference:', error);
    });
}
</script>
@endif
