<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Presensi PT. CAK</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols Rounded -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

    <style>
        :root {
            /* M3 Color Tokens - PT. CAK Corporate Blue */
            --md-sys-color-primary: #1565C0;
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-primary-container: #D4E3FF;
            --md-sys-color-on-primary-container: #001C3A;
            --md-sys-color-surface: #FAFAFA;
            --md-sys-color-surface-container: #F0F0F4;
            --md-sys-color-surface-container-highest: #E4E4E8;
            --md-sys-color-on-surface: #1C1B1F;
            --md-sys-color-on-surface-variant: #44474E;
            --md-sys-color-outline: #74777F;
            --md-sys-color-outline-variant: #C4C6CF;
            --md-sys-color-error: #BA1A1A;
            --md-sys-color-error-container: #FFDAD6;
            --md-sys-color-on-error-container: #410002;
            --md-sys-color-success-container: #C8E6C9;
            --md-sys-color-on-success-container: #1B5E20;

            /* M3 Shape & Elevation Tokens */
            --md-sys-shape-corner-medium: 12px;
            --md-sys-shape-corner-large: 16px;
            --md-sys-shape-corner-extra-large: 28px;
            --md-sys-shape-corner-full: 9999px;
            --md-sys-elevation-1: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px -1px rgba(0,0,0,0.1);
            --md-sys-elevation-2: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .login-card {
            background-color: #FFFFFF;
            width: 100%;
            max-width: 420px;
            border-radius: var(--md-sys-shape-corner-extra-large);
            box-shadow: var(--md-sys-elevation-2);
            padding: 36px 28px;
            border: 1px solid var(--md-sys-color-outline-variant);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background-color: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-primary);
            border-radius: var(--md-sys-shape-corner-large);
            margin-bottom: 12px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--md-sys-color-primary);
            letter-spacing: -0.2px;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--md-sys-color-on-surface-variant);
            margin-top: 4px;
        }

        .alert-box {
            padding: 12px 16px;
            border-radius: var(--md-sys-shape-corner-medium);
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-error {
            background-color: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }

        .alert-success {
            background-color: var(--md-sys-color-success-container);
            color: var(--md-sys-color-on-success-container);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--md-sys-color-on-surface-variant);
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-field {
            width: 100%;
            height: 48px;
            padding: 0 16px 0 44px;
            border-radius: var(--md-sys-shape-corner-medium);
            border: 1px solid var(--md-sys-color-outline-variant);
            background-color: var(--md-sys-color-surface);
            font-family: inherit;
            font-size: 14px;
            color: var(--md-sys-color-on-surface);
            transition: all 0.2s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--md-sys-color-primary);
            box-shadow: 0 0 0 3px var(--md-sys-color-primary-container);
            background-color: #FFFFFF;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            color: var(--md-sys-color-outline);
            pointer-events: none;
            font-size: 20px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--md-sys-color-outline);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }

        .toggle-password:hover {
            color: var(--md-sys-color-on-surface);
        }

        .remember-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--md-sys-color-on-surface-variant);
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background-color: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            border: none;
            border-radius: var(--md-sys-shape-corner-full);
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.1px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: var(--md-sys-elevation-1);
        }

        .btn-submit:hover {
            opacity: 0.92;
            box-shadow: var(--md-sys-elevation-2);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .footer-note {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--md-sys-color-outline);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <img src="{{ asset('images/logo-ptcak.svg') }}" alt="Logo PT. CAK" style="height: 48px; width: auto; margin-bottom: 12px;">
            <h1 class="brand-title">Presensi PT. CAK</h1>
            <p class="brand-subtitle">Sistem Presensi Biometrik & Geospasial</p>
        </div>

        @if(session('success'))
            <div class="alert-box alert-success">
                <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-box alert-error">
                <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="login" class="form-label">NIK atau Email</label>
                <div class="input-wrapper">
                    <span class="material-symbols-rounded input-icon">person</span>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        class="input-field" 
                        placeholder="Contoh: SA001 atau user@ptcak.com" 
                        value="{{ old('login') }}" 
                        required 
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="input-wrapper">
                    <span class="material-symbols-rounded input-icon">lock</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="input-field" 
                        placeholder="Masukkan kata sandi Anda" 
                        required
                    >
                    <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Lihat kata sandi">
                        <span class="material-symbols-rounded" id="togglePasswordIcon">visibility</span>
                    </button>
                </div>
            </div>

            <div class="remember-wrapper">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Masuk ke Sistem</span>
                <span class="material-symbols-rounded" style="font-size: 18px;">login</span>
            </button>
        </form>

        <div class="footer-note">
            PT. Cahaya Anugrah Kalimantan &copy; {{ date('Y') }}
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = 'visibility';
            }
        });
    </script>
</body>
</html>
