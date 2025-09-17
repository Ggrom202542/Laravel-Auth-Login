<div class="row">
    <div class="col-md-6">
        <h6 class="fw-bold text-primary mb-3">
            <i class="bi bi-info-circle me-1"></i>
            ข้อมูลพื้นฐาน
        </h6>
        <table class="table table-borderless table-sm">
            <tr>
                <td class="text-muted" style="width: 40%;">IP Address:</td>
                <td class="fw-semibold">{{ $ipRecord->ip_address }}</td>
            </tr>
            <tr>
                <td class="text-muted">ประเภท:</td>
                <td>
                    @if($ipRecord->type === 'blacklist')
                        <span class="badge bg-danger">
                            <i class="bi bi-shield-x me-1"></i>Blacklist
                        </span>
                    @else
                        <span class="badge bg-success">
                            <i class="bi bi-shield-check me-1"></i>Whitelist
                        </span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted">สถานะ:</td>
                <td>
                    @if($ipRecord->status === 'active' && ($ipRecord->expires_at === null || $ipRecord->expires_at > now()))
                        <span class="badge bg-success">ใช้งาน</span>
                    @else
                        <span class="badge bg-secondary">ไม่ใช้งาน</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted">วันที่สร้าง:</td>
                <td>{{ $ipRecord->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @if($ipRecord->expires_at)
            <tr>
                <td class="text-muted">หมดอายุ:</td>
                <td class="text-warning">{{ $ipRecord->expires_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="fw-bold text-info mb-3">
            <i class="bi bi-graph-up me-1"></i>
            สถิติกิจกรรม (30 วันที่ผ่านมา)
        </h6>
        <table class="table table-borderless table-sm">
            <tr>
                <td class="text-muted" style="width: 50%;">ความพยายามล้มเหลว:</td>
                <td>
                    <span class="badge bg-warning">{{ $relatedData['recent_attempts'] }}</span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">เข้าสู่ระบบสำเร็จ:</td>
                <td>
                    <span class="badge bg-success">{{ $relatedData['successful_logins'] }}</span>
                </td>
            </tr>
        </table>
        
        @if($ipRecord->country || $ipRecord->city)
        <h6 class="fw-bold text-success mb-3 mt-4">
            <i class="bi bi-geo-alt me-1"></i>
            ข้อมูลตำแหน่ง
        </h6>
        <table class="table table-borderless table-sm">
            @if($ipRecord->country)
            <tr>
                <td class="text-muted" style="width: 30%;">ประเทศ:</td>
                <td>{{ $ipRecord->country }}</td>
            </tr>
            @endif
            @if($ipRecord->city)
            <tr>
                <td class="text-muted">เมือง:</td>
                <td>{{ $ipRecord->city }}</td>
            </tr>
            @endif
            @if($ipRecord->isp)
            <tr>
                <td class="text-muted">ISP:</td>
                <td class="small">{{ $ipRecord->isp }}</td>
            </tr>
            @endif
        </table>
        @endif
    </div>
</div>

@if($ipRecord->reason)
<div class="row mt-3">
    <div class="col-12">
        <h6 class="fw-bold text-dark mb-2">
            <i class="bi bi-file-text me-1"></i>
            เหตุผล
        </h6>
        <div class="alert alert-light border-start border-primary border-3 mb-2">
            {{ $ipRecord->reason }}
        </div>
    </div>
</div>
@endif

@if($ipRecord->description)
<div class="row">
    <div class="col-12">
        <h6 class="fw-bold text-dark mb-2">
            <i class="bi bi-card-text me-1"></i>
            คำอธิบาย
        </h6>
        <div class="alert alert-light border-start border-info border-3 mb-2">
            {{ $ipRecord->description }}
        </div>
    </div>
</div>
@endif

<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex gap-2">
            @if($ipRecord->type === 'blacklist')
                <button type="button" class="btn btn-success btn-sm" onclick="moveToWhitelist({{ $ipRecord->id }})">
                    <i class="bi bi-shield-check me-1"></i>
                    ย้ายไป Whitelist
                </button>
            @else
                <button type="button" class="btn btn-warning btn-sm" onclick="moveToBlacklist({{ $ipRecord->id }})">
                    <i class="bi bi-shield-x me-1"></i>
                    ย้ายไป Blacklist
                </button>
            @endif
            
            <button type="button" class="btn btn-danger btn-sm" onclick="removeIpRestriction({{ $ipRecord->id }})">
                <i class="bi bi-trash me-1"></i>
                ลบ Restriction
            </button>
            
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshIpInfo({{ $ipRecord->id }})">
                <i class="bi bi-arrow-clockwise me-1"></i>
                รีเฟรชข้อมูล
            </button>
        </div>
    </div>
</div>

<script>
function moveToWhitelist(ipId) {
    if (confirm('คุณต้องการย้าย IP นี้ไป Whitelist หรือไม่?')) {
        // Implementation for moving to whitelist
        alert('ฟีเจอร์นี้กำลังพัฒนา');
    }
}

function moveToBlacklist(ipId) {
    if (confirm('คุณต้องการย้าย IP นี้ไป Blacklist หรือไม่?')) {
        // Implementation for moving to blacklist  
        alert('ฟีเจอร์นี้กำลังพัฒนา');
    }
}

function removeIpRestriction(ipId) {
    if (confirm('คุณต้องการลบ IP restriction นี้หรือไม่?')) {
        // Implementation for removing restriction
        alert('ฟีเจอร์นี้กำลังพัฒนา');
    }
}

function refreshIpInfo(ipId) {
    // Reload the modal content
    viewIpDetails(ipId);
}
</script>
