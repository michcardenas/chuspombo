<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña | Chuspombo</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" href="{{ asset('images/Hostella_logo_horizontal.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- copia EXACTA del <style> usado arriba -->
    <style>
        :root {
            --hostella-primary: #1a1a1a;
            --hostella-secondary: #FFD700;
            --hostella-light: #f8f9fa;
            --hostella-dark: #000;
            --hostella-accent: #D4AF37;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: #222;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background:
                radial-gradient(900px circle at 10% 15%, rgba(212, 175, 55, .18), transparent 40%),
                radial-gradient(900px circle at 85% 85%, rgba(212, 175, 55, .12), transparent 45%),
                linear-gradient(135deg, #0b0b0b 0%, #1c1c1c 100%);
        }

        .login-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .18);
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
            text-align: center;
        }

        .login-logo {
            width: 180px;
            margin-bottom: 22px;
        }

        h2 {
            color: var(--hostella-primary);
            font-size: 22px;
            margin-bottom: 10px;
        }

        .title-underline {
            width: 56px;
            height: 3px;
            margin: 10px auto 0;
            background: linear-gradient(90deg, var(--hostella-secondary), transparent);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: border .2s, box-shadow .2s;
        }

        .form-group input:focus {
            border-color: var(--hostella-accent);
            outline: none;
            box-shadow: 0 0 0 .2rem rgba(212, 175, 55, .18);
        }

        .btn-login {
            width: 100%;
            background: var(--hostella-primary);
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: filter .25s, transform .05s;
        }

        .btn-login:hover {
            filter: brightness(.93)
        }

        .btn-login:active {
            transform: translateY(1px)
        }

        .error {
            color: #d33;
            font-size: .95rem;
            margin-bottom: 14px;
        }

        .muted-link {
            display: block;
            margin-top: 14px;
            font-size: .9rem;
            color: var(--hostella-accent);
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width:480px) {
            .login-container {
                padding: 28px 20px
            }

            .login-logo {
                width: 150px
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="{{ asset('images/Hostella_logo_horizontal.png') }}" alt="Logo" class="login-logo">
        <h2>Restablecer contraseña</h2>
        <div class="title-underline"></div>

        @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label for="password">Nueva contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>

            <button type="submit" class="btn-login">Restablecer</button>
        </form>

        <a href="{{ route('login') }}" class="muted-link">← Volver al login</a>
    </div>
</body>

</html>