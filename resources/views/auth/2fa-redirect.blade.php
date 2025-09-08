<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="1;url={{ $redirect_url }}">
    <title>กำลังเปลี่ยนเส้นทาง...</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
        .redirect-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="redirect-container">
        <div class="spinner"></div>
        <h3>{{ $message }}</h3>
        <p>กำลังเปลี่ยนเส้นทางไปยังหน้ายืนยันตัวตน...</p>
        <p><a href="{{ $redirect_url }}">หากไม่เปลี่ยนเส้นทางโดยอัตโนมัติ คลิกที่นี่</a></p>
    </div>

    <script>
        // Force redirect ด้วย JavaScript
        setTimeout(function() {
            window.location.href = '{{ $redirect_url }}';
        }, 1000);
    </script>
</body>
</html>
