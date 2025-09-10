@extends('layouts.dashboard')

@section('title', 'ประวัติการเข้าสู่ระบบ')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        ประวัติการเข้าสู่ระบบ
                    </h1>
                    <p class="text-muted mb-0">ดูประวัติการเข้าถึงบัญชีและเหตุการณ์ความปลอดภัยของคุณ</p>
                </div>
                <div>
                    <a href="{{ route('user.security.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> กลับไปยังความปลอดภัย
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshHistory()">
                        <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                    </button>
                </div>
            </div>

            <!-- Login Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-check-circle text-success fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ number_format($loginStats['successful_logins']) }}</h3>
                            <p class="text-muted mb-0">เข้าสู่ระบบสำเร็จ</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-danger-subtle border-danger-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-x-circle text-danger fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $loginStats['failed_attempts'] }}</h3>
                            <p class="text-muted mb-0">ความพยายามที่ล้มเหลว</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-warning-subtle border-warning-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-exclamation-triangle text-warning fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $loginStats['suspicious_attempts'] }}</h3>
                            <p class="text-muted mb-0">ความพยายามที่น่าสงสัย</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-list-ul text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ number_format($loginStats['total_attempts']) }}</h3>
                            <p class="text-muted mb-0">ความพยายามทั้งหมด</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login History Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-list-ul text-primary me-2"></i>
                            บันทึกกิจกรรมการเข้าสู่ระบบ
                        </h6>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                                <option value="">สถานะทั้งหมด</option>
                                <option value="success">สำเร็จ</option>
                                <option value="failed">ล้มเหลว</option>
                            </select>
                            <select class="form-select form-select-sm" id="timeFilter" style="width: auto;">
                                <option value="">ทุกช่วงเวลา</option>
                                <option value="today">วันนี้</option>
                                <option value="week">สัปดาห์นี้</option>
                                <option value="month">เดือนนี้</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($loginAttempts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 ps-4">สถานะ</th>
                                        <th class="border-0">วันที่และเวลา</th>
                                        <th class="border-0">ที่อยู่ IP</th>
                                        <th class="border-0">ตำแหน่ง</th>
                                        <th class="border-0">อุปกรณ์</th>
                                        <th class="border-0">เบราว์เซอร์</th>
                                        <th class="border-0">ความเสี่ยง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loginAttempts as $attempt)
                                    <tr class="login-row" data-status="{{ $attempt->status }}" data-date="{{ $attempt->attempted_at->format('Y-m-d') }}">
                                        <td class="ps-4" style="text-align: center;">
                                            @if($attempt->status === 'success')
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="bi bi-check-circle me-1"></i>สำเร็จ
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="bi bi-x-circle me-1"></i>ล้มเหลว
                                                </span>
                                            @endif
                                            
                                            @if($attempt->is_suspicious)
                                                <span class="badge bg-warning-subtle text-warning ms-1">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>น่าสงสัย
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $attempt->attempted_at->format('M j, Y') }}</div>
                                            <div class="small text-muted">{{ $attempt->attempted_at->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            <code class="bg-light px-2 py-1 rounded">{{ $attempt->ip_address }}</code>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $attempt->city ?? 'ไม่ทราบ' }}</div>
                                            <div class="small text-muted">{{ $attempt->country_name ?? 'ตำแหน่งไม่ทราบ' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-{{ $attempt->device_type === 'mobile' ? 'phone' : ($attempt->device_type === 'tablet' ? 'tablet' : 'laptop') }} text-muted me-2"></i>
                                                <div>
                                                    <div class="fw-medium">{{ $attempt->device_type ?? 'ไม่ทราบ' }}</div>
                                                    <div class="small text-muted">{{ $attempt->operating_system ?? 'ไม่มีข้อมูล' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $attempt->browser_name ?? 'ไม่ทราบ' }}</div>
                                            <div class="small text-muted">{{ $attempt->browser_version ?? 'ไม่มีข้อมูล' }}</div>
                                        </td>
                                        <td>
                                            @if($attempt->risk_score)
                                                @php
                                                    $riskLevel = $attempt->risk_score >= 70 ? 'high' : ($attempt->risk_score >= 40 ? 'medium' : 'low');
                                                    $riskColor = $riskLevel === 'high' ? 'danger' : ($riskLevel === 'medium' ? 'warning' : 'success');
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar bg-{{ $riskColor }}" style="width: {{ $attempt->risk_score }}%"></div>
                                                    </div>
                                                    <span class="badge bg-{{ $riskColor }}-subtle text-{{ $riskColor }}">
                                                        {{ $attempt->risk_score }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-muted">ไม่มีข้อมูล</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center p-3">
                            {{ $loginAttempts->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-clock-history text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">ไม่มีประวัติการเข้าสู่ระบบ</h5>
                            <p class="text-muted">กิจกรรมการเข้าสู่ระบบของคุณจะปรากฏที่นี่</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .progress {
        background-color: #e9ecef;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const statusFilter = document.getElementById('statusFilter');
    const timeFilter = document.getElementById('timeFilter');
    
    statusFilter.addEventListener('change', filterTable);
    timeFilter.addEventListener('change', filterTable);
    
    function filterTable() {
        const statusValue = statusFilter.value;
        const timeValue = timeFilter.value;
        const rows = document.querySelectorAll('.login-row');
        
        rows.forEach(row => {
            let showRow = true;
            
            // Status filter
            if (statusValue && row.dataset.status !== statusValue) {
                showRow = false;
            }
            
            // Time filter
            if (timeValue && showRow) {
                const rowDate = new Date(row.dataset.date);
                const now = new Date();
                
                switch (timeValue) {
                    case 'today':
                        if (rowDate.toDateString() !== now.toDateString()) {
                            showRow = false;
                        }
                        break;
                    case 'week':
                        const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                        if (rowDate < weekAgo) {
                            showRow = false;
                        }
                        break;
                    case 'month':
                        const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                        if (rowDate < monthAgo) {
                            showRow = false;
                        }
                        break;
                }
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
});

function refreshHistory() {
    location.reload();
}
</script>
@endpush
@endsection
