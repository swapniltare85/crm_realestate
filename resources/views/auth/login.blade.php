<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - EstatePulse Real Estate CRM</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: #0f172a;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .login-header {
            padding: 32px 32px 20px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .brand-logo-icon {
            width: 44px;
            height: 44px;
            background-color: #1d4ed8;
            color: #ffffff;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 14px;
        }

        .login-body {
            padding: 28px 32px;
        }

        .form-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 5px;
        }

        .form-control {
            font-size: 0.875rem;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 9px 12px;
            color: #0f172a;
        }

        .form-control:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
        }

        .btn-signin {
            background-color: #1d4ed8;
            color: #ffffff;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            width: 100%;
            transition: background-color 0.15s ease;
        }

        .btn-signin:hover {
            background-color: #1e40af;
            color: #ffffff;
        }

        .demo-account-btn {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            font-size: 0.8125rem;
            transition: all 0.15s;
            cursor: pointer;
            text-align: left;
        }

        .demo-account-btn:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-header">
        <div class="brand-logo-icon">
            <i class="bi bi-buildings"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.01em;">EstatePulse CRM</h4>
        <p class="text-muted small mb-0">Enterprise Real Estate Sales Management</p>
    </div>

    <div class="login-body">
        @if(session('success'))
            <div class="alert alert-success py-2 px-3 small mb-3 border-0 rounded-2" style="background-color: #ecfdf5; border-left: 3px solid #10b981 !important;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small mb-3 border-0 rounded-2" style="background-color: #fef2f2; border-left: 3px solid #ef4444 !important;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" id="loginForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="emailInput" class="form-control border-start-0" placeholder="admin@realestate.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0" placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary border-start-0 border bg-light text-muted" type="button" id="togglePasswordBtn">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check mb-0">
                    <input type="checkbox" name="remember" class="form-check-input" id="rememberMe" checked>
                    <label class="form-check-label text-muted small" for="rememberMe">Remember me</label>
                </div>
                <span class="text-muted small">Protected Session</span>
            </div>

            <button type="submit" class="btn btn-signin mb-3">
                Sign In to CRM
            </button>
        </form>

        <!-- Fast Demo Account Selector -->
        <div class="mt-4 pt-3 border-top">
            <span class="text-muted fw-semibold d-block mb-2 text-uppercase" style="font-size: 0.6875rem; letter-spacing: 0.05em;">
                Quick Test Credentials:
            </span>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="demo-account-btn" onclick="fillLogin('admin@realestate.com', 'admin123')">
                    <span class="text-dark fw-medium"><i class="bi bi-shield-check text-danger me-1"></i> Admin Manager</span>
                    <span class="badge bg-light text-secondary border">admin@realestate.com</span>
                </button>
                <button type="button" class="demo-account-btn" onclick="fillLogin('rohan@realestate.com', 'agent123')">
                    <span class="text-dark fw-medium"><i class="bi bi-person text-primary me-1"></i> Rohan Sharma</span>
                    <span class="badge bg-light text-secondary border">Sales Executive</span>
                </button>
                <button type="button" class="demo-account-btn" onclick="fillLogin('priya@realestate.com', 'agent123')">
                    <span class="text-dark fw-medium"><i class="bi bi-person text-success me-1"></i> Priya Patel</span>
                    <span class="badge bg-light text-secondary border">Sales Executive</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('passwordInput');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            togglePasswordIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    function fillLogin(email, password) {
        document.getElementById('emailInput').value = email;
        document.getElementById('passwordInput').value = password;
        document.getElementById('loginForm').submit();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
