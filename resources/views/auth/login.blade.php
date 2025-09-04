<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión | Hostella</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- si tienes un CSS global -->
    <link rel="icon" href="{{ asset('images/Hostella_Avatar.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #01176d, #0056d2);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            text-align: center;
        }

        .login-logo {
            width: 180px;
            margin-bottom: 25px;
        }

        h2 {
            color: #01176d;
            font-size: 22px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
        }

        .form-group input:focus {
            border-color: #01176d;
            outline: none;
        }

        .btn-login {
            width: 100%;
            background-color: #01176d;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background-color: #0033a3;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-logo {
                width: 140px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="{{ asset('images/Hostella_logo_horizontal.png') }}" alt="Hostella Logo" class="login-logo">

        <h2>Iniciar Sesión</h2>

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
