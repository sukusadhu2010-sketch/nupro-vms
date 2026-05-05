<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'VMS Pro') }}</title>

    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Login CSS (External) -->
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>
    <div class="container-fluid p-0 min-vh-100 d-flex align-items-center">
        <div class="row g-0 w-100">
            <!-- Left Hero Section (Hidden on Mobile) -->
            <div class="col-lg-6 login-hero d-none d-lg-flex">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-10 col-lg-8 text-center hero-content">
                            <div class="mb-5">
                                <i class="fas fa-users-cog fa-5x text-white mb-4" style="opacity: 0.9;"></i>
                            </div>
                            <h1 class="display-4 fw-bold mb-4">VMS Pro</h1>
                            <p class="lead mb-5">Advanced Vendor Management System for streamlined operations, secure
                                access, and powerful insights.</p>
                            <div class="mt-5">
                                <i class="fas fa-shield-alt fa-3x text-white mb-3"></i>
                                <p class="text-white-50 mb-0">Your data is secure with us</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Login Form Section -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="w-100" style="max-width: 450px;">
                    <div class="login-card p-4 p-lg-5">
                        <!-- Logo/Brand -->
                        <div class="text-center mb-4">
                            <img src="https://www.nuprovalve.com/images/logo.jpg" class="mb-2" style="width:100px; height:100px" >
                            <h2 class="h3 fw-bold mb-2 text-dark">Welcome Back</h2>
                            
                            <p class="text-muted mb-0">Sign in to your VMS Pro account</p>
                        </div>

                        <!-- Login Form -->
                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <!-- Email -->
                            <div class="input-group mb-4">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                    id="email" name="email" placeholder="Email address" required
                                    autocomplete="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="input-group mb-4">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Password" required
                                    autocomplete="current-password">
                                <button class="btn btn-secondary border-0 toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Remember & Forgot Password -->
                            <!--<div class="d-flex justify-content-between align-items-center mb-4">-->
                            <!--    <div class="form-check">-->
                            <!--        <input class="form-check-input" type="checkbox" name="remember" id="remember">-->
                            <!--        <label class="form-check-label text-muted" for="remember">-->
                            <!--            Remember me-->
                            <!--        </label>-->
                            <!--    </div>-->
                            <!--    <a href="#" class="text-decoration-none fw-medium" style="color: #667eea;">Forgot-->
                            <!--        Password?</a>-->
                            <!--</div>-->

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-login w-100 mb-4 text-white fw-semibold shadow-lg">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                Sign In Securely
                            </button>
                        </form>

                        <!-- Divider -->
                        <!--<div class="divider my-4">-->
                        <!--    <span>or continue with</span>-->
                        <!--</div>-->

                        <!-- Social Login Buttons -->
                        <!--<div class="row g-3 mb-4">-->
                        <!--    <div class="col-6">-->
                        <!--        <button type="button" class="btn social-btn w-100 text-dark">-->
                        <!--            <i class="fab fa-google me-2"></i>Google-->
                        <!--        </button>-->
                        <!--    </div>-->
                        <!--    <div class="col-6">-->
                        <!--        <button type="button" class="btn social-btn w-100 text-dark">-->
                        <!--            <i class="fab fa-linkedin-in me-2"></i>LinkedIn-->
                        <!--        </button>-->
                        <!--    </div>-->
                        <!--</div>-->

                        <!-- Create Account -->
                        <!--<div class="text-center">-->
                        <!--    <p class="text-muted mb-0">-->
                        <!--        Don't have an account?-->
                        <!--        <a href="#" class="fw-semibold text-dark text-decoration-none"-->
                        <!--            style="color: #667eea;">Create one now</a>-->
                        <!--    </p>-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Password toggle functionality
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
        });

        // Input animations
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>

</html>
