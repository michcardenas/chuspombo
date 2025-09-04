<footer class="hostella-footer py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Logo y descripción -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="footer-branding">
                    <div class="logo-container">
                        <img src="{{ asset('images/' . ($pagina->logo ?? 'Hostella_Logo_horizontal_Blanco_.png')) }}" alt="chuspombo" class="footer-logo" height="80">
                    </div>
                    <p class="footer-description">
                        Descubre las más exclusivas villas y propiedades de lujo con chuspombo, tu socio confiable para experiencias inolvidables
                    </p>
                </div>
            </div>

            <!-- Enlaces rápidos -->
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <h5 class="footer-heading">Enlaces</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Inicio</a></li>
                    <li><a href="{{ route('properties.index') }}">Propiedades</a></li>
                    <li><a href="{{ route('for-owners') }}">Propietarios</a></li>
                    <li><a href="{{ route('about') }}">Nosotros</a></li>
                </ul>
            </div>

            <!-- Información legal -->
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <h5 class="footer-heading">Legal</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                    <li><a href="#">Políticas</a></li>
                    <li><a href="#">Términos</a></li>
                    <li><a href="#">Privacidad</a></li>
                </ul>
            </div>

            <!-- Contacto -->
            <div class="col-lg-4 col-md-4">
                <h5 class="footer-heading">Contacto</h5>
                <ul class="contact-info">
                    <li>
                        <div class="icon-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span>España, Galicia</span>
                    </li>

                    <li>
                        <div class="icon-wrapper">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span>
                            @if (isset($pagina) && $pagina->whatsapp)
                            {{ preg_match('/^\+?(\d{1,3})(\d{3})(\d{3})(\d{4})$/', preg_replace('/[^0-9]/', '', $pagina->whatsapp), $matches)
                        ? "+{$matches[1]} ({$matches[2]}) {$matches[3]}-{$matches[4]}"
                        : $pagina->whatsapp }}
                            @else
                            +1 (123) 456-7890
                            @endif
                        </span>
                    </li>

                    <li>
                        <div class="icon-wrapper">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span>info@chuspombo.com</span>
                    </li>
                </ul>

                <!-- Redes sociales dinámicas -->
                <div class="social-links">
                    @if (isset($pagina) && $pagina->instagram)
                    <a href="{{ $pagina->instagram }}" target="_blank" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    @endif
                    @if (isset($pagina) && $pagina->facebook)
                    <a href="{{ $pagina->facebook }}" target="_blank" aria-label="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    @endif
                    @if (isset($pagina) && $pagina->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pagina->whatsapp) }}" target="_blank" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="footer-divider my-4"></div>

    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="row align-items-center">
            <div class="footer-bottom text-center">
                <p class="mb-0 copyright">&copy; {{ date('Y') }} chuspombo. Todos los derechos reservados.</p>
            </div>
            <div class="col-md-6 text-md-end">
                {{-- Aquí podrías incluir links secundarios o versión --}}
            </div>
        </div>
    </div>
    </div>
</footer>