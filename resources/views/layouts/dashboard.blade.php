<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/components.css') }}">
    
    <!-- Custom SCSS/JS -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body id="page-top">

    <div id="wrapper" class="d-flex">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div class="sidebar-brand-text mx-3">{{ config('app.name', 'Laravel') }}</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ request()->is('*/dashboard') ? 'active' : '' }}">
                @if(auth()->user()->role == 'user')
                    <a class="nav-link" href="{{ route('user.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                @elseif(auth()->user()->role == 'admin')
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                @elseif(auth()->user()->role == 'super_admin')
                    <a class="nav-link" href="{{ route('super-admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                @endif
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                การจัดการ
            </div>

            <!-- Profile Section for All Users -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProfile" 
                   aria-expanded="false" aria-controls="collapseProfile">
                    <i class="bi bi-person-circle"></i>
                    <span>โปรไฟล์</span>
                </a>
                <div id="collapseProfile" class="collapse" data-bs-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">การจัดการโปรไฟล์:</h6>
                        <a class="collapse-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-eye me-2"></i>ดูโปรไฟล์
                        </a>
                        <a class="collapse-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-pencil-square me-2"></i>แก้ไขโปรไฟล์
                        </a>
                        <a class="collapse-item" href="{{ route('profile.settings') }}">
                            <i class="bi bi-gear me-2"></i>การตั้งค่า
                        </a>
                    </div>
                </div>
            </li>

            @if(auth()->user()->role == 'user')
                <!-- User Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSecurity" 
                       aria-expanded="false" aria-controls="collapseSecurity">
                        <i class="bi bi-shield-lock"></i>
                        <span>ความปลอดภัย</span>
                    </a>
                    <div id="collapseSecurity" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การจัดการความปลอดภัย:</h6>
                            <a class="collapse-item" href="{{ route('user.security.index') }}">
                                <i class="bi bi-shield-check me-2"></i>แดชบอร์ดความปลอดภัย
                            </a>
                            <a class="collapse-item" href="{{ route('user.security.devices') }}">
                                <i class="bi bi-phone me-2"></i>จัดการอุปกรณ์
                            </a>
                            <a class="collapse-item" href="{{ route('user.security.login-history') }}">
                                <i class="bi bi-clock-history me-2"></i>ประวัติการเข้าสู่ระบบ
                            </a>
                            <a class="collapse-item" href="{{ route('user.security.alerts') }}">
                                <i class="bi bi-exclamation-triangle me-2"></i>การแจ้งเตือนความปลอดภัย
                            </a>
                        </div>
                    </div>
                </li>

                <!-- User Session Management -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.sessions.index') }}">
                        <i class="bi bi-laptop"></i>
                        <span>จัดการ Sessions</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-clock-history"></i>
                        <span>ประวัติกิจกรรม</span>
                    </a>
                </li>

            @elseif(auth()->user()->role == 'admin')
                <!-- Admin Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseApprovals" 
                       aria-expanded="false" aria-controls="collapseApprovals">
                        <i class="bi bi-person-check"></i>
                        <span>จัดการอนุมัติสมาชิก</span>
                    </a>
                    <div id="collapseApprovals" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การอนุมัติสมาชิก:</h6>
                            <a class="collapse-item" href="{{ route('admin.approvals.index') }}">
                                <i class="bi bi-list-ul me-2"></i>รายการทั้งหมด
                            </a>
                            <a class="collapse-item" href="{{ route('admin.approvals.index', ['status' => 'pending']) }}">
                                <i class="bi bi-hourglass-split me-2"></i>รอการอนุมัติ
                            </a>
                            <a class="collapse-item" href="{{ route('admin.approvals.index', ['status' => 'approved']) }}">
                                <i class="bi bi-check-circle me-2"></i>อนุมัติแล้ว
                            </a>
                            <a class="collapse-item" href="{{ route('admin.approvals.index', ['status' => 'rejected']) }}">
                                <i class="bi bi-x-circle me-2"></i>ปฏิเสธแล้ว
                            </a>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUsers" 
                       aria-expanded="false" aria-controls="collapseUsers">
                        <i class="bi bi-people"></i>
                        <span>จัดการผู้ใช้</span>
                    </a>
                    <div id="collapseUsers" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การจัดการผู้ใช้:</h6>
                            <a class="collapse-item" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-list-ul me-2"></i>รายการผู้ใช้
                            </a>
                            <a class="collapse-item" href="{{ route('admin.users.index', ['status' => 'active']) }}">
                                <i class="bi bi-person-check me-2"></i>ผู้ใช้ที่ใช้งานได้
                            </a>
                            <a class="collapse-item" href="{{ route('admin.users.index', ['status' => 'inactive']) }}">
                                <i class="bi bi-person-x me-2"></i>ผู้ใช้ไม่ใช้งาน
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Security Management for Admin -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAdminSecurity" 
                       aria-expanded="false" aria-controls="collapseAdminSecurity">
                        <i class="bi bi-shield-lock"></i>
                        <span>จัดการความปลอดภัย</span>
                    </a>
                    <div id="collapseAdminSecurity" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">ความปลอดภัยระบบ:</h6>
                            <a class="collapse-item" href="{{ route('admin.security.index') }}">
                                <i class="bi bi-shield-check me-2"></i>แดชบอร์ดความปลอดภัย
                            </a>
                            <a class="collapse-item" href="{{ route('admin.security.ip.index') }}">
                                <i class="bi bi-globe me-2"></i>จัดการ IP Address
                            </a>
                            <a class="collapse-item" href="{{ route('admin.security.index') }}#devices">
                                <i class="bi bi-phone me-2"></i>อุปกรณ์ผู้ใช้
                            </a>
                            <a class="collapse-item" href="{{ route('admin.security.index') }}#suspicious">
                                <i class="bi bi-exclamation-triangle me-2"></i>กิจกรรมน่าสงสัย
                            </a>
                            <a class="collapse-item" href="{{ route('admin.security.report') }}">
                                <i class="bi bi-graph-up me-2"></i>รายงานความปลอดภัย
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Admin Session Management -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAdminSessions" 
                       aria-expanded="false" aria-controls="collapseAdminSessions">
                        <i class="bi bi-laptop"></i>
                        <span>จัดการ Sessions</span>
                    </a>
                    <div id="collapseAdminSessions" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Session Management:</h6>
                            <a class="collapse-item" href="{{ route('admin.sessions.index') }}">
                                <i class="bi bi-list-ul me-2"></i>รายการ Sessions
                            </a>
                            <a class="collapse-item" href="{{ route('admin.sessions.report') }}">
                                <i class="bi bi-graph-up me-2"></i>รายงาน Sessions
                            </a>
                            <a class="collapse-item" href="{{ route('admin.sessions.index', ['status' => 'active']) }}">
                                <i class="bi bi-wifi me-2"></i>Sessions ที่ใช้งาน
                            </a>
                            <a class="collapse-item" href="{{ route('admin.sessions.index', ['status' => 'expired']) }}">
                                <i class="bi bi-clock-history me-2"></i>Sessions หมดอายุ
                            </a>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-graph-up"></i>
                        <span>รายงาน</span>
                    </a>
                </li>

            @elseif(auth()->user()->role == 'super_admin')
                <!-- Super Admin Menu -->
                
                <!-- Registration Approval Management (สำคัญมาก!) -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseApprovals" 
                       aria-expanded="false" aria-controls="collapseApprovals">
                        <i class="bi bi-person-check"></i>
                        <span>จัดการอนุมัติสมาชิก</span>
                        <span class="badge bg-warning text-dark ms-auto">Super</span>
                    </a>
                    <div id="collapseApprovals" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การอนุมัติสมาชิก:</h6>
                            <a class="collapse-item" href="{{ route('admin.approvals.index') }}">
                                <i class="bi bi-list-ul me-2"></i>รายการทั้งหมด
                            </a>
                            <a class="collapse-item" href="{{ route('admin.approvals.index', ['status' => 'pending']) }}">
                                <i class="bi bi-hourglass-split me-2"></i>รอการอนุมัติ
                            </a>
                            <a class="collapse-item" href="{{ route('admin.approvals.index', ['escalated' => '1']) }}">
                                <i class="bi bi-exclamation-triangle me-2 text-warning"></i>รายการค้างนาน
                            </a>
                            <a class="collapse-item" href="{{ route('admin.approvals.index', ['status' => 'approved']) }}">
                                <i class="bi bi-check-circle me-2"></i>อนุมัติแล้ว
                            </a>
                            <a class="collapse-item" href="{{ route('admin.approvals.index', ['status' => 'rejected']) }}">
                                <i class="bi bi-x-circle me-2"></i>ปฏิเสธแล้ว
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Audit & Monitoring -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAudit" 
                       aria-expanded="false" aria-controls="collapseAudit">
                        <i class="bi bi-shield-exclamation"></i>
                        <span>Audit & Monitoring</span>
                    </a>
                    <div id="collapseAudit" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การติดตาม & ตรวจสอบ:</h6>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-clipboard-check me-2"></i>Approval Audit Logs
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-arrow-repeat me-2 text-warning"></i>Override History
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-graph-up me-2"></i>Approval Statistics
                            </a>
                            <a class="collapse-item" href="{{ route('notifications.index') }}">
                                <i class="bi bi-bell me-2"></i>Notification Center
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-bell-slash me-2"></i>Notification Logs
                            </a>
                        </div>
                    </div>
                </li>

                <!-- System Management -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystem" 
                       aria-expanded="false" aria-controls="collapseSystem">
                        <i class="bi bi-gear-wide-connected"></i>
                        <span>จัดการระบบ</span>
                    </a>
                    <div id="collapseSystem" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การจัดการระบบ:</h6>
                            <a class="collapse-item" href="{{ route('super-admin.users.index') }}">
                                <i class="bi bi-people me-2"></i>ผู้ใช้งาน (ทั้งหมด)
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.users.index', ['role' => 'admin']) }}">
                                <i class="bi bi-person-badge me-2"></i>จัดการ Admin
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.users.sessions') }}">
                                <i class="bi bi-people-fill me-2"></i>Active Sessions
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-shield-check me-2"></i>บทบาท & สิทธิ์
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-gear me-2"></i>ตั้งค่าระบบ
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Advanced Security Management for Super Admin -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSuperSecurity" 
                       aria-expanded="false" aria-controls="collapseSuperSecurity">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>ความปลอดภัยขั้นสูง</span>
                        <span class="badge bg-danger text-white ms-auto">Advanced</span>
                    </a>
                    <div id="collapseSuperSecurity" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">ความปลอดภัยระบบ:</h6>
                            <a class="collapse-item" href="{{ route('admin.security.index') }}">
                                <i class="bi bi-shield-check me-2"></i>แดชบอร์ดความปลอดภัย
                            </a>
                            <a class="collapse-item" href="{{ route('admin.security.ip.index') }}">
                                <i class="bi bi-globe me-2"></i>จัดการ IP Address
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.security.index') }}">
                                <i class="bi bi-shield-exclamation me-2"></i>Security Dashboard
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.security.devices') }}">
                                <i class="bi bi-phone me-2"></i>Device Management
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.security.ip-management') }}">
                                <i class="bi bi-globe me-2"></i>IP Management
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.security.suspicious-activity') }}">
                                <i class="bi bi-exclamation-triangle me-2"></i>Suspicious Activity
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.security.policies') }}">
                                <i class="bi bi-shield-shaded me-2"></i>Security Policies
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Super Admin Session Management -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSuperSessions" 
                       aria-expanded="false" aria-controls="collapseSuperSessions">
                        <i class="bi bi-laptop"></i>
                        <span>Session Management</span>
                        <span class="badge bg-primary text-white ms-auto">Pro</span>
                    </a>
                    <div id="collapseSuperSessions" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Session Management:</h6>
                            <a class="collapse-item" href="{{ route('super-admin.sessions.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.sessions.index') }}">
                                <i class="bi bi-list-ul me-2"></i>All Sessions
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.sessions.realtime') }}">
                                <i class="bi bi-broadcast me-2"></i>Real-time Monitor
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.sessions.system-report') }}">
                                <i class="bi bi-graph-up me-2"></i>System Reports
                            </a>
                            <a class="collapse-item" href="{{ route('super-admin.sessions.settings') }}">
                                <i class="bi bi-gear me-2"></i>Session Settings
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Reports and Analytics -->
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-graph-up"></i>
                        <span>รายงานระบบ</span>
                    </a>
                </li>

            @endif

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                อื่นๆ
            </div>

            <!-- Notifications (All Users) -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell"></i>
                    <span>การแจ้งเตือน</span>
                    @php
                        $unreadCount = auth()->user()->unreadNotifications()->count() ?? 0;
                    @endphp
                    @if($unreadCount > 0)
                        <span class="badge bg-danger ms-auto">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('profile.settings') }}">
                    <i class="bi bi-gear"></i>
                    <span>ตั้งค่า</span>
                </a>
            </li>

            <!-- Sidebar Toggler (Sidebar) -->
            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->
        
        <div id="content-wrapper" class="d-flex flex-column flex-grow-1">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
                        <i class="bi bi-list"></i>
                    </button>

                    <form class="d-none d-sm-inline-block form-inline me-auto ms-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="ค้นหา..." 
                                   aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <ul class="navbar-nav ms-auto">

                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-search"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-3 shadow" 
                                 aria-labelledby="searchDropdown">
                                <form class="form-inline me-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small" 
                                               placeholder="ค้นหา..." aria-label="Search" aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count() ?? 0;
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="badge bg-danger badge-counter">{{ $unreadCount }}</span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow" 
                                 aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header bg-primary text-white py-2 px-3 m-0">
                                    <i class="bi bi-bell me-2"></i>การแจ้งเตือน
                                    @if($unreadCount > 0)
                                        <span class="badge bg-light text-primary ms-2">{{ $unreadCount }}</span>
                                    @endif
                                </h6>
                                
                                @php
                                    $notifications = auth()->user()->notifications()->limit(5)->get() ?? collect();
                                @endphp
                                
                                @forelse($notifications as $notification)
                                    <a class="dropdown-item d-flex align-items-center {{ $notification->read_at ? '' : 'bg-light' }}" href="#">
                                        <div class="me-3">
                                            @php
                                                $notificationType = $notification->data['type'] ?? 'default';
                                            @endphp
                                            <div class="icon-circle {{ $notificationType == 'approval_override' ? 'bg-warning' : ($notificationType == 'approval_escalation' ? 'bg-danger' : 'bg-info') }}">
                                                @if($notificationType == 'approval_override')
                                                    <i class="bi bi-arrow-repeat text-white"></i>
                                                @elseif($notificationType == 'approval_escalation')
                                                    <i class="bi bi-exclamation-triangle text-white"></i>
                                                @else
                                                    <i class="bi bi-person-plus text-white"></i>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                                            <span class="font-weight-bold">{{ $notification->data['message'] ?? 'การแจ้งเตือน' }}</span>
                                            @if(!$notification->read_at)
                                                <span class="badge bg-primary ms-1">ใหม่</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="dropdown-item text-center py-3">
                                        <i class="bi bi-bell-slash text-muted"></i>
                                        <div class="small text-gray-500 mt-1">ไม่มีการแจ้งเตือน</div>
                                    </div>
                                @endforelse
                                
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center small text-gray-500" href="{{ route('notifications.index') }}">
                                    <i class="bi bi-eye me-1"></i>ดูการแจ้งเตือนทั้งหมด
                                </a>
                                @if($unreadCount > 0)
                                    <a class="dropdown-item text-center small text-primary" href="#" onclick="markAllNotificationsRead()">
                                        <i class="bi bi-check2-all me-1"></i>ทำเครื่องหมายอ่านทั้งหมด
                                    </a>
                                @endif
                            </div>
                        </li>

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-chat-dots"></i>
                                <span class="badge bg-info badge-counter">2</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow" 
                                 aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header bg-info text-white py-2 px-3 m-0">
                                    <i class="bi bi-chat-dots me-2"></i>ข้อความ
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image me-3">
                                        <img class="rounded-circle" src="https://ui-avatars.com/api/?name=Admin&color=7F9CF5&background=EBF4FF" 
                                             alt="Admin" style="width: 40px; height: 40px;">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div class="font-weight-bold">
                                        <div class="text-truncate">ระบบส่งข้อความทดสอบ...</div>
                                        <div class="small text-gray-500">Admin · 58 นาทีที่แล้ว</div>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center small text-gray-500" href="#">
                                    <i class="bi bi-chat-square-text me-1"></i>ดูข้อความทั้งหมด
                                </a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="me-2 d-none d-lg-inline text-gray-600 small">
                                    {{ auth()->user()->prefix }}{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                </span>
                                <img class="img-profile rounded-circle" 
                                     src="{{ auth()->user()->profile_image ? asset('storage/avatars/'.auth()->user()->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->first_name.' '.auth()->user()->last_name).'&color=7F9CF5&background=EBF4FF' }}" 
                                     alt="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow" 
                                 aria-labelledby="userDropdown">
                                <div class="dropdown-header text-center py-3">
                                    <img class="rounded-circle mb-2" 
                                         src="{{ auth()->user()->profile_photo ? asset('storage/'.auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                         alt="{{ auth()->user()->name }}" style="width: 60px; height: 60px;">
                                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                                    <div class="small text-muted">{{ ucfirst(auth()->user()->role) }}</div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="bi bi-person me-2 text-gray-400"></i>
                                    โปรไฟล์
                                </a>
                                <a class="dropdown-item" href="{{ route('profile.settings') }}">
                                    <i class="bi bi-gear me-2 text-gray-400"></i>
                                    ตั้งค่า
                                </a>
                                <a class="dropdown-item" href="{{ route('password.status') }}">
                                    <i class="bi bi-shield-check me-2 text-warning"></i>
                                    สถานะรหัสผ่าน
                                </a>
                                <a class="dropdown-item" href="{{ route('password.change') }}">
                                    <i class="bi bi-key me-2 text-gray-400"></i>
                                    เปลี่ยนรหัสผ่าน
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-clock-history me-2 text-gray-400"></i>
                                    ประวัติกิจกรรม
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="bi bi-box-arrow-right me-2 text-gray-400"></i>
                                    ออกจากระบบ
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                
                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>
            
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ config('app.name') }} {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
            
        </div>
        
    </div>

    <a class="scroll-to-top rounded d-flex align-items-center justify-content-center" href="#page-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" 
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="bi bi-box-arrow-right me-2"></i>ยืนยันการออกจากระบบ
                    </h5>
                    <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-question-circle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-2">คุณต้องการออกจากระบบใช่หรือไม่?</h6>
                    <p class="text-muted small mb-0">คลิก "ออกจากระบบ" หากคุณพร้อมที่จะสิ้นสุดเซสชันปัจจุบัน</p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button class="btn btn-secondary px-4" type="button" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>ยกเลิก
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>
    
    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>

    <script src="{{ asset('js/dashboard.js') }}"></script>

    <!-- Notification Management Script -->
    <script>
        function markAllNotificationsRead() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update notification badge
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error marking notifications as read:', error);
            });
        }

        // Auto-refresh notifications every 5 minutes
        setInterval(() => {
            // You can implement AJAX refresh here if needed
            // For now, we'll just show a subtle indicator
        }, 300000);

        // Add visual indicators for Super Admin features
        document.addEventListener('DOMContentLoaded', function() {
            @if(auth()->user()->role === 'super_admin')
                // Highlight Super Admin specific menu items
                const superAdminItems = document.querySelectorAll('.collapse-item');
                superAdminItems.forEach(item => {
                    if (item.textContent.includes('Override') || item.textContent.includes('รายการค้างนาน')) {
                        item.classList.add('text-warning');
                        item.style.fontWeight = '500';
                    }
                });
            @endif
        });
    </script>

    @stack('scripts')

</body>

</html>
