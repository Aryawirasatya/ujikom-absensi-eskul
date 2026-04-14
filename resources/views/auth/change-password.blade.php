<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password — Absensi Eskul</title>

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

        .auth-icon {
            width: 52px;
            height: 52px;
            background: #fef2f2; /* Light Red */
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #ef4444; /* Red */
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
            line-height: 1.5;
        }

        .form-label {
            font-size: 0.813rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 16px;
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
            padding: 10px 48px 10px 48px;
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

        .btn-toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            z-index: 11;
        }

        .btn-toggle-password:hover {
            color: var(--accent-color);
            background: #f1f5f9;
        }

        .btn-save {
            height: 50px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s;
        }

        .btn-save:hover {
            background: #000;
            transform: translateY(-1px);
        }

        .animated {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        input::-ms-reveal { display: none; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card animated">
        
        <div class="auth-header">
            <div class="auth-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="auth-title">Ganti Password</h1>
            <p class="auth-subtitle">Demi keamanan, silahkan perbarui password default Anda.</p>
        </div>

        @if(session('warning'))
            <div class="alert alert-warning border-0 small mb-4" style="background: #fffbeb; color: #92400e; border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <div class="mb-1">
                <label class="form-label">Password Baru</label>
                <div class="input-group-custom">
                    <i class="bi bi-key icon-leading"></i>
                    <input type="password" name="password" id="passInput"
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Minimal 8 karakter" required>
                    <button type="button" class="btn-toggle-password" onclick="toggle('passInput', 'icon1')">
                        <i class="bi bi-eye" id="icon1"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger mb-3" style="font-size: 0.75rem; font-weight: 500;">
                         {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group-custom">
                    <i class="bi bi-check2-circle icon-leading"></i>
                    <input type="password" name="password_confirmation" id="confirmInput"
                           class="form-control" 
                           placeholder="Ulangi password baru" required>
                    <button type="button" class="btn-toggle-password" onclick="toggle('confirmInput', 'icon2')">
                        <i class="bi bi-eye" id="icon2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-save shadow-sm">
                Perbarui Password
            </button>

            <div class="text-center mt-4">
                <p class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    Sistem Absensi ekstrakulikuler
                </p>
            </div>
        </form>

    </div>
</div>

<script>
    function toggle(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>

</body>
</html>