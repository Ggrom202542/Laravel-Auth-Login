@extends('layouts.dashboard')

@section('title', 'จัดการ Sessions')

@section('page_title', 'จัดการ Sessions ที่ใช้งาน')
@section('page_subtitle', 'ติดตาม Sessions ของผู้ใช้ที่กำลังใช้งานระบบ')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>
                    ตัวกรองการค้นหา
                </h5>
            </div>
            <div class="card-body">
                <form id="sessionFilterForm" method="GET" action="{{ route('super-admin.users.sessions') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">ค้นหา</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="ชื่อ, Email, หรือ IP Address">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="user_role" class="form-label">บทบาท</label>
                            <select class="form-select" id="user_role" name="user_role">
                                <option value="">ทั้งหมด</option>
                                <option value="super_admin" {{ request('user_role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="admin" {{ request('user_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="user" {{ request('user_role') == 'user' ? 'selected' : '' }}>User</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> ค้นหา
                                </button>
                                <a href="{{ route('super-admin.users.sessions') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> ล้าง
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sessions Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users-cog me-2"></i>
                    Sessions ที่ใช้งาน ({{ $sessions->total() }} รายการ)
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-info" id="refreshSessions">
                        <i class="fas fa-sync-alt"></i> รีเฟรช
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="sessionsContainer">
                    @include('admin.super-admin.users.sessions-table', ['sessions' => $sessions])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.session-row:hover {
    background-color: #f8f9fa;
}

.session-status {
    font-size: 0.75rem;
}

.table td {
    vertical-align: middle;
}

.user-info {
    min-width: 200px;
}

.session-info {
    min-width: 250px;
}

.activity-info {
    min-width: 120px;
}

.text-truncate-custom {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto refresh sessions every 30 seconds
    let refreshInterval;
    
    function startAutoRefresh() {
        refreshInterval = setInterval(function() {
            refreshSessions(false);
        }, 30000); // 30 seconds
    }
    
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }
    
    // Start auto refresh
    startAutoRefresh();
    
    // Manual refresh
    $('#refreshSessions').on('click', function() {
        refreshSessions(true);
    });
    
    // Refresh sessions function
    function refreshSessions(showLoading = true) {
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        
        if (showLoading) {
            $('#sessionsContainer').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </div>
            `);
        }
        
        $.get(url.toString())
            .done(function(response) {
                if (response.success) {
                    $('#sessionsContainer').html(response.html);
                    
                    // Update pagination if exists
                    if (response.pagination) {
                        $('.pagination-container').html(response.pagination);
                    }
                } else {
                    showAlert('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
                }
            })
            .fail(function() {
                showAlert('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
    }
    
    // Filter form submission
    $('#sessionFilterForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const newUrl = window.location.pathname + '?' + formData;
        
        // Update URL without reload
        window.history.pushState({}, '', newUrl);
        
        // Refresh sessions with new filters
        refreshSessions(true);
    });
    
    // Terminate session function (delegated event)
    $(document).on('click', '.terminate-session-btn', function(e) {
        e.preventDefault();
        
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        
        if (!confirm(`คุณต้องการยกเลิกเซสชันทั้งหมดของ "${userName}" หรือไม่?`)) {
            return;
        }
        
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...');
        
        $.post(`{{ route('super-admin.users.terminate-sessions', ['id' => '__ID__']) }}`.replace('__ID__', userId), {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                showAlert('success', response.message);
                refreshSessions(false);
            } else {
                showAlert('error', response.message || 'เกิดข้อผิดพลาดในการยกเลิกเซสชัน');
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showAlert('error', response?.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
        })
        .always(function() {
            btn.prop('disabled', false).html(originalText);
        });
    });
    
    // Stop auto refresh when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        stopAutoRefresh();
    });
    
    // Show alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of content
        $('.card').first().before(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>
@endsection
