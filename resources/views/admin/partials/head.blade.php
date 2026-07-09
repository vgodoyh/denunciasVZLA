<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>{{ $title ?? 'Denuncias Vzla' }}</title>

<link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">

<link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/argon-dashboard.css') }}" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

@vite(['resources/css/admin.css', 'resources/js/app.js'])
@livewireStyles