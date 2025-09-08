<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="{{ $seo->charset ?? 'utf-8' }}">
    <meta name="viewport" content="{{ $seo->viewport ?? 'width=device-width, initial-scale=1.0' }}">
     
    <title>{{ $seo->meta_title ?? 'Chuspombo - Villas de Lujo' }}</title>
    <meta name="description" content="{{ $seo->meta_description ?? 'Chuspombo - Administración de propiedades exclusivas y villas de lujo' }}">
    <meta name="keywords" content="{{ $seo->meta_keywords ?? '' }}">
    <meta name="author" content="{{ $seo->author ?? '' }}">
    <meta name="language" content="{{ $seo->language ?? 'es' }}">
    <meta name="robots" content="{{ $seo->robots ?? 'index, follow' }}">

    @if(!empty($seo->canonical_url))
        <link rel="canonical" href="{{ $seo->canonical_url }}">
    @endif

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/Hostella_Avatar.png') }}" type="image/x-icon">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.patch.css') }}">
    @yield('styles')
        
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1 0 auto;
        }

        footer {
            flex-shrink: 0;
        }
    </style>
</head>
 
<body>
    <!-- Navegación -->
    @include('partials.navigation')
     
    <!-- Contenido principal -->
    <main>
        @yield('content')
    </main>
     
    <!-- Pie de página -->
    @include('partials.footer')
     
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
     
    @yield('scripts')
</body>
</html>