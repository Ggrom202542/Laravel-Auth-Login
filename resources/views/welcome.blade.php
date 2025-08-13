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
            <h1 class="welcome-title">ยินดีต้อนรับ</h1>
            <h2 class="welcome-app">{{ config('app.name', 'Laravel') }}</h2>
            <p class="welcome-desc">ระบบ Authentication ตัวอย่างสำหรับ Laravel<br>รองรับผู้ใช้หลายระดับ</p>
            <a href="{{ route('login') }}" class="welcome-btn">เข้าสู่ระบบ</a>
        </div>
        <div class="welcome-bg-anim"></div>
    </div>
    <script src="{{ asset('js/welcome/welcome.js') }}"></script>
</body>
</html>