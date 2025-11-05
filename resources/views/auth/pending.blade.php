<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Pending - {{ config('app.name', 'Warehouse Maintenance') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .pending-container {
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .pending-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .pending-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .pending-header .icon-container {
            margin-bottom: 20px;
        }
        
        .pending-header i.main-icon {
            font-size: 70px;
            animation: pulse 2s infinite;
            display: inline-block;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.8; }
        }
        
        .pending-header h3 {
            margin: 15px 0;
            font-weight: 600;
            font-size: 26px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 25px;
            background: rgba(255, 255, 255, 0.95);
            color: #f5576c;
            border-radius: 25px;
            font-weight: 600;
            font-size: 15px;
            margin-top: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge i {
            font-size: 16px;
        }
        
        .pending-body {
            padding: 35px 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .info-box h5 {
            color: #667eea;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-box ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .info-box li {
            margin-bottom: 10px;
            color: #6c757d;
            line-height: 1.6;
        }
        
        .user-info {
            background: linear-gradient(135deg, #e7f3ff 0%, #f0f7ff 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #d0e7ff;
        }
        
        .user-info p {
            margin: 8px 0;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-info strong {
            color: #2c3e50;
            min-width: 120px;
        }
        
        .user-info i {
            width: 18px;
            text-align: center;
            color: #667eea;
        }
        
        .btn-back {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            font-size: 15px;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
            color: white;
        }
        
        .contact-info {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        
        .contact-info p {
            margin: 5px 0;
        }
        
        .contact-info i {
            color: #667eea;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
        }
        
        .alert i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="pending-container">
        <div class="pending-card">
            <!-- Header -->
            <div class="pending-header">
                <div class="icon-container">
                    <i class="fas fa-hourglass-half main-icon"></i>
                </div>
                <h3>Account Pending Approval</h3>
                <div class="status-badge">
                    <i class="fas fa-clock"></i>
                    <span>Awaiting Activation</span>
                </div>
            </div>
            
            <!-- Body -->
            <div class="pending-body">
                <!-- Success Message -->
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Registration Successful!</strong> Your account has been created.
                </div>

                <!-- User Info -->
                <div class="user-info">
                    <p>
                        <strong><i class="fas fa-user"></i> Name:</strong>
                        <span>{{ $user->name ?? 'N/A' }}</span>
                    </p>
                    <p>
                        <strong><i class="fas fa-id-card"></i> Employee ID:</strong>
                        <span>{{ $user->employee_id ?? 'N/A' }}</span>
                    </p>
                    <p>
                        <strong><i class="fas fa-envelope"></i> Email:</strong>
                        <span>{{ $user->email ?? 'N/A' }}</span>
                    </p>
                    <p>
                        <strong><i class="fas fa-calendar"></i> Registered:</strong>
                        <span>{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : 'Just now' }}</span>
                    </p>
                </div>

                <!-- Info Box -->
                <div class="info-box">
                    <h5>
                        <i class="fas fa-info-circle"></i>
                        What's Next?
                    </h5>
                    <ul>
                        <li><strong>Account Review:</strong> Your account is currently under review by our administrators.</li>
                        <li><strong>Approval Process:</strong> An admin will verify your registration details and activate your account.</li>
                        <li><strong>Check Status:</strong> Please check back regularly or wait for an email confirmation.</li>
                        <li><strong>Estimated Time:</strong> Approval usually takes 1-2 business days.</li>
                    </ul>
                </div>

                <!-- Important Note -->
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> You will not be able to login until your account is activated by an administrator.
                </div>

                <!-- Back Button -->
                <a href="{{ route('login') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Login</span>
                </a>

                <!-- Contact Info -->
                <div class="contact-info">
                    <p><strong>Need help? Contact administrator at:</strong></p>
                    <p><i class="fas fa-envelope"></i> admin@warehouse.com</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>