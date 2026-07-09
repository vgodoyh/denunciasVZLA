<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-dark auth-login-page">
        <div class="d-flex min-vh-100 flex-column align-items-center justify-content-center gap-4 p-3 p-md-5">
            <div class="d-flex w-100 flex-column gap-2" style="max-width: 380px;">
                <div class="d-flex flex-column align-items-center gap-2 mb-2">
                    <span class="d-flex mb-1 align-items-center justify-content-center rounded">
                        <x-app-logo-icon style="height:12rem; width:auto;" />
                    </span>
                    <span class="visually-hidden">{{ config('app.name', 'Denuncias Vzla') }}</span>
                </div>
                <div class="d-flex flex-column gap-4">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>