<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>
<body>
    <nav class="navbar bg-body-tertiary fixed-top p-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="">{{ config('app.name', 'Laravel') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">{{ config('app.name', 'Laravel') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <form class="d-flex mt-3" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" />
                        <button class="btn btn-outline-success" type="submit">Search</button>
                    </form>
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3 mt-3 p-2">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ route('super_admin.dashboard') }}"><i class="bi bi-house"></i>หน้าหลัก</a>
                        </li><hr>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-folder-symlink"></i>ลิงก์ 1</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-folder-symlink"></i>ลิงก์ 2</a>
                        </li><hr>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-folder-symlink"></i>ลิงก์ 3
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-folder-symlink"></i>ลิงก์ 3-1</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-folder-symlink"></i>ลิงก์ 3-2</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-folder-symlink"></i>ลิงก์ 3-3</a></li>
                            </ul>
                        </li><hr>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-person-circle"></i>โปรไฟล์
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('super_admin.information') }}"><i class="bi bi-gear"></i>ข้อมูลส่วนตัว</a></li>
                                <li><a class="dropdown-item" href="{{ route('super_admin.accountSettings') }}"><i class="bi bi-gear"></i>บัญชีผู้ใช้</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-folder"></i>ประวัติการใช้งาน</a></li>
                            </ul>
                        </li><hr>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}"><i class="bi bi-door-open"></i>ออกจากระบบ</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <main class="px-5 p-5">
        @yield('content')
    </main>
</body>
</html>