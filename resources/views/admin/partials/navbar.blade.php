<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <div class="sidenav-toggler sidenav-toggler-inner d-xl-none d-block">
            <button class="btn btn-link p-0" id="iconNavbarSidenav">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
            </button>
        </div>
        <nav aria-label="breadcrumb">
            
        </nav>

        <ul class="navbar-nav ms-auto justify-content-end">
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
                <a href="#" class="nav-link text-secondary px-0 dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-user me-sm-1"></i>
                    <span class="d-sm-inline d-none">{{ auth()->user()->name }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end px-2 py-3">
                    {{-- <li>
                        <a class="dropdown-item border-radius-md" href="{{ route('profile.edit') }}">
                            Perfil
                        </a>
                    </li> --}}

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item border-radius-md">
                                Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>

    </div>
</nav>