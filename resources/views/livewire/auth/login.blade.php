<x-layouts::auth :title="__('Login')">
    <div class="d-flex flex-column gap-3 mb-0 mt-0">
        <div class="text-center mb-1">
            <h1 class="h3 mb-0">{{ __('Inicia sesión') }}</h1>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success text-center" role="alert">
                {{ session('status') }}
            </div>
        @endif

        {{-- <x-passkey-verify /> --}}

        <form method="POST" action="{{ route('login.store') }}" class="d-flex flex-column gap-3">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="form-label">{{ __('E-mail') }}</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="position-relative">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label">{{ __('Contraseña') }}</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate class="text-xs text-decoration-underline">
                            {{ __('¿Olvido su contraseña?') }}
                        </a>
                    @endif
                </div>
                <div class="input-group">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autocomplete="current-password"
                        placeholder="{{ __('Password') }}"
                    >
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-check">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    class="form-check-input"
                    {{ old('remember') ? 'checked' : '' }}
                >
                <label for="remember" class="form-check-label mt-0">
                    {{ __('Recordar') }}
                </label>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-dark w-100" data-test="login-button">
                    {{ __('Login') }}
                </button>
            </div>
        </form>

    </div>
</x-layouts::auth>