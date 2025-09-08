<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - IP Blocked</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            backdrop-filter: blur(10px);
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .error-title {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        .error-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .ip-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1.5rem 0;
            border-left: 4px solid #dc3545;
        }
        .ip-address {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #dc3545;
            font-size: 1.1rem;
        }
        .btn-back {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .security-tips {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: left;
        }
        .security-tips h5 {
            color: #1976d2;
            margin-bottom: 1rem;
        }
        .security-tips ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .security-tips li {
            margin-bottom: 0.5rem;
            color: #424242;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <h1 class="error-title">Access Denied</h1>
        <p class="error-subtitle">
            Your IP address has been blocked due to security reasons. 
            This may be due to multiple failed login attempts or suspicious activity.
        </p>

        <div class="ip-info">
            <strong>Your IP Address:</strong><br>
            <span class="ip-address">{{ $ip }}</span>
        </div>

        <div class="security-tips">
            <h5><i class="fas fa-info-circle me-2"></i>What can you do?</h5>
            <ul>
                <li>If you believe this is an error, please contact the system administrator</li>
                <li>Check if you are using a VPN or proxy service that might be flagged</li>
                <li>Ensure you are entering correct login credentials</li>
                <li>Wait for the temporary block to expire (if applicable)</li>
                <li>Contact support with your IP address for manual review</li>
            </ul>
        </div>

        <div class="mt-4">
            <a href="{{ url('/') }}" class="btn-back">
                <i class="fas fa-home me-2"></i>Return to Homepage
            </a>
        </div>

        <div class="mt-3">
            <small class="text-muted">
                Error Code: 403 | Time: {{ now()->format('Y-m-d H:i:s T') }}
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
