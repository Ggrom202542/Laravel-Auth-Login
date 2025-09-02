<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
    @stack('styles')
</head>

<body id="page-top">

    <div id="wrapper" class="d-flex">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
                <div class="sidebar-brand-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div class="sidebar-brand-text mx-3">{{ config('app.name', 'Laravel') }}</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ request()->is('*/dashboard') ? 'active' : '' }}">
                @if(auth()->user()->role === 'user')
                    <a class="nav-link" href="{{ route('user.dashboard') }}">
                @elseif(auth()->user()->role === 'admin')
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                @else
                    <a class="nav-link" href="{{ route('super-admin.dashboard') }}">
                @endif
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                การจัดการ
            </div>

            @if(auth()->user()->role === 'user')
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProfile" 
                       aria-expanded="false" aria-controls="collapseProfile">
                        <i class="bi bi-person-circle"></i>
                        <span>โปรไฟล์</span>
                    </a>
                    <div id="collapseProfile" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การจัดการโปรไฟล์:</h6>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-eye me-2"></i>ดูโปรไฟล์
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-pencil-square me-2"></i>แก้ไขโปรไฟล์
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน
                            </a>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-clock-history"></i>
                        <span>ประวัติกิจกรรม</span>
                    </a>
                </li>

            @elseif(auth()->user()->role === 'admin')
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUsers" 
                       aria-expanded="false" aria-controls="collapseUsers">
                        <i class="bi bi-people"></i>
                        <span>จัดการผู้ใช้</span>
                    </a>
                    <div id="collapseUsers" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การจัดการผู้ใช้:</h6>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-list-ul me-2"></i>รายการผู้ใช้
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-person-plus me-2"></i>เพิ่มผู้ใช้ใหม่
                            </a>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-person-lock me-2"></i>ผู้ใช้ถูกล็อค
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

            @else
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystem" 
                       aria-expanded="false" aria-controls="collapseSystem">
                        <i class="bi bi-gear-wide-connected"></i>
                        <span>จัดการระบบ</span>
                    </a>
                    <div id="collapseSystem" class="collapse" data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">การจัดการระบบ:</h6>
                            <a class="collapse-item" href="#">
                                <i class="bi bi-people me-2"></i>ผู้ใช้งาน
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

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-database"></i>
                        <span>ฐานข้อมูล</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-shield-exclamation"></i>
                        <span>Security Logs</span>
                    </a>
                </li>
            @endif

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                อื่นๆ
            </div>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-gear"></i>
                    <span>ตั้งค่า</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        
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
                                <span class="badge bg-danger badge-counter">3</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow" 
                                 aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header bg-primary text-white py-2 px-3 m-0">
                                    <i class="bi bi-bell me-2"></i>การแจ้งเตือน
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="me-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="bi bi-file-text text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">{{ date('d M Y') }}</div>
                                        <span class="font-weight-bold">มีรายงานใหม่พร้อมดาวน์โหลด!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="me-3">
                                        <div class="icon-circle bg-success">
                                            <i class="bi bi-person-check text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">{{ date('d M Y') }}</div>
                                        <span class="font-weight-bold">ผู้ใช้ใหม่ลงทะเบียนเข้าระบบ</span>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center small text-gray-500" href="#">
                                    <i class="bi bi-eye me-1"></i>ดูการแจ้งเตือนทั้งหมด
                                </a>
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
                                <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                                <img class="img-profile rounded-circle" 
                                     src="{{ auth()->user()->profile_photo ? asset('storage/'.auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                     alt="{{ auth()->user()->name }}">
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
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person me-2 text-gray-400"></i>
                                    โปรไฟล์
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-gear me-2 text-gray-400"></i>
                                    ตั้งค่า
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

    <script src="{{ asset('js/dashboard.js') }}"></script>

    @stack('scripts')

</body>

</html>
