<!DOCTYPE html>
<html lang="es">
<head>
    @include('admin.partials.head')
</head>

<body class="g-sidenav-show bg-gray-200">

    @include('admin.partials.sidebar')

    <main class="main-content position-relative border-radius-sm">
        @include('admin.partials.navbar')

        <div class="container-fluid py-4">
            {{ $slot }}
        </div>
    </main>

    @include('admin.partials.scripts')
</body>
</html>