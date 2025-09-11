<!-- resources/views/partials/navigation.blade.php -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top py-3">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ asset('images/' . ($pagina->logo ?? 'Hostella_logo_horizontal.png')) }}" 
                 alt="Chuspombo" class="logo" style="height: 50px; object-fit: contain;">
        </a>

        <!-- Botón móvil -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"
                aria-controls="mobileMenu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menú desktop -->
        <div class="collapse navbar-collapse d-none d-lg-flex" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('properties.index') ? 'active' : '' }}" href="{{ route('properties.index') }}">Propiedades</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Nosotros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contacto</a>
                </li>
            </ul>

            <!-- Redes sociales -->
            <div class="hostella-social-nav d-flex align-items-center ms-3 gap-2">
                @if (!empty($pagina->instagram))
                    <a href="{{ $pagina->instagram }}" target="_blank" class="hostella-social-icon instagram" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                @endif

                @if (!empty($pagina->facebook))
                    <a href="{{ $pagina->facebook }}" target="_blank" class="hostella-social-icon facebook" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                @endif

                @if (!empty($pagina->whatsapp))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pagina->whatsapp) }}" target="_blank" class="hostella-whatsapp-btn" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>

<!-- Offcanvas Móvil -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="mb-0 fw-bold" id="mobileMenuLabel">Menú</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('properties.index') ? 'active' : '' }}" href="{{ route('properties.index') }}">Propiedades</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Nosotros</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contacto</a>
            </li>
        </ul>

        <!-- Redes sociales también en móvil -->
        <div class="d-flex gap-3 mt-4">
            @if (!empty($pagina->instagram))
                <a href="{{ $pagina->instagram }}" target="_blank" class="hostella-social-icon instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            @endif
            @if (!empty($pagina->facebook))
                <a href="{{ $pagina->facebook }}" target="_blank" class="hostella-social-icon facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
            @endif
            @if (!empty($pagina->whatsapp))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pagina->whatsapp) }}" target="_blank" class="hostella-whatsapp-btn">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
            @endif
        </div>
    </div>
</div>
