<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - {{ config('app.name', 'Warehouse Maintenance') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome from jsDelivr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }

        .verify-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
        }

        .verify-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .verify-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .verify-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .verify-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .verify-body {
            padding: 40px 30px;
        }

        .email-display {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
        }

        .email-display span {
            font-weight: 600;
            color: #667eea;
        }

        .code-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 25px;
        }

        .code-input {
            width: 50px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .code-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .code-input.filled {
            border-color: #667eea;
            background-color: #f8f9fe;
        }

        .btn-verify {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-verify:disabled {
            background: #ccc;
            transform: none;
            box-shadow: none;
        }

        .resend-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .resend-section p {
            color: #6c757d;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .btn-resend {
            background: none;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-resend:hover {
            background: #667eea;
            color: white;
        }

        .btn-resend:disabled {
            border-color: #ccc;
            color: #ccc;
            cursor: not-allowed;
        }

        .timer {
            font-size: 14px;
            color: #6c757d;
            margin-top: 10px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #6c757d;
            text-decoration: none;
        }

        .back-link a:hover {
            color: #667eea;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .icon-envelope {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            <!-- Header -->
            <div class="verify-header">
                <i class="fas fa-envelope-open-text icon-envelope"></i>
                <h3>Verify Your Email</h3>
                <p>Enter the 6-digit code we sent you</p>
            </div>

            <!-- Body -->
            <div class="verify-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <div class="email-display">
                    <i class="fas fa-envelope"></i> Code sent to: <span>{{ $email }}</span>
                </div>

                <form method="POST" action="{{ route('verify.code') }}" id="verifyForm">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="code" id="codeInput">

                    <div class="code-inputs">
                        <input type="text" maxlength="1" class="code-input" data-index="0" inputmode="numeric" pattern="[0-9]*" autofocus>
                        <input type="text" maxlength="1" class="code-input" data-index="1" inputmode="numeric" pattern="[0-9]*">
                        <input type="text" maxlength="1" class="code-input" data-index="2" inputmode="numeric" pattern="[0-9]*">
                        <input type="text" maxlength="1" class="code-input" data-index="3" inputmode="numeric" pattern="[0-9]*">
                        <input type="text" maxlength="1" class="code-input" data-index="4" inputmode="numeric" pattern="[0-9]*">
                        <input type="text" maxlength="1" class="code-input" data-index="5" inputmode="numeric" pattern="[0-9]*">
                    </div>

                    @error('code')
                        <div class="text-danger text-center mb-3">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-verify" id="verifyBtn" disabled>
                        <i class="fas fa-check-circle"></i> Verify & Complete Registration
                    </button>
                </form>

                <!-- Resend Section -->
                <div class="resend-section">
                    <p>Didn't receive the code?</p>
                    <form method="POST" action="{{ route('verify.resend') }}" id="resendForm">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit" class="btn btn-resend" id="resendBtn">
                            <i class="fas fa-redo"></i> Resend Code
                        </button>
                    </form>
                    <div class="timer" id="timer" style="display: none;">
                        Resend available in <span id="countdown">60</span> seconds
                    </div>
                </div>

                <!-- Back Link -->
                <div class="back-link">
                    <a href="{{ route('register') }}">
                        <i class="fas fa-arrow-left"></i> Back to Registration
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInputs = document.querySelectorAll('.code-input');
            const codeHiddenInput = document.getElementById('codeInput');
            const verifyBtn = document.getElementById('verifyBtn');
            const resendBtn = document.getElementById('resendBtn');
            const timerDiv = document.getElementById('timer');
            const countdownSpan = document.getElementById('countdown');

            // Handle input for each code box
            codeInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');

                    if (this.value.length === 1) {
                        this.classList.add('filled');
                        // Move to next input
                        if (index < codeInputs.length - 1) {
                            codeInputs[index + 1].focus();
                        }
                    } else {
                        this.classList.remove('filled');
                    }

                    updateCode();
                });

                input.addEventListener('keydown', function(e) {
                    // Handle backspace
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        codeInputs[index - 1].focus();
                        codeInputs[index - 1].value = '';
                        codeInputs[index - 1].classList.remove('filled');
                    }
                });

                // Handle paste
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);

                    pastedData.split('').forEach((char, i) => {
                        if (codeInputs[i]) {
                            codeInputs[i].value = char;
                            codeInputs[i].classList.add('filled');
                        }
                    });

                    // Focus last filled or next empty
                    const focusIndex = Math.min(pastedData.length, codeInputs.length - 1);
                    codeInputs[focusIndex].focus();

                    updateCode();
                });
            });

            function updateCode() {
                let code = '';
                codeInputs.forEach(input => {
                    code += input.value;
                });
                codeHiddenInput.value = code;

                // Enable/disable verify button
                verifyBtn.disabled = code.length !== 6;
            }

            // Resend cooldown (60 seconds)
            let cooldownTime = 60;
            let cooldownInterval;

            function startCooldown() {
                resendBtn.disabled = true;
                timerDiv.style.display = 'block';
                cooldownTime = 60;

                cooldownInterval = setInterval(() => {
                    cooldownTime--;
                    countdownSpan.textContent = cooldownTime;

                    if (cooldownTime <= 0) {
                        clearInterval(cooldownInterval);
                        resendBtn.disabled = false;
                        timerDiv.style.display = 'none';
                    }
                }, 1000);
            }

            // Start cooldown if there's a success message (code was just sent)
            @if(session('success'))
                startCooldown();
            @endif

            // Handle resend form
            document.getElementById('resendForm').addEventListener('submit', function() {
                startCooldown();
            });
        });
    </script>
</body>
</html>
