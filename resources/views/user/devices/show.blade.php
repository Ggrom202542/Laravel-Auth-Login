<!-- Device Details Content -->
<div class="row">
    <div class="col-md-4 text-center mb-4">
        <!-- Device Icon Large -->
        <div class="avatar-xl {{ $device->is_trusted ? 'bg-success-subtle' : 'bg-warning-subtle' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
            @if($device->device_type === 'mobile')
                <i class="bi bi-phone {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-1"></i>
            @elseif($device->device_type === 'tablet')
                <i class="bi bi-tablet {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-1"></i>
            @else
                <i class="bi bi-pc-display {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-1"></i>
            @endif
        </div>
        
        <h4 class="fw-bold text-dark mb-2">{{ $device->device_name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}</h4>
        
        @if($device->is_trusted)
            <span class="badge bg-success fs-6 px-3 py-2">
                <i class="bi bi-shield-check me-2"></i> อุปกรณ์ที่เชื่อถือได้
            </span>
        @else
            <span class="badge bg-warning fs-6 px-3 py-2">
                <i class="bi bi-question-circle me-2"></i> อุปกรณ์ที่ไม่เชื่อถือ
            </span>
        @endif
        
        @if($device->last_seen_at && $device->last_seen_at->gt(now()->subHours(1)))
            <div class="mt-2">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="bi bi-circle-fill me-2" style="font-size: 8px;"></i> ออนไลน์
                </span>
            </div>
        @endif
    </div>
    
    <div class="col-md-8">
        <!-- Device Information -->
        <h5 class="fw-bold text-dark mb-3">
            <i class="bi bi-info-circle text-primary me-2"></i>
            ข้อมูลอุปกรณ์
        </h5>
        
        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted fw-semibold">ประเภทอุปกรณ์:</small>
            </div>
            <div class="col-sm-8">
                <span class="text-dark">
                    @if($device->device_type === 'mobile')
                        <i class="bi bi-phone me-1"></i> มือถือ
                    @elseif($device->device_type === 'tablet')
                        <i class="bi bi-tablet me-1"></i> แท็บเล็ต
                    @else
                        <i class="bi bi-pc-display me-1"></i> คอมพิวเตอร์
                    @endif
                </span>
            </div>
        </div>
        
        @if($device->platform)
        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted fw-semibold">ระบบปฏิบัติการ:</small>
            </div>
            <div class="col-sm-8">
                <span class="text-dark">{{ $device->platform ?? $device->operating_system ?? 'ไม่ทราบ' }}</span>
            </div>
        </div>
        @endif
        
        @if($device->browser_name)
        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted fw-semibold">เบราว์เซอร์:</small>
            </div>
            <div class="col-sm-8">
                <span class="text-dark">{{ $device->browser_name }}</span>
                @if($device->browser_version)
                    <small class="text-muted">(เวอร์ชัน {{ $device->browser_version }})</small>
                @endif
            </div>
        </div>
        @endif
        
        @if($device->ip_address)
        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted fw-semibold">ที่อยู่ IP:</small>
            </div>
            <div class="col-sm-8">
                <code class="text-muted">{{ $device->ip_address }}</code>
                @if($device->location)
                    <br><small class="text-muted">
                        <i class="bi bi-geo-alt me-1"></i>{{ $device->location }}
                    </small>
                @endif
            </div>
        </div>
        @endif
        
        @if($device->screen_resolution)
        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted fw-semibold">ความละเอียดหน้าจอ:</small>
            </div>
            <div class="col-sm-8">
                <span class="text-dark">{{ $device->screen_resolution }}</span>
            </div>
        </div>
        @endif
        
        @if($device->language)
        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted fw-semibold">ภาษา:</small>
            </div>
            <div class="col-sm-8">
                <span class="text-dark">{{ $device->language }}</span>
            </div>
        </div>
        @endif
        
        @if($device->user_agent)
        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted fw-semibold">User Agent:</small>
            </div>
            <div class="col-sm-8">
                <small class="text-muted font-monospace">{{ Str::limit($device->user_agent, 100) }}</small>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Activity Information -->
<hr class="my-4">
<h5 class="fw-bold text-dark mb-3">
    <i class="bi bi-clock-history text-primary me-2"></i>
    ข้อมูลกิจกรรม
</h5>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <i class="bi bi-calendar-plus text-info fs-3 mb-2"></i>
                <h6 class="fw-semibold text-dark mb-1">ลงทะเบียนครั้งแรก</h6>
                @if($device->first_seen_at ?? $device->created_at)
                    <p class="text-muted mb-0">{{ ($device->first_seen_at ?? $device->created_at)->format('d M Y H:i:s') }}</p>
                    <small class="text-muted">{{ ($device->first_seen_at ?? $device->created_at)->diffForHumans() }}</small>
                @else
                    <p class="text-muted mb-0">ไม่ทราบ</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <i class="bi bi-clock text-success fs-3 mb-2"></i>
                <h6 class="fw-semibold text-dark mb-1">ใช้งานล่าสุด</h6>
                @if($device->last_seen_at)
                    <p class="text-muted mb-0">{{ $device->last_seen_at->format('d M Y H:i:s') }}</p>
                    <small class="text-muted">{{ $device->last_seen_at->diffForHumans() }}</small>
                @else
                    <p class="text-muted mb-0">ไม่เคยใช้งาน</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($device->login_count ?? false)
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <i class="bi bi-arrow-repeat text-warning fs-3 mb-2"></i>
                <h6 class="fw-semibold text-dark mb-1">จำนวนการเข้าสู่ระบบ</h6>
                <p class="text-muted mb-0">{{ $device->login_count }} ครั้ง</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                @if($device->is_active ?? true)
                    <i class="bi bi-check-circle text-success fs-3 mb-2"></i>
                    <h6 class="fw-semibold text-dark mb-1">สถานะ</h6>
                    <p class="text-success mb-0">ใช้งานอยู่</p>
                @else
                    <i class="bi bi-x-circle text-danger fs-3 mb-2"></i>
                    <h6 class="fw-semibold text-dark mb-1">สถานะ</h6>
                    <p class="text-danger mb-0">ไม่ได้ใช้งาน</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Action Buttons -->
<hr class="my-4">
<div class="d-flex justify-content-between align-items-center">
    <div>
        @if($device->is_trusted)
            <form action="{{ route('user.devices.untrust', $device) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning" 
                        onclick="return confirm('ยกเลิกการเชื่อถืออุปกรณ์นี้หรือไม่?')">
                    <i class="bi bi-x me-2"></i> ยกเลิกการเชื่อถือ
                </button>
            </form>
        @else
            <form action="{{ route('user.devices.trust', $device) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-shield-check me-2"></i> เชื่อถืออุปกรณ์
                </button>
            </form>
        @endif
    </div>
    
    <div>
        <form action="{{ route('user.devices.destroy', $device) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" 
                    onclick="return confirm('ลบอุปกรณ์นี้หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้')">
                <i class="bi bi-trash me-2"></i> ลบอุปกรณ์
            </button>
        </form>
    </div>
</div>

<style>
.avatar-xl {
    width: 80px;
    height: 80px;
}

.font-monospace {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.875rem;
}
</style>
