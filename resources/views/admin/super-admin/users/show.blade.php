@extends('layouts.dashboard')

@section('title', 'Super Admin - ข้อมูลผู้ใช้')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-person-circle"></i> ข้อมูลผู้ใช้: {{ $user->name }}
        </h1>
        <div>
            <a href="{{ route('super-admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm mr-2">
                <i class="bi bi-pencil-square"></i> แก้ไข
            </a>
            <a href="{{ route('super-admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person-vcard"></i> ข้อมูลส่วนตัว
                    </h6>
                </div>
                <div class="card-body text-center">
                    <!-- Profile Image -->
                    @if($user->profile_image)
                        <img class="rounded-circle mb-3" src="{{ asset('storage/profiles/'.$user->profile_image) }}" 
                             style="width: 120px; height: 120px;" alt="{{ $user->name }}">
                    @else
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mx-auto mb-3"
                             style="width: 120px; height: 120px; font-size: 36px;">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                    @endif

                    <h5 class="card-title">{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->username }}</p>

                    <!-- Role Badge -->
                    @if($user->role === 'super_admin')
                        <span class="badge badge-danger badge-pill text-dark">
                            <i class="bi bi-shield-fill-exclamation"></i> Super Admin
                        </span>
                    @elseif($user->role === 'admin')
                        <span class="badge badge-warning badge-pill text-dark">
                            <i class="bi bi-shield-fill-check"></i> Admin
                        </span>
                    @else
                        <span class="badge badge-secondary badge-pill text-dark">
                            <i class="bi bi-person"></i> ผู้ใช้
                        </span>
                    @endif

                    <!-- Status Badge -->
                    @switch($user->status)
                        @case('active')
                            <span class="badge badge-success badge-pill ml-2">ใช้งานได้</span>
                            @break
                        @case('inactive')
                            <span class="badge badge-secondary badge-pill ml-2">ไม่ใช้งาน</span>
                            @break
                        @case('suspended')
                            <span class="badge badge-danger badge-pill ml-2">ระงับการใช้งาน</span>
                            @break
                        @case('pending')
                            <span class="badge badge-info badge-pill ml-2">รออนุมัติ</span>
                            @break
                    @endswitch

                    <!-- Action Buttons -->
                    <div class="mt-3">
                        <div class="btn-group" role="group" style="gap: 8px;">
                            <button type="button" class="btn btn-warning btn-sm" 
                                    onclick="showResetPasswordModal({{ $user->id }}, '{{ $user->name }}')">
                                <i class="bi bi-key"></i> รีเซ็ตรหัสผ่าน
                            </button>
                            <button type="button" class="btn btn-info btn-sm" 
                                    onclick="showStatusToggleModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->status }}')">
                                <i class="bi bi-toggle-on"></i> เปลี่ยนสถานะ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-bar-chart"></i> สถิติด่วน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-primary">{{ $stats['total_sessions'] }}</h4>
                                <small class="text-muted">เซสชันทั้งหมด</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $stats['active_sessions'] }}</h4>
                            <small class="text-muted">เซสชันที่ใช้งาน</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-warning">{{ $stats['last_7_days_sessions'] }}</h4>
                                <small class="text-muted">7 วันล่าสุด</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-secondary">{{ $stats['unique_ip_addresses'] }}</h4>
                            <small class="text-muted">IP ที่แตกต่าง</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle"></i> ข้อมูลพื้นฐาน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>ID:</strong></label>
                            <p class="form-control-plaintext">{{ $user->id }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>ชื่อผู้ใช้:</strong></label>
                            <p class="form-control-plaintext">{{ $user->username }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>อีเมล:</strong></label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-envelope"></i> {{ $user->email }}
                                @if($user->email_verified_at)
                                    <span class="badge badge-success badge-sm ml-2">ยืนยันแล้ว</span>
                                @else
                                    <span class="badge badge-warning badge-sm ml-2">ยังไม่ยืนยัน</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>เบอร์โทรศัพท์:</strong></label>
                            <p class="form-control-plaintext">
                                @if($user->phone)
                                    <i class="bi bi-telephone"></i> {{ $user->phone }}
                                @else
                                    <span class="text-muted">ไม่ได้ระบุ</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($user->address)
                    <div class="mb-3">
                        <label class="form-label"><strong>ที่อยู่:</strong></label>
                        <p class="form-control-plaintext">
                            <i class="bi bi-geo-alt"></i> {{ $user->address }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="bi bi-shield-lock"></i> การตั้งค่าความปลอดภัย
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Two-Factor Authentication:</strong></label>
                            <p class="form-control-plaintext">
                                @if($user->two_fa_enabled)
                                    <span class="badge badge-success">
                                        <i class="bi bi-lock-fill"></i> เปิดใช้งาน
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="bi bi-unlock"></i> ปิดใช้งาน
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Session Timeout:</strong></label>
                            <p class="form-control-plaintext">
                                @if($user->session_timeout)
                                    {{ $user->session_timeout }} นาที
                                @else
                                    <span class="text-muted">ค่าเริ่มต้น (60 นาที)</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($user->ip_restrictions)
                    <div class="mb-3">
                        <label class="form-label"><strong>IP Restrictions:</strong></label>
                        <div class="border rounded p-2 bg-light">
                            <code>{{ str_replace("\n", '<br>', $user->ip_restrictions) }}</code>
                        </div>
                    </div>
                    @endif

                    @if($user->allowed_login_methods)
                    <div class="mb-3">
                        <label class="form-label"><strong>วิธีการเข้าสู่ระบบที่อนุญาต:</strong></label>
                        <p class="form-control-plaintext">
                            @php
                                $methods = json_decode($user->allowed_login_methods, true);
                                $methodLabels = [
                                    'password' => 'รหัสผ่าน',
                                    'two_factor' => '2FA',
                                    'social' => 'Social Login'
                                ];
                            @endphp
                            @foreach($methods as $method)
                                <span class="badge badge-info mr-1">{{ $methodLabels[$method] ?? $method }}</span>
                            @endforeach
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- System Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="bi bi-database"></i> ข้อมูลระบบ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>สมาชิกเมื่อ:</strong></label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-calendar-plus"></i> 
                                {{ $user->created_at->format('d/m/Y H:i:s') }}
                                <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>อัปเดตล่าสุด:</strong></label>
                            <p class="form-control-plaintext">
                                <i class="bi bi-pencil-square"></i> 
                                {{ $user->updated_at->format('d/m/Y H:i:s') }}
                                <small class="text-muted">({{ $user->updated_at->diffForHumans() }})</small>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>เข้าสู่ระบบล่าสุด:</strong></label>
                            <p class="form-control-plaintext">
                                @if($user->last_login_at)
                                    <i class="bi bi-box-arrow-in-right"></i> 
                                    {{ $user->last_login_at->format('d/m/Y H:i:s') }}
                                    <small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                                @else
                                    <span class="text-muted">ยังไม่เคยเข้าสู่ระบบ</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>IP ล่าสุด:</strong></label>
                            <p class="form-control-plaintext">
                                @if($user->last_login_ip)
                                    <code>{{ $user->last_login_ip }}</code>
                                @else
                                    <span class="text-muted">ไม่มีข้อมูล</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($user->created_by_admin)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>สร้างโดย:</strong></label>
                            <p class="form-control-plaintext">
                                @php
                                    $creator = \App\Models\User::find($user->created_by_admin);
                                @endphp
                                @if($creator)
                                    <i class="bi bi-shield-check"></i> {{ $creator->name }}
                                @else
                                    <span class="text-muted">ไม่มีข้อมูล</span>
                                @endif
                            </p>
                        </div>
                        @if($user->updated_by_admin)
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>อัปเดตล่าสุดโดย:</strong></label>
                            <p class="form-control-plaintext">
                                @php
                                    $updater = \App\Models\User::find($user->updated_by_admin);
                                @endphp
                                @if($updater)
                                    <i class="bi bi-shield-check"></i> {{ $updater->name }}
                                @else
                                    <span class="text-muted">ไม่มีข้อมูล</span>
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($user->admin_notes)
                    <div class="mb-3">
                        <label class="form-label"><strong>หมายเหตุผู้ดูแลระบบ:</strong></label>
                        <div class="alert alert-info">
                            <i class="bi bi-sticky"></i> {{ $user->admin_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Sessions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history"></i> เซสชันล่าสุด
                    </h6>
                    @if($stats['active_sessions'] > 0)
                    <button class="btn btn-sm btn-danger" 
                            onclick="terminateUserSessions({{ $user->id }}, '{{ $user->name }}')">
                        <i class="bi bi-box-arrow-right"></i> ยกเลิกเซสชันทั้งหมด
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($recentSessions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>เวลา</th>
                                    <th>IP Address</th>
                                    <th>Browser/OS</th>
                                    <th>สถานะ</th>
                                    <th>ระยะเวลา</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSessions as $session)
                                <tr class="{{ $session->status === 'active' ? 'table-success' : '' }}">
                                    <td>
                                        {{ $session->created_at->format('d/m/Y H:i:s') }}
                                        <br><small class="text-muted">{{ $session->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $session->ip_address }}</code>
                                        @if($session->location)
                                            <br><small class="text-muted">{{ $session->location }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <i class="bi bi-browser-chrome"></i> {{ $session->browser }}
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-display"></i> {{ $session->operating_system }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($session->status === 'active')
                                            <span class="badge badge-success">ใช้งานอยู่</span>
                                        @else
                                            <span class="badge badge-secondary">ปิดแล้ว</span>
                                        @endif
                                    </td>
                                    <td>{{ $session->duration }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history" style="font-size: 3rem; color: #d1d3e2;"></i>
                        <p class="text-gray-500 mt-3">ไม่มีประวัติการเข้าสู่ระบบ</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.super-admin.users.modals.password-reset-modal')
@include('admin.super-admin.users.modals.status-toggle-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/admin/super-admin-users.js') }}"></script>
@endpush
