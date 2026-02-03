<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Warehouse Maintenance') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.png') }}">

    <!-- Vite Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
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

        /* Overlay untuk background image */
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

        .login-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            /* HIDDEN BY DEFAULT */
            opacity: 0;
            visibility: hidden;
            transform: translateY(30px) scale(0.95);
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            pointer-events: none;
        }

        /* Show card on container hover (when NOT permanently shown) */
        .login-container:hover .login-card:not(.permanent-show) {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        /* Show card permanently when has .permanent-show class */
        .login-card.permanent-show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        /* Add a subtle hint text */
        .hover-hint {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            text-shadow: 3px 3px 15px rgba(0, 0, 0, 0.8);
            opacity: 1;
            transition: opacity 0.5s ease;
            z-index: 0;
            pointer-events: none;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.05);
            }
        }

        .login-container:hover .hover-hint {
            opacity: 0;
        }

        .login-card.permanent-show ~ .hover-hint {
            opacity: 0;
            display: none;
        }

        /* Make the entire container area hoverable */
        .login-container {
            cursor: pointer;
        }

        .login-card * {
            cursor: default;
        }
        
        .login-header {
            background: #0095DA;
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .login-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 28px;
        }
        
        .login-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #0095DA;
            box-shadow: 0 0 0 0.2rem rgba(0, 149, 218, 0.25);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: #0095DA;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #007AB8;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 149, 218, 0.4);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
        
        .register-link a {
            color: #0095DA;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
            color: #007AB8;
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #0095DA;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
            color: #007AB8;
        }
        
        .remember-me {
            margin: 20px 0;
        }
        
        .invalid-feedback {
            font-size: 13px;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .input-group-custom {
            position: relative;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="hover-hint">
            <i class="fas fa-hand-pointer mb-3"></i><br>
            Hover here to login
        </div>
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <i class="fas fa-warehouse"></i>
                <h3>Warehouse Maintenance</h3>
                <p>Please login to continue</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Oops!</strong> There were some problems with your input.
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Enter your email"
                               required 
                               autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-2">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="input-group-custom">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    @if (Route::has('password.request'))
                    <div class="forgot-password">
                        <a href="{{ route('password.request') }}">
                            <i class="fas fa-key"></i> Forgot Password?
                        </a>
                    </div>
                    @endif
                    
                    <!-- Remember Me -->
                    <div class="remember-me">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="remember" 
                                   id="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <!-- Register Link -->
                @if (Route::has('register'))
                <div class="register-link">
                    Don't have an account? <a href="{{ route('register') }}">Register here</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Password Visibility
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }

            // Show login card on hover and keep it visible after first interaction
            const loginCard = document.querySelector('.login-card');
            const loginContainer = document.querySelector('.login-container');
            let hasInteracted = false;

            // Show card permanently if there are errors or messages
            @if ($errors->any() || session('success') || session('error'))
                loginCard.classList.add('permanent-show');
                hasInteracted = true;
            @endif

            // Make card permanently visible on any click inside
            loginCard.addEventListener('click', function(e) {
                if (!hasInteracted) {
                    hasInteracted = true;
                    this.classList.add('permanent-show');
                }
            });

            // Make card permanently visible when any input is focused
            const inputs = loginCard.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    if (!hasInteracted) {
                        hasInteracted = true;
                        loginCard.classList.add('permanent-show');
                    }
                });
            });

            // Make card permanently visible on mousedown on any form element
            const formElements = loginCard.querySelectorAll('input, button, a');
            formElements.forEach(element => {
                element.addEventListener('mousedown', function() {
                    if (!hasInteracted) {
                        hasInteracted = true;
                        loginCard.classList.add('permanent-show');
                    }
                });
            });
        });
    </script>
</body>
</html>