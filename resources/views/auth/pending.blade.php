<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Pending - {{ config('app.name', 'Warehouse Maintenance') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">

    <style>
        body {
            background: url('{{ asset('assets/maxresdefault.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 0;
        }

        .pending-container {
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .pending-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .pending-header {
            background: #0095DA;
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
            color: #0095DA;
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
            border-left: 4px solid #0095DA;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-box h5 {
            color: #0095DA;
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
            background: #E6F7FF;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #B3E0F7;
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
            color: #0095DA;
        }

        .btn-back {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            background: #0095DA;
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
            background: #007AB8;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 149, 218, 0.4);
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
            color: #0095DA;
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
                @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>{{ session('success') }}</strong>
                </div>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Registration Successful!</strong> Your account has been created.
                </div>
                @endif

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
                    <p><i class="fas fa-envelope"></i> ivan.david@gdn-commerce.com</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
