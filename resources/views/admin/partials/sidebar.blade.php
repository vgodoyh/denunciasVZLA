<aside class="sidenav bg-gray-900 navbar navbar-vertical navbar-expand-xs border-0 fixed-start" id="sidenav-main">

    <div class="sidenav-header">
        <a class="navbar-brand m-0 text-light d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>
    </div>

    <hr class="horizontal light mt-0">

    <div class="navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            <li class="nav-item">
                <a class="nav-link text-white" href="{{route('denuncia.index')}}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-file-circle-plus text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Denuncias</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-chart-line text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Informes</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white opacity-6">
                    Gestión del Sistema
                </h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{route('tipo_denuncia.index')}}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-tags text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Tipo denuncia</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{route('estado.index')}}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                      <i class="fa-solid fa-map-pin text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Estados</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('emisor.*') || request()->routeIs('tipo_emisor.*') || request()->routeIs('tipo_red_social.*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse"
                href="#submenu-emisor"
                aria-expanded="{{ request()->routeIs('emisor.*') || request()->routeIs('tipo_emisor.*') || request()->routeIs('tipo_red_social.*') ? 'true' : 'false' }}">
                    <i class="fas fa-bullhorn text-white"></i>
                    <span class="nav-link-text ms-1">Emisor</span>
                </a>

                <div class="collapse {{ request()->routeIs('emisor.*') || request()->routeIs('tipo_emisor.*') || request()->routeIs('tipo_red_social.*') ? 'show' : '' }}"
                    id="submenu-emisor">
                    <ul class="nav ms-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('emisor.*') ? 'active' : '' }}" href="{{ route('emisor.index') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-circle" style="font-size:5px;"></i></span>
                                <span class="sidenav-normal">Emisores</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tipo_emisor.*') ? 'active' : '' }}" href="{{ route('tipo_emisor.index') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-circle" style="font-size:5px;"></i></span>
                                <span class="sidenav-normal">Tipos de emisor</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tipo_red_social.*') ? 'active' : '' }}" href="{{ route('tipo_red_social.index') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-circle" style="font-size:5px;"></i></span>
                                <span class="sidenav-normal">Tipos de red social</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{route('palabras_clave.index')}}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Palabras claves</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white opacity-6">
                    Seguridad del Sistema
                </h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{route('permission.index')}}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-unlock text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Permisos</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{route('role.index')}}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-gear text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Roles</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="{{route('user.index')}}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-users text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Usuarios</span>
                </a>
            </li>

        </ul>
    </div>

    <div class="sidenav-footer mx-2">
        <div class="card card-plain shadow-none">
            <img class="w-50 mx-auto" src="{{ asset('assets/img/logo_denuncias_vzla.png') }}" alt="logo_denuncias_vzla">

            <div class="card-body text-center p-3 w-100 pt-0">
                <p class="mb-0 text-xs text-white">
                    © {{ date('Y') }} Denuncias Vzla
                </p>
            </div>
        </div>
    </div>

</aside>