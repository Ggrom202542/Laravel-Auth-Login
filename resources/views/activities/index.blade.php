@extends('layouts.dashboard')

@section('title', 'ประวัติกิจกรรม')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-clock-history me-2"></i>ประวัติกิจกรรม
    </h1>
    <div class="d-flex gap-2">
        @if($canViewAll)
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#chartModal">
                <i class="bi bi-graph-up me-1"></i>ดูกราฟ
            </button>
        @endif
        <button type="button" class="btn btn-success btn-sm" onclick="exportActivities()">
            <i class="bi bi-download me-1"></i>ส่งออกข้อมูล
        </button>
    </div>
</div>

<!-- สถิติภาพรวม -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            กิจกรรมทั้งหมด
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_activities']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-list-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            กิจกรรมวันนี้
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['activities_today']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            กิจกรรมน่าสงสัย
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['suspicious_activities']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            IP Address ที่แตกต่าง
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['unique_ips']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-globe fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ตัวกรอง -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-funnel me-2"></i>ตัวกรองข้อมูล
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('activities.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="activity_type" class="form-label">ประเภทกิจกรรม</label>
                    <select name="activity_type" id="activity_type" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('activity_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                
                @if($canViewAll)
                <div class="col-md-3 mb-3">
                    <label for="user_id" class="form-label">ผู้ใช้</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="col-md-2 mb-3">
                    <label for="ip_address" class="form-label">IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" class="form-control" 
                           placeholder="ค้นหา IP" value="{{ request('ip_address') }}">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="sort_by" class="form-label">เรียงลำดับตาม</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>วันที่</option>
                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>รหัสกิจกรรม</option>
                        <option value="activity_type" {{ request('sort_by') == 'activity_type' ? 'selected' : '' }}>ประเภท</option>
                        <option value="ip_address" {{ request('sort_by') == 'ip_address' ? 'selected' : '' }}>IP Address</option>
                        <option value="is_suspicious" {{ request('sort_by') == 'is_suspicious' ? 'selected' : '' }}>สถานะ</option>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="sort_order" class="form-label">ลำดับ</label>
                    <select name="sort_order" id="sort_order" class="form-select">
                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>ล่าสุดก่อน</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>เก่าสุดก่อน</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="suspicious" value="1" 
                               id="suspicious" {{ request('suspicious') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="suspicious">
                            แสดงเฉพาะกิจกรรมที่น่าสงสัย
                        </label>
                    </div>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                    <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- รายการกิจกรรม -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-list-ul me-2"></i>รายการประวัติกิจกรรม
            <small class="text-muted ms-2">
                (คลิกหัวตารางเพื่อเรียงลำดับ)
            </small>
        </h6>
        @if($activities->total() > 0)
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark me-2">
                    <i class="bi bi-collection me-1"></i>{{ number_format($activities->total()) }} รายการ
                </span>
                <span class="badge bg-primary">
                    <i class="bi bi-file-earmark-text me-1"></i>หน้า {{ $activities->currentPage() }}/{{ $activities->lastPage() }}
                </span>
                @if(request('sort_by'))
                    <span class="badge bg-success ms-2">
                        <i class="bi bi-sort-down me-1"></i>
                        เรียงตาม: {{ 
                            request('sort_by') == 'created_at' ? 'วันที่' : 
                            (request('sort_by') == 'id' ? 'รหัส' : 
                            (request('sort_by') == 'activity_type' ? 'ประเภท' : 
                            (request('sort_by') == 'ip_address' ? 'IP' : 'สถานะ')))
                        }}
                        ({{ request('sort_order', 'desc') == 'desc' ? 'ล่าสุดก่อน' : 'เก่าสุดก่อน' }})
                    </span>
                @endif
            </div>
        @endif
    </div>
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="activitiesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'id', 'sort_order' => request('sort_by') == 'id' && request('sort_order', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    #
                                    @if(request('sort_by') == 'id')
                                        <i class="bi bi-chevron-{{ request('sort_order', 'desc') == 'desc' ? 'down' : 'up' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="12%">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_by') == 'created_at' && request('sort_order', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    วันที่/เวลา
                                    @if(request('sort_by', 'created_at') == 'created_at')
                                        <i class="bi bi-chevron-{{ request('sort_order', 'desc') == 'desc' ? 'down' : 'up' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            @if($canViewAll)
                            <th width="15%">ผู้ใช้</th>
                            @endif
                            <th width="15%">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'activity_type', 'sort_order' => request('sort_by') == 'activity_type' && request('sort_order', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    ประเภท
                                    @if(request('sort_by') == 'activity_type')
                                        <i class="bi bi-chevron-{{ request('sort_order', 'desc') == 'desc' ? 'down' : 'up' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="25%">คำอธิบาย</th>
                            <th width="12%">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'ip_address', 'sort_order' => request('sort_by') == 'ip_address' && request('sort_order', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    IP Address
                                    @if(request('sort_by') == 'ip_address')
                                        <i class="bi bi-chevron-{{ request('sort_order', 'desc') == 'desc' ? 'down' : 'up' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="8%">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'is_suspicious', 'sort_order' => request('sort_by') == 'is_suspicious' && request('sort_order', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center">
                                    สถานะ
                                    @if(request('sort_by') == 'is_suspicious')
                                        <i class="bi bi-chevron-{{ request('sort_order', 'desc') == 'desc' ? 'down' : 'up' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="8%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                        <tr class="{{ $activity->is_suspicious ? 'table-warning' : '' }}">
                            <td>{{ $activity->id }}</td>
                            <td>
                                <small class="text-muted">{{ $activity->created_at->format('d/m/Y') }}</small><br>
                                <strong>{{ $activity->created_at->format('H:i:s') }}</strong>
                            </td>
                            @if($canViewAll)
                            <td>
                                @if($activity->user)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $activity->user->profile_image ? asset('storage/avatars/'.$activity->user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($activity->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                             alt="{{ $activity->user->name }}" 
                                             class="rounded-circle me-2" style="width: 30px; height: 30px;">
                                        <div>
                                            <div class="fw-bold">{{ $activity->user->name }}</div>
                                            <small class="text-muted">{{ ucfirst($activity->user->role) }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                <span class="d-flex align-items-center">
                                    <i class="{{ $activity->activity_icon }} me-2"></i>
                                    {{ $activity->friendly_description }}
                                </span>
                            </td>
                            <td>
                                <span title="{{ $activity->description }}">
                                    {{ Str::limit($activity->description, 80) }}
                                </span>
                                @if($activity->browser_info && $activity->browser_info != 'ไม่ทราบ')
                                    <br><small class="text-muted">{{ $activity->browser_info }}</small>
                                @endif
                            </td>
                            <td>
                                <code>{{ $activity->ip_address }}</code>
                                @if($activity->location)
                                    <br><small class="text-muted">{{ $activity->location }}</small>
                                @endif
                            </td>
                            <td>
                                @if($activity->is_suspicious)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle me-1"></i>น่าสงสัย
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>ปกติ
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('activities.show', $activity->id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($canViewAll)
                                        @if($activity->is_suspicious)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    onclick="unmarkSuspicious({{ $activity->id }})"
                                                    title="ยกเลิกการทำเครื่องหมาย">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning" 
                                                    onclick="markSuspicious({{ $activity->id }})"
                                                    title="ทำเครื่องหมายว่าน่าสงสัย">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination และข้อมูลสถิติ -->
            <div class="pagination-container mt-4">
                <div class="pagination-info">
                    <span class="text-muted">
                        <i class="bi bi-info-circle me-2"></i>
                        แสดง {{ $activities->firstItem() ?? 0 }} ถึง {{ $activities->lastItem() ?? 0 }} 
                        จากทั้งหมด {{ number_format($activities->total()) }} รายการ
                    </span>
                </div>
                
                @if($activities->hasPages())
                    <div class="pagination-nav">
                        <nav aria-label="กิจกรรม pagination">
                            <ul class="pagination pagination-sm mb-0">
                                {{-- Previous Page Link --}}
                                @if ($activities->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="bi bi-chevron-left"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $activities->previousPageUrl() }}" rel="prev">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @php
                                    $currentPage = $activities->currentPage();
                                    $lastPage = $activities->lastPage();
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($lastPage, $currentPage + 2);
                                @endphp

                                {{-- First page --}}
                                @if($startPage > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $activities->url(1) }}">1</a>
                                    </li>
                                    @if($startPage > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif

                                {{-- Page numbers --}}
                                @for($page = $startPage; $page <= $endPage; $page++)
                                    @if ($page == $currentPage)
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $activities->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endfor

                                {{-- Last page --}}
                                @if($endPage < $lastPage)
                                    @if($endPage < $lastPage - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $activities->url($lastPage) }}">{{ $lastPage }}</a>
                                    </li>
                                @endif

                                {{-- Next Page Link --}}
                                @if ($activities->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $activities->nextPageUrl() }}" rel="next">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="bi bi-chevron-right"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <i class="bi bi-inbox display-1 text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">ไม่พบประวัติกิจกรรม</h5>
                    <p class="text-muted mb-4">ลองปรับเปลี่ยนเงื่อนไขการค้นหา หรือสร้างกิจกรรมใหม่</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('activities.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i>รีเฟรชหน้า
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('filterForm').reset();">
                            <i class="bi bi-funnel me-1"></i>ล้างตัวกรอง
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if($canViewAll)
<!-- Modal สำหรับแสดงกราฟ -->
<div class="modal fade" id="chartModal" tabindex="-1" aria-labelledby="chartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chartModalLabel">
                    <i class="bi bi-graph-up me-2"></i>สถิติกิจกรรม
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="chartDays" class="form-label">ช่วงเวลา</label>
                        <select id="chartDays" class="form-select" onchange="loadChartData()">
                            <option value="7">7 วันที่ผ่านมา</option>
                            <option value="30">30 วันที่ผ่านมา</option>
                            <option value="90">90 วันที่ผ่านมา</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <h6>กิจกรรมรายวัน</h6>
                        <canvas id="dailyChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-4">
                        <h6>ประเภทกิจกรรม</h6>
                        <canvas id="typeChart" width="200" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let dailyChart, typeChart;

function exportActivities() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = "{{ route('activities.export') }}?" + params.toString();
}

@if($canViewAll)
function markSuspicious(activityId) {
    Swal.fire({
        title: 'ทำเครื่องหมายว่าน่าสงสัย',
        input: 'textarea',
        inputLabel: 'กรุณาระบุเหตุผล',
        inputPlaceholder: 'ระบุเหตุผลที่ทำเครื่องหมายว่าน่าสงสัย...',
        inputAttributes: {
            'aria-label': 'ระบุเหตุผลที่ทำเครื่องหมายว่าน่าสงสัย'
        },
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-exclamation-triangle"></i> ทำเครื่องหมาย',
        cancelButtonText: '<i class="bi bi-x-circle"></i> ยกเลิก',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return 'กรุณาระบุเหตุผล'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดง loading
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                html: 'กรุณารอสักครู่',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });

            fetch(`/activities/${activityId}/mark-suspicious`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'ทำเครื่องหมายกิจกรรมว่าน่าสงสัยเรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถดำเนินการได้',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            });
        }
    });
}

function unmarkSuspicious(activityId) {
    Swal.fire({
        title: 'ยกเลิกการทำเครื่องหมาย',
        text: 'คุณต้องการยกเลิกการทำเครื่องหมายว่าน่าสงสัยใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-circle"></i> ยกเลิกเครื่องหมาย',
        cancelButtonText: '<i class="bi bi-x-circle"></i> ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดง loading
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                html: 'กรุณารอสักครู่',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });

            fetch(`/activities/${activityId}/unmark-suspicious`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'ยกเลิกการทำเครื่องหมายเรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถดำเนินการได้',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            });
        }
    });
}

