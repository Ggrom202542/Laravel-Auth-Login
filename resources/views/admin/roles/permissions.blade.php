@extends('layouts.dashboard')

@section('title', 'สิทธิ์การใช้งาน')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-list-check me-2"></i>สิทธิ์การใช้งาน
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.roles.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left me-1"></i>กลับไปจัดการบทบาท
            </a>
            <a href="{{ route('super-admin.roles.history') }}" class="btn btn-secondary">
                <i class="bi bi-clock-history me-1"></i>ประวัติการเปลี่ยนแปลง
            </a>
        </div>
    </div>

    <!-- Permission Summary Cards -->
    <div class="row mb-4">
        @foreach($permissionSummary as $role => $summary)
            @php
                $cardColor = match($role) {
                    'super_admin' => 'danger',
                    'admin' => 'primary',
                    'user' => 'success',
                    default => 'secondary'
                };
                $riskColor = match($summary['risk_level']) {
                    'High' => 'danger',
                    'Medium' => 'warning',
                    'Low' => 'success',
                    default => 'secondary'
                };
            @endphp
            <div class="col-lg-4 mb-4">
                <div class="card border-left-{{ $cardColor }} shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-{{ $cardColor }} text-uppercase mb-1">
                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 me-3 font-weight-bold text-gray-800">
                                            {{ $summary['total_users'] }} คน
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm me-2">
                                            <div class="progress-bar bg-{{ $cardColor }}" role="progressbar"
                                                 style="width: {{ ($summary['total_users'] / max(1, array_sum(array_column($permissionSummary, 'total_users')))) * 100 }}%"
                                                 aria-valuenow="{{ $summary['total_users'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="{{ array_sum(array_column($permissionSummary, 'total_users')) }}"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-2">
                                    <strong>{{ $summary['capabilities'] }} สิทธิ์</strong> | 
                                    ความเสี่ยง: <span class="text-{{ $riskColor }}">{{ $summary['risk_level'] }}</span>
                                </div>
                                <div class="small text-muted">{{ $summary['description'] }}</div>
                            </div>
                            <div class="col-auto">
                                @if($role === 'super_admin')
                                    <i class="bi bi-shield-fill-exclamation fa-2x text-{{ $cardColor }}"></i>
                                @elseif($role === 'admin')
                                    <i class="bi bi-person-badge fa-2x text-{{ $cardColor }}"></i>
                                @else
                                    <i class="bi bi-people fa-2x text-{{ $cardColor }}"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Detailed Permissions by Role -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-shield-lock me-2"></i>สิทธิ์การใช้งานแบบละเอียด
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ฟีเจอร์ / ความสามารถ</th>
                                    <th class="text-center">
                                        <span class="badge bg-success">User</span>
                                    </th>
                                    <th class="text-center">
                                        <span class="badge bg-primary">Admin</span>
                                    </th>
                                    <th class="text-center">
                                        <span class="badge bg-danger">Super Admin</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- User Management -->
                                <tr class="table-info">
                                    <td colspan="4"><strong><i class="bi bi-people me-2"></i>การจัดการผู้ใช้</strong></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">จัดการโปรไฟล์ตนเอง</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ดูข้อมูลผู้ใช้อื่น</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">แก้ไขข้อมูลผู้ใช้อื่น</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-warning"></i> จำกัด</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ลบผู้ใช้</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>

                                <!-- Registration Approval -->
                                <tr class="table-warning">
                                    <td colspan="4"><strong><i class="bi bi-person-check me-2"></i>การอนุมัติสมาชิก</strong></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ดูรายการคำขอสมัคร</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">อนุมัติ/ปฏิเสธคำขอ</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Override การตัดสินใจ</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>

                                <!-- Role Management -->
                                <tr class="table-danger">
                                    <td colspan="4"><strong><i class="bi bi-shield-check me-2"></i>การจัดการบทบาท</strong></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ดูบทบาทตนเอง</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">เปลี่ยนบทบาทผู้อื่น</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ดูประวัติการเปลี่ยนบทบาท</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>

                                <!-- Security -->
                                <tr class="table-secondary">
                                    <td colspan="4"><strong><i class="bi bi-shield-lock me-2"></i>ความปลอดภัย</strong></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">จัดการอุปกรณ์ตนเอง</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ดู Security Dashboard</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-warning"></i> จำกัด</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">จัดการ IP Address</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Security Policies</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>

                                <!-- Audit & Monitoring -->
                                <tr class="table-primary">
                                    <td colspan="4"><strong><i class="bi bi-clipboard-check me-2"></i>Audit & Monitoring</strong></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ดู Audit Logs ตนเอง</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-warning"></i> จำกัด</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">ดู Audit Logs ทั้งหมด</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-warning"></i> จำกัด</td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">สถิติและรายงานขั้นสูง</td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Role Changes -->
    @if($recentChanges->isNotEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-clock-history me-2"></i>การเปลี่ยนแปลงบทบาทล่าสุด
                        </h6>
                        <a href="{{ route('super-admin.roles.history') }}" class="btn btn-sm btn-outline-primary">
                            ดูทั้งหมด
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>ผู้ใช้</th>
                                        <th>การเปลี่ยนแปลง</th>
                                        <th>เหตุผล</th>
                                        <th>เปลี่ยนโดย</th>
                                        <th>เวลา</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentChanges as $change)
                                        <tr>
                                            <td>
                                                {{ $change->user_first_name }} {{ $change->user_last_name }}
                                            </td>
                                            <td>
                                                @php
                                                    $oldRoleColor = match($change->old_role) {
                                                        'super_admin' => 'danger',
                                                        'admin' => 'primary',
                                                        'user' => 'success',
                                                        default => 'secondary'
                                                    };
                                                    $newRoleColor = match($change->new_role) {
                                                        'super_admin' => 'danger',
                                                        'admin' => 'primary',
                                                        'user' => 'success',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $oldRoleColor }}">{{ ucfirst($change->old_role) }}</span>
                                                <i class="bi bi-arrow-right mx-1"></i>
                                                <span class="badge bg-{{ $newRoleColor }}">{{ ucfirst($change->new_role) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($change->reason, 50) }}</span>
                                            </td>
                                            <td>
                                                {{ $change->changer_first_name }} {{ $change->changer_last_name }}
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($change->created_at)->diffForHumans() }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection