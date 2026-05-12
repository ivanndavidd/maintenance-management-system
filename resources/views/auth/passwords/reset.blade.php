<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ config('app.name', 'Warehouse Maintenance') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('{{ asset('assets/maxresdefault.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.55);
        }
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0095DA 0%, #006BA8 100%);
            color: white;
            text-align: center;
            padding: 35px 30px 25px;
        }
        .login-header i { font-size: 48px; margin-bottom: 12px; display: block; }
        .login-header h3 { font-size: 22px; font-weight: 700; margin-bottom: 5px; }
        .login-header p { font-size: 14px; opacity: 0.85; margin: 0; }
        .login-body { padding: 30px; }
        .form-control {
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            padding: 10px 15px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: #0095DA; box-shadow: 0 0 0 3px rgba(0,149,218,0.15); }
        .btn-submit {
            background: linear-gradient(135deg, #0095DA 0%, #006BA8 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            padding: 12px;
            font-size: 15px;
            width: 100%;
            transition: opacity 0.2s;
        }
        .btn-submit:hover { opacity: 0.9; color: white; }
        .back-link { text-align: center; margin-top: 20px; font-size: 14px; }
        .back-link a { color: #0095DA; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-lock-open"></i>
                <h3>Reset Password</h3>
                <p>Enter your new password below</p>
            </div>
            <div class="login-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope text-primary"></i> Email Address
                        </label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ $email ?? old('email') }}"
                               placeholder="Your email"
                               required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="fas fa-lock text-primary"></i> New Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="Minimum 8 characters"
                                   required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="fas fa-lock text-primary"></i> Confirm New Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Re-enter new password"
                                   required>
                            <i class="fas fa-eye password-toggle" id="toggleConfirm"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-check me-1"></i> Reset Password
                    </button>
                </form>

                <div class="back-link">
                    <a href="{{ route('login') }}">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            this.classList.toggle('fa-eye', isText);
            this.classList.toggle('fa-eye-slash', !isText);
        });
        document.getElementById('toggleConfirm').addEventListener('click', function () {
            const input = document.getElementById('password_confirmation');
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            this.classList.toggle('fa-eye', isText);
            this.classList.toggle('fa-eye-slash', !isText);
        });
    </script>
</body>
</html>
