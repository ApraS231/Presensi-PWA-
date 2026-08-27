<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Sistem Presensi PT. CAK</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols Rounded -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

    <style>
        :root {
            --md-sys-color-primary: #1565C0;
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-surface: #FAFAFA;
            --md-sys-color-on-surface: #1C1B1F;
            --md-sys-color-on-surface-variant: #44474E;
            --md-sys-color-outline: #74777F;
            --md-sys-color-outline-variant: #C4C6CF;
            --md-sys-color-error: #BA1A1A;
            --md-sys-color-error-container: #FFDAD6;

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
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .error-card {
            background-color: #FFFFFF;
            width: 100%;
            max-width: 440px;
            border-radius: var(--md-sys-shape-corner-extra-large);
            box-shadow: var(--md-sys-elevation-2);
            padding: 40px 32px;
            border: 1px solid var(--md-sys-color-outline-variant);
            text-align: center;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background-color: var(--md-sys-color-error-container);
            color: var(--md-sys-color-error);
            border-radius: var(--md-sys-shape-corner-large);
            margin-bottom: 16px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--md-sys-color-on-surface);
            letter-spacing: -0.2px;
        }

        .brand-subtitle {
            font-size: 13.5px;
            color: var(--md-sys-color-on-surface-variant);
            margin-top: 8px;
            line-height: 1.55;
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
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: var(--md-sys-elevation-1);
            text-decoration: none;
            margin-top: 24px;
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

    <div class="error-card">
        <div class="brand-badge">
            <span class="material-symbols-rounded" style="font-size: 28px;">gpp_maybe</span>
        </div>
        <h1 class="brand-title">403 - Akses Tidak Diizinkan</h1>
        <p class="brand-subtitle">
            Anda tidak memiliki hak akses atau izin yang cukup untuk membuka modul halaman ini.
        </p>

        <a href="{{ url('/') }}" class="btn-submit">
            <span class="material-symbols-rounded" style="font-size: 18px;">home</span>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="footer-note">
            PT. Cahaya Anugrah Kalimantan &copy; {{ date('Y') }}
        </div>
    </div>

</body>
</html>