function loadChartData() {
    const days = document.getElementById('chartDays').value;
    
    fetch(`/activities/chart-data?days=${days}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDailyChart(data.daily_data);
            updateTypeChart(data.type_data);
        }
    })
    .catch(error => {
        console.error('Error loading chart data:', error);
    });
}

function updateDailyChart(data) {
    const ctx = document.getElementById('dailyChart').getContext('2d');
    
    if (dailyChart) {
        dailyChart.destroy();
    }
    
    dailyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.formatted_date),
            datasets: [{
                label: 'จำนวนกิจกรรม',
                data: data.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateTypeChart(data) {
    const ctx = document.getElementById('typeChart').getContext('2d');
    
    if (typeChart) {
        typeChart.destroy();
    }
    
    typeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.activity_type),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF',
                    '#4BC0C0',
                    '#FF6384'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// โหลดข้อมูลกราฟเมื่อเปิด modal
document.getElementById('chartModal').addEventListener('shown.bs.modal', function () {
    loadChartData();
});
@endif

// Auto refresh ทุก 5 นาที
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 300000);

// เพิ่ม loading state เมื่อคลิก pagination
document.querySelectorAll('.pagination .page-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!this.closest('.page-item').classList.contains('active') && 
            !this.closest('.page-item').classList.contains('disabled')) {
            
            // แสดง loading
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            this.style.pointerEvents = 'none';
            
            // Reset หลังจาก 3 วินาที ในกรณีที่หน้าไม่ reload
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 3000);
        }
    });
});

// เพิ่ม loading state เมื่อคลิกหัวตารางเพื่อเรียงลำดับ
document.querySelectorAll('.table thead th a').forEach(link => {
    link.addEventListener('click', function(e) {
        // แสดง loading overlay
        const overlay = document.createElement('div');
        overlay.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <div class="mt-2">กำลังเรียงลำดับข้อมูล...</div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay.firstElementChild);
        
        // Remove overlay หลังจาก 5 วินาที ในกรณีที่หน้าไม่ reload
        setTimeout(() => {
            const loadingOverlay = document.querySelector('[style*="position: fixed"]');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }, 5000);
    });
});

// Smooth scroll เมื่อเปลี่ยนหน้า
if (window.location.hash) {
    document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
}
</script>
@endpush

@push('styles')
<style>
/* SweetAlert2 Custom Styles */
.swal2-popup {
    border-radius: 10px !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.swal2-title {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
}

.swal2-html-container {
    font-size: 0.95rem !important;
    line-height: 1.6 !important;
}

.swal2-input, .swal2-textarea {
    border-radius: 6px !important;
    border: 1px solid #ced4da !important;
}

.swal2-confirm, .swal2-cancel {
    border-radius: 6px !important;
    font-weight: 500 !important;
    padding: 8px 20px !important;
}

.swal2-icon {
    border: none !important;
}

.swal2-icon.swal2-warning {
    border-color: #f39c12 !important;
    color: #f39c12 !important;
}

.swal2-icon.swal2-success {
    border-color: #28a745 !important;
    color: #28a745 !important;
}

.swal2-icon.swal2-error {
    border-color: #dc3545 !important;
    color: #dc3545 !important;
}

.swal2-icon.swal2-question {
    border-color: #17a2b8 !important;
    color: #17a2b8 !important;
}

/* Table styling improvements */
.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.badge {
    font-size: 0.75rem;
}

/* Sortable table headers */
.table thead th a {
    color: #495057 !important;
    font-weight: 600;
    transition: color 0.2s ease;
}

.table thead th a:hover {
    color: #4e73df !important;
    text-decoration: none !important;
}

.table thead th a .bi {
    font-size: 0.8rem;
    opacity: 0.7;
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    vertical-align: middle;
    position: relative;
}

.table thead th:hover {
    background-color: #e9ecef;
}

/* Active sort indicator */
.table thead th a:has(.bi-chevron-down),
.table thead th a:has(.bi-chevron-up) {
    color: #4e73df !important;
}

.table thead th a .bi-chevron-down,
.table thead th a .bi-chevron-up {
    opacity: 1;
    color: #4e73df !important;
}

/* Card shadow improvements */
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

/* Pagination Styling */
.pagination {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.pagination .page-item .page-link {
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 10px 15px;
    margin: 0 2px;
    border-radius: 6px;
    transition: all 0.2s ease-in-out;
    font-weight: 500;
    min-width: 45px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.pagination .page-item .page-link:hover {
    background-color: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-decoration: none;
}

.pagination .page-item.active .page-link {
    background-color: #4e73df;
    border-color: #4e73df;
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3);
}

.pagination .page-item.disabled .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
    border-color: #dee2e6;
    cursor: not-allowed;
}

.pagination .page-item:first-child .page-link {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.pagination .page-item:last-child .page-link {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* Page info styling */
.text-muted .bi-info-circle {
    color: #4e73df !important;
}

/* Responsive pagination */
@media (max-width: 576px) {
    .pagination .page-item .page-link {
        padding: 8px 10px;
        font-size: 0.875rem;
        min-width: 38px;
        height: 36px;
        margin: 0 1px;
    }
    
    .pagination {
        justify-content: center;
    }
    
    /* ซ่อนหมายเลขหน้าบางส่วนในมือถือ */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
}

@media (max-width: 768px) {
    .pagination .page-item .page-link {
        min-width: 40px;
        height: 38px;
        padding: 9px 12px;
    }
}

/* เพิ่ม spacing และ alignment */
.pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.pagination-info {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.pagination-nav {
    display: flex;
    justify-content: center;
    flex: 1;
    min-width: 250px;
}
</style>
@endpush
