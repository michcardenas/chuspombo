<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión | Chuspombo</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" href="{{ asset('images/Hostella_logo_horizontal.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --hostella-primary: #1a1a1a;
            /* negro principal */
            --hostella-secondary: #FFD700;
            /* dorado */
            --hostella-light: #f8f9fa;
            --hostella-dark: #000000;
            --hostella-accent: #D4AF37;
            /* dorado suave */
        }

        * {
            box-sizing: border-box;
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
            /* Fondo oscuro con halos dorados suaves */
            background:
                radial-gradient(900px circle at 10% 15%, rgba(212, 175, 55, .18), transparent 40%),
                radial-gradient(900px circle at 85% 85%, rgba(212, 175, 55, .12), transparent 45%),
                linear-gradient(135deg, #0b0b0b 0%, #1c1c1c 100%);
        }

        .login-container {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
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
            margin-bottom: 22px;
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
            background-color: var(--hostella-primary);
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
            filter: brightness(.93);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .error {
            color: #d33;
            font-size: 0.95rem;
            margin-bottom: 14px;
        }

        /* Detalle dorado fino bajo el título */
        .title-underline {
            width: 56px;
            height: 3px;
            margin: 10px auto 0;
            background: linear-gradient(90deg, var(--hostella-secondary), transparent);
            border-radius: 2px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 28px 20px;
            }

            .login-logo {
                width: 150px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <img src="{{ asset('images/Hostella_logo_horizontal.png') }}" alt="Hostella Logo" class="login-logo">

        <h2>Iniciar Sesión</h2>
        <div class="title-underline"></div>

        @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn-login">Ingresar</button>
        </form>
    </div>

</body>

</html>