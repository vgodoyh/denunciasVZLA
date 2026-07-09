<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Denuncias Vzla') : config('app.name', 'Denuncias Vzla') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

<link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/argon-dashboard.css') }}" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
@fluxAppearance