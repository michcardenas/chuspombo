<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            <img style='height: 6rem;' src="{{ asset('images/Hostella_Avatar.png') }}" alt="Chuspombo Admin">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pagina.propiedades.*') ? 'active' : '' }}" href="{{ route('admin.pagina.propiedades.edit') }}">Propiedades</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pagos*') ? 'active' : '' }}" href="{{ route('admin.pagos.index') }}">
                        Pagos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">Nosotros</a>
                </li>

                {{-- NUEVO: Contacto (Admin) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pagina.contacto.*') ? 'active' : '' }}" href="{{ route('admin.pagina.contacto.edit') }}">Contacto</a>
                </li>

                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link btn btn-link text-danger p-0">Salir</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
