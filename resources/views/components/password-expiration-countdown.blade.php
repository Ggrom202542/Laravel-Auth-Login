@php
    use App\Services\PasswordExpirationService;
    $passwordService = app(PasswordExpirationService::class);
    $user = auth()->user();
    
    if ($user && $user->password_expiration_enabled) {
        $daysLeft = $passwordService->getDaysUntilExpiration($user);
        $isExpired = $passwordService->isPasswordExpired($user);
        $shouldShowWarning = $passwordService->shouldShowWarning($user);
        $passwordChangedAt = $user->password_changed_at;
        $passwordExpiresAt = $user->password_expires_at;
    }
@endphp

<!-- Password Status Session Alert -->
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

<!-- Password Expiration Countdown Widget -->
@if(isset($user) && $user->password_expiration_enabled && isset($passwordExpiresAt))
<div class="row mb-4">
    <div class="col-12">
        @if(isset($isExpired) && $isExpired)
            <!-- Expired Password Alert -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        รหัสผ่านหมดอายุแล้ว!
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-2">
                                <i class="bi bi-clock text-danger me-2"></i>
                                รหัสผ่านของคุณหมดอายุเมื่อ: <strong>{{ $passwordExpiresAt->format('d/m/Y H:i') }}</strong>
                            </p>
                            <p class="text-muted mb-0">
                                คุณจำเป็นต้องเปลี่ยนรหัสผ่านใหม่เพื่อใช้งานระบบต่อไป
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="expired-badge">
                                <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                                <div class="mt-2">
                                    <strong class="text-danger">หมดอายุแล้ว</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <a href="{{ route('password.change') }}" class="btn btn-danger btn-lg">
                            <i class="bi bi-key me-2"></i>
                            เปลี่ยนรหัสผ่านทันที
                        </a>
                    </div>
                </div>
            </div>
        @elseif(isset($shouldShowWarning) && $shouldShowWarning && isset($daysLeft))
            <!-- Password Expiring Soon Widget -->
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i>
                        การแจ้งเตือนรหัสผ่าน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-2">
                                <i class="bi bi-calendar-event text-warning me-2"></i>
                                รหัสผ่านจะหมดอายุในวันที่: <strong>{{ $passwordExpiresAt->format('d/m/Y H:i') }}</strong>
                            </p>
                            @if($passwordChangedAt)
                            <p class="text-muted small mb-3">
                                เปลี่ยนรหัสผ่านครั้งล่าสุด: {{ $passwordChangedAt->format('d/m/Y H:i') }}
                            </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-center">
                            <!-- Countdown Timer -->
                            <div class="countdown-widget">
                                <div class="countdown-circle" data-expires="{{ $passwordExpiresAt->toISOString() }}">
                                    <div class="countdown-number">
                                        <span id="daysLeft">{{ $daysLeft }}</span>
                                    </div>
                                    <div class="countdown-label">
                                        วันเหลือ
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    @php
                        $config = config('password_policy.expiration');
                        $totalDays = $config['days'] ?? 90;
                        $daysUsed = max(0, $totalDays - $daysLeft);
                        $progressPercentage = min(100, ($daysUsed / $totalDays) * 100);
                        
                        $progressColor = 'bg-success';
                        if ($progressPercentage > 80) {
                            $progressColor = 'bg-danger';
                        } elseif ($progressPercentage > 60) {
                            $progressColor = 'bg-warning';
                        }
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>วันที่ใช้งานรหัสผ่าน</small>
                            <small>{{ $daysUsed }}/{{ $totalDays }} วัน</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $progressColor }}" 
                                 role="progressbar" 
                                 style="width: {{ $progressPercentage }}%"
                                 aria-valuenow="{{ $progressPercentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Countdown Display -->
                    <div class="text-center mb-3">
                        <div id="detailedCountdown" class="countdown-detailed">
                            <div class="row">
                                <div class="col-3">
                                    <div class="countdown-item">
                                        <span id="countdownDays" class="countdown-digit">0</span>
                                        <small>วัน</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="countdown-item">
                                        <span id="countdownHours" class="countdown-digit">0</span>
                                        <small>ชม.</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="countdown-item">
                                        <span id="countdownMinutes" class="countdown-digit">0</span>
                                        <small>นาที</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="countdown-item">
                                        <span id="countdownSeconds" class="countdown-digit">0</span>
                                        <small>วิ.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('password.change') }}" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>
                            เปลี่ยนรหัสผ่านตอนนี้
                        </a>
                        <a href="{{ route('password.status') }}" class="btn btn-outline-info">
                            <i class="bi bi-graph-up me-2"></i>
                            ดูรายละเอียด
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="dismissPasswordWarning()">
                            <i class="bi bi-x me-2"></i>
                            ปิดการแจ้งเตือน
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Password Status OK - Minimal Display -->
            <div class="card border-success bg-light">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="bi bi-shield-check text-success"></i>
                        </div>
                        <div class="col">
                            <small class="text-muted">
                                รหัสผ่าน: 
                                @if(isset($daysLeft) && $daysLeft > 7)
                                    <span class="text-success">ปลอดภัย (เหลือ {{ $daysLeft }} วัน)</span>
                                @else
                                    <span class="text-muted">ใช้งานได้ปกติ</span>
                                @endif
                            </small>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="{{ route('password.change') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-key me-1"></i>
                                    เปลี่ยน
                                </a>
                                <a href="{{ route('password.status') }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-graph-up me-1"></i>
                                    ดูรายละเอียด
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Countdown Styles -->
<style>
.countdown-widget {
    display: flex;
    justify-content: center;
}

.countdown-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border: 4px solid white;
}

.countdown-number {
    font-size: 1.8rem;
    font-weight: bold;
    color: white;
    line-height: 1;
}

.countdown-label {
    font-size: 0.7rem;
    color: white;
    font-weight: 500;
}

.countdown-detailed {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    border: 1px solid #dee2e6;
}

.countdown-item {
    text-align: center;
}

.countdown-digit {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #495057;
}

.countdown-item small {
    color: #6c757d;
    font-size: 0.75rem;
}

.expired-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<!-- Countdown JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($passwordExpiresAt) && !$isExpired)
    const expirationDate = new Date('{{ $passwordExpiresAt->toISOString() }}');
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expirationDate.getTime() - now;
        
        if (distance > 0) {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Update detailed countdown
            document.getElementById('countdownDays').textContent = days;
            document.getElementById('countdownHours').textContent = hours;
            document.getElementById('countdownMinutes').textContent = minutes;
            document.getElementById('countdownSeconds').textContent = seconds;
            
            // Update main counter
            const daysLeftElement = document.getElementById('daysLeft');
            if (daysLeftElement) {
                daysLeftElement.textContent = days;
            }
        } else {
            // Password expired - reload page to show expired state
            location.reload();
        }
    }
    
    // Update immediately
    updateCountdown();
    
    // Update every second
    setInterval(updateCountdown, 1000);
    @endif
});

function dismissPasswordWarning() {
    // Hide the warning for this session
    const warningCard = document.querySelector('.card.border-warning');
    if (warningCard) {
        warningCard.style.display = 'none';
    }
    
    // Optional: Save dismissal preference
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
