<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ยินดีต้อนรับ | {{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/welcome/welcome.css') }}">
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <div class="welcome-logo">
                <img src="{{ asset('images/logo/prapreut-logo.png') }}" alt="Prapreut" width="170px">
            </div>
            <h1 class="welcome-title">สวัสดี, ยินดีต้อนรับ</h1>
            <h2 class="welcome-app">{{ config('app.name', 'Laravel') }}</h2>
            <p class="welcome-desc">ระบบ Authentication ตัวอย่างสำหรับ Laravel<br>รองรับผู้ใช้หลายระดับ</p>
            <a href="{{ route('login') }}" class="welcome-btn">เข้าสู่ระบบ</a>
        </div>
        <div class="welcome-bg-anim"></div>
        <div class="welcome-logo"><br>
            <img src="{{ asset('images/logo/pakpoon-logo.png') }}" alt="Pakpoon" width="150px">
            <img src="{{ asset('images/logo/laravel-logo.png') }}" alt="Laravel" width="170px">
            <img src="{{ asset('images/logo/ruts-logo.png') }}" alt="Ruts" width="350px">
            <img src="{{ asset('images/logo/rmutsv-logo.png') }}" alt="RMUTSV" width="100px">
            <h1 style="color: var(--color-8)">ผู้สนับสนุนและลูกค้า</h1>
        </div>
    </div>
    <script src="{{ asset('js/welcome/welcome.js') }}"></script>
</body>
</html>