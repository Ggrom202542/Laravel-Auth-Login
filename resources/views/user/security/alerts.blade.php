@extends('layouts.dashboard')

@section('title', 'การแจ้งเตือนความปลอดภัย')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        การแจ้งเตือนความปลอดภัย
                    </h1>
                    <p class="text-muted mb-0">ติดตามกิจกรรมที่น่าสงสัยและเหตุการณ์ความปลอดภัยในบัญชีของคุณ</p>
                </div>
                <div>
                    <a href="{{ route('user.security.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> กลับไปยังความปลอดภัย
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshAlerts()">
                        <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                    </button>
                </div>
            </div>

            <!-- Alert Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-danger-subtle border-danger-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-exclamation-circle text-danger fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $alerts->where('severity', 'high')->count() }}</h3>
                            <p class="text-muted mb-0">ความเสี่ยงสูง</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-warning-subtle border-warning-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-exclamation-triangle text-warning fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $alerts->where('severity', 'medium')->count() }}</h3>
                            <p class="text-muted mb-0">ความเสี่ยงปานกลาง</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-info-circle text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $alerts->where('severity', 'low')->count() }}</h3>
                            <p class="text-muted mb-0">ความเสี่ยงต่ำ</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-list-ul text-primary fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $alerts->count() }}</h3>
                            <p class="text-muted mb-0">การแจ้งเตือนทั้งหมด</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Alerts List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            ประวัติการแจ้งเตือนความปลอดภัย
                        </h6>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="severityFilter" style="width: auto;">
                                <option value="">ความเสี่ยงทั้งหมด</option>
                                <option value="high">ความเสี่ยงสูง</option>
                                <option value="medium">ความเสี่ยงปานกลาง</option>
                                <option value="low">ความเสี่ยงต่ำ</option>
                            </select>
                            <select class="form-select form-select-sm" id="typeFilter" style="width: auto;">
                                <option value="">ประเภททั้งหมด</option>
                                <option value="suspicious_login">การเข้าสู่ระบบที่น่าสงสัย</option>
                                <option value="security_alert">การแจ้งเตือนความปลอดภัย</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($alerts->count() > 0)
                        @foreach($alerts as $alert)
                        <div class="alert-item border-start border-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }} border-4 p-3 mb-3 bg-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }}-subtle rounded" 
                             data-severity="{{ $alert['severity'] }}" 
                             data-type="{{ $alert['type'] }}">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm bg-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }}-subtle rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                    <i class="bi bi-{{ $alert['severity'] === 'high' ? 'exclamation-circle' : ($alert['severity'] === 'medium' ? 'exclamation-triangle' : 'info-circle') }} text-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $alert['message'] }}</h6>
                                            <div class="text-muted small mb-2">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ $alert['location'] }}
                                                @if($alert['ip_address'])
                                                    • <code class="bg-white px-2 py-1 rounded">{{ $alert['ip_address'] }}</code>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }}-subtle text-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }}">
                                                    ความเสี่ยง{{ $alert['severity'] === 'high' ? 'สูง' : ($alert['severity'] === 'medium' ? 'ปานกลาง' : 'ต่ำ') }}
                                                </span>
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    {{ $alert['type'] === 'suspicious_login' ? 'การเข้าสู่ระบบที่น่าสงสัย' : 'การแจ้งเตือนความปลอดภัย' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">{{ $alert['date']->diffForHumans() }}</div>
                                            <div class="small text-muted">{{ $alert['date']->format('M j, Y h:i A') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if($alerts->count() === 50)
                        <div class="text-center mt-4">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                แสดงการแจ้งเตือน 50 รายการล่าสุด การแจ้งเตือนเก่าจะถูกเก็บถาวรโดยอัตโนมัติ
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-shield-check text-success" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-success">ไม่มีการแจ้งเตือนความปลอดภัย</h5>
                            <p class="text-muted">ดีเยี่ยม! ไม่พบกิจกรรมที่น่าสงสัยในบัญชีของคุณ</p>
                            <div class="mt-3">
                                <a href="{{ route('user.security.index') }}" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-1"></i> ดูแดชบอร์ดความปลอดภัย
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Security Tips -->
            @if($alerts->count() > 0)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm bg-info-subtle">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-lightbulb text-info me-2"></i>
                                Security Tips
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Enable two-factor authentication for enhanced security
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Use strong, unique passwords for all accounts
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Only log in from trusted devices and networks
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Regularly review your login history and alerts
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Log out from devices you no longer use
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Report any suspicious activity immediately
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    .alert-item {
        transition: all 0.2s ease-in-out;
    }
    .alert-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const severityFilter = document.getElementById('severityFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    severityFilter.addEventListener('change', filterAlerts);
    typeFilter.addEventListener('change', filterAlerts);
    
    function filterAlerts() {
        const severityValue = severityFilter.value;
        const typeValue = typeFilter.value;
        const alertItems = document.querySelectorAll('.alert-item');
        
        alertItems.forEach(item => {
            let showItem = true;
            
            // Severity filter
            if (severityValue && item.dataset.severity !== severityValue) {
                showItem = false;
            }
            
            // Type filter
            if (typeValue && item.dataset.type !== typeValue) {
                showItem = false;
            }
            
            item.style.display = showItem ? '' : 'none';
        });
        
        // Update empty state
        const visibleItems = document.querySelectorAll('.alert-item[style=""], .alert-item:not([style])');
        const hasVisibleItems = Array.from(visibleItems).some(item => item.style.display !== 'none');
        
        if (!hasVisibleItems && document.querySelectorAll('.alert-item').length > 0) {
            showNoResultsMessage();
        } else {
            hideNoResultsMessage();
        }
    }
    
    function showNoResultsMessage() {
        const existingMessage = document.getElementById('noResultsMessage');
        if (existingMessage) return;
        
        const message = document.createElement('div');
        message.id = 'noResultsMessage';
        message.className = 'text-center py-4';
        message.innerHTML = `
            <i class="fas fa-search text-muted" style="font-size: 2rem;"></i>
            <h6 class="mt-2 text-muted">No alerts match your filters</h6>
            <p class="text-muted mb-0">Try adjusting your filter criteria.</p>
        `;
        
        const cardBody = document.querySelector('.card-body');
        cardBody.appendChild(message);
    }
    
    function hideNoResultsMessage() {
        const message = document.getElementById('noResultsMessage');
        if (message) {
            message.remove();
        }
    }
});

function refreshAlerts() {
    location.reload();
}
</script>
@endpush
@endsection
