<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Absensi Eskul</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-color: #0f172a; 
            --accent-color: #6366f1;  
            --bg-color: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .auth-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-logo {
            width: 52px;
            height: 52px;
            background: var(--primary-color);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 1.6rem;
        }

        .auth-title {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.03em;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .form-label {
            font-size: 0.813rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        /* Input Group Custom */
        .input-group-custom {
            position: relative;
        }

        .input-group-custom .icon-leading {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            z-index: 10;
        }

        .form-control {
            height: 50px;
            padding: 10px 48px 10px 48px; /* Ruang untuk icon kiri dan kanan */
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.938rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
            outline: none;
        }

        /* Toggle Password Button */
        .btn-toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            z-index: 11;
            transition: all 0.2s;
        }

        .btn-toggle-password:hover {
            color: var(--accent-color);
            background: #f1f5f9;
        }

        .btn-login {
            height: 50px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 12px;
            transition: all 0.2s;
        }

        .btn-login:hover {
            background: #000;
            transform: translateY(-1px);
        }

        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.813rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }

        .animated {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Hilangkan toggle password bawaan browser (Edge/Chrome) */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card animated">
        
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-qr-code-scan"></i>
            </div>
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Masuk ke sistem absensi ekstrakurikuler</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email atau NISN</label>
                <div class="input-group-custom">
                    <i class="bi bi-envelope icon-leading"></i>
                    <input type="text" name="login" 
                           class="form-control @error('login') is-invalid @enderror" 
                           placeholder="Masukkan ID Anda"
                           value="{{ old('login') }}" required autofocus>
                </div>
                @error('login')
                    <div class="text-danger mt-2" style="font-size: 0.75rem; font-weight: 500;">
                         <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <i class="bi bi-lock icon-leading"></i>
                    <input type="password" name="password" id="passwordInput"
                           class="form-control" 
                           placeholder="••••••••" required>
                    <button type="button" class="btn-toggle-password" onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check m-0">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="form-check-label" style="cursor: pointer;">Ingat saya</label>
                </div>
                <a href="javascript:void(0)" onclick="handleForgotPassword()" class="text-decoration-none" style="font-size: 0.813rem; color: var(--accent-color); font-weight: 600;">Lupa password?</a>
            </div>

            <button type="submit" class="btn-login">
                Sign in
            </button>

            <div class="auth-footer">
                Absensi Ekstrakurikuler
            </div>
        </form>

    </div>
</div>

<script>
    // Fungsi Toggle Password
    function togglePassword() {
        const passwordInput = document.getElementById('passwordInput');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }

    // Fungsi Alert Lupa Password
    function handleForgotPassword() {
        // Anda bisa mengganti alert ini dengan SweetAlert2 jika ingin lebih mewah
        alert("Silahkan hubungi Admin kesiswaan untuk melakukan reset password akun Anda.");
    }
</script>

</body>
</html>