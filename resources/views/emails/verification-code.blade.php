<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .verification-code {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
        }
        .info-text {
            color: #666;
            font-size: 14px;
            margin: 15px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box strong {
            color: #856404;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Warehouse Maintenance System</h1>
            <p>Email Verification</p>
        </div>

        <div class="email-body">
            <p class="greeting">Hello <strong>{{ $userName }}</strong>,</p>

            <p>Thank you for registering with Warehouse Maintenance System. To complete your registration, please use the verification code below:</p>

            <div class="verification-code">
                {{ $code }}
            </div>

            <p class="info-text">Enter this code on the verification page to confirm your email address and activate your account.</p>

            <div class="warning-box">
                <strong>Important:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>This code will expire in <strong>15 minutes</strong></li>
                    <li>Do not share this code with anyone</li>
                    <li>If you did not request this code, please ignore this email</li>
                </ul>
            </div>

            <p class="info-text">If you're having trouble, please contact our support team.</p>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Warehouse Maintenance System. All rights reserved.</p>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
