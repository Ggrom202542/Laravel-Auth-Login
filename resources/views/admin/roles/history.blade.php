@extends('layouts.dashboard')

@section('title', 'ประวัติการเปลี่ยนบทบาท')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-clock-history me-2"></i>ประวัติการเปลี่ยนบทบาท
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.roles.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left me-1"></i>กลับไปจัดการบทบาท
            </a>
            <a href="{{ route('super-admin.roles.permissions') }}" class="btn btn-info">
                <i class="bi bi-list-check me-1"></i>สิทธิ์การใช้งาน
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-funnel me-2"></i>กรองข้อมูล
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('super-admin.roles.history') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="user_id" class="form-label">ผู้ใช้</label>
                    <select class="form-control" id="user_id" name="user_id">
                        <option value="">ทั้งหมด</option>
                        @php
                            $users = \App\Models\User::orderBy('first_name')->get();
                        @endphp
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">บทบาท</label>
                    <select class="form-control" id="role" name="role">
                        <option value="">ทั้งหมด</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                    <a href="{{ route('super-admin.roles.history') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Role Changes History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-journal-text me-2"></i>รายการการเปลี่ยนแปลง
            </h6>
        </div>
        <div class="card-body">
            @if($roleChanges->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ผู้ใช้</th>
                                <th>การเปลี่ยนแปลง</th>
                                <th>เหตุผล</th>
                                <th>เปลี่ยนโดย</th>
                                <th>วันที่และเวลา</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roleChanges as $change)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="rounded-circle me-2" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($change->user_first_name.' '.$change->user_last_name) }}&color=7F9CF5&background=EBF4FF" 
                                                 alt="{{ $change->user_first_name }} {{ $change->user_last_name }}" 
                                                 style="width: 32px; height: 32px;">
                                            <div>
                                                <div class="font-weight-bold">
                                                    {{ $change->user_first_name }} {{ $change->user_last_name }}
                                                </div>
                                                <div class="small text-muted">{{ $change->user_email }}</div>
                                            </div>
                                        </div>
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
                                            $oldRoleName = match($change->old_role) {
                                                'super_admin' => 'Super Admin',
                                                'admin' => 'Admin',
                                                'user' => 'User',
                                                default => $change->old_role
                                            };
                                            $newRoleName = match($change->new_role) {
                                                'super_admin' => 'Super Admin',
                                                'admin' => 'Admin',
                                                'user' => 'User',
                                                default => $change->new_role
                                            };
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-{{ $oldRoleColor }}">{{ $oldRoleName }}</span>
                                            <i class="bi bi-arrow-right mx-2 text-muted"></i>
                                            <span class="badge bg-{{ $newRoleColor }}">{{ $newRoleName }}</span>
                                        </div>
                                        @if($change->old_role === 'user' && $change->new_role === 'super_admin')
                                            <div class="small text-danger mt-1">
                                                <i class="bi bi-exclamation-triangle me-1"></i>การเปลี่ยนแปลงความเสี่ยงสูง
                                            </div>
                                        @elseif($change->new_role === 'super_admin')
                                            <div class="small text-warning mt-1">
                                                <i class="bi bi-shield-exclamation me-1"></i>เพิ่มสิทธิ์ระดับสูง
                                            </div>
                                        @elseif($change->old_role === 'super_admin')
                                            <div class="small text-info mt-1">
                                                <i class="bi bi-shield-minus me-1"></i>ลดสิทธิ์ระดับสูง
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-wrap" style="max-width: 250px;">
                                            {{ $change->reason }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="rounded-circle me-2" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($change->changer_first_name.' '.$change->changer_last_name) }}&color=7F9CF5&background=EBF4FF" 
                                                 alt="{{ $change->changer_first_name }} {{ $change->changer_last_name }}" 
                                                 style="width: 28px; height: 28px;">
                                            <div>
                                                <div class="small font-weight-bold">
                                                    {{ $change->changer_first_name }} {{ $change->changer_last_name }}
                                                </div>
                                                <div class="small text-muted">Super Admin</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $changeDate = \Carbon\Carbon::parse($change->created_at);
                                        @endphp
                                        <div class="small">
                                            <div class="font-weight-bold">{{ $changeDate->format('d/m/Y') }}</div>
                                            <div class="text-muted">{{ $changeDate->format('H:i:s') }}</div>
                                            <div class="text-muted">{{ $changeDate->diffForHumans() }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $roleChanges->appends(request()->query())->links() }}
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clock-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่พบประวัติการเปลี่ยนบทบาท</h5>
                    @if(request()->hasAny(['user_id', 'role']))
                        <p class="text-muted">ลองเปลี่ยนเงื่อนไขการค้นหา</p>
                        <a href="{{ route('super-admin.roles.history') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i>ดูทั้งหมด
                        </a>
                    @else
                        <p class="text-muted">ยังไม่มีการเปลี่ยนแปลงบทบาทในระบบ</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Statistics -->
    @if($roleChanges->count() > 0)
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-bar-chart me-2"></i>สถิติการเปลี่ยนแปลง
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $totalChanges = $roleChanges->total();
                            $upgradeCount = $roleChanges->where('new_role', 'super_admin')->count() + 
                                           $roleChanges->where('old_role', 'user')->where('new_role', 'admin')->count();
                            $downgradeCount = $roleChanges->where('old_role', 'super_admin')->count() + 
                                             $roleChanges->where('old_role', 'admin')->where('new_role', 'user')->count();
                        @endphp
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="border-end">
                                    <div class="h4 font-weight-bold text-primary">{{ $totalChanges }}</div>
                                    <div class="small text-muted">รวมการเปลี่ยนแปลง</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border-end">
                                    <div class="h4 font-weight-bold text-success">{{ $upgradeCount }}</div>
                                    <div class="small text-muted">เพิ่มสิทธิ์</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="h4 font-weight-bold text-warning">{{ $downgradeCount }}</div>
                                <div class="small text-muted">ลดสิทธิ์</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-exclamation-triangle me-2"></i>การเปลี่ยนแปลงที่ต้องติดตาม
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $highRiskChanges = $roleChanges->filter(function($change) {
                                return ($change->old_role === 'user' && $change->new_role === 'super_admin') ||
                                       ($change->new_role === 'super_admin');
                            });
                        @endphp
                        @if($highRiskChanges->count() > 0)
                            <div class="small">
                                <div class="text-danger mb-2">
                                    <i class="bi bi-shield-exclamation me-1"></i>
                                    <strong>การเปลี่ยนแปลงความเสี่ยงสูง: {{ $highRiskChanges->count() }} รายการ</strong>
                                </div>
                                <ul class="list-unstyled mb-0">
                                    @foreach($highRiskChanges->take(3) as $change)
                                        <li class="mb-1">
                                            <i class="bi bi-dot me-1"></i>
                                            {{ $change->user_first_name }} {{ $change->user_last_name }} 
                                            เป็น Super Admin
                                            <small class="text-muted">({{ \Carbon\Carbon::parse($change->created_at)->diffForHumans() }})</small>
                                        </li>
                                    @endforeach
                                    @if($highRiskChanges->count() > 3)
                                        <li><small class="text-muted">และอีก {{ $highRiskChanges->count() - 3 }} รายการ</small></li>
                                    @endif
                                </ul>
                            </div>
                        @else
                            <div class="text-center text-success">
                                <i class="bi bi-shield-check fa-2x mb-2"></i>
                                <div class="small">ไม่มีการเปลี่ยนแปลงที่เสี่ยง</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection