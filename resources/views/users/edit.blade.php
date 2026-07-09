<x-layouts::admin :title="'Editar Usuario'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('user.index') }}" class="text-muted text-decoration-none">Usuarios</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Editar</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Editar Usuario: {{ $user->name }}</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('user.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Nueva contraseña (opcional)</label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Dejar en blanco para no cambiar">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Roles</label>

                    <input type="text"
                           id="buscar-rol"
                           class="form-control mb-3"
                           placeholder="Buscar rol...">

                    <div class="row" id="lista-roles">
                        @forelse ($roles as $role)
                            <div class="col-md-4 mb-2 item-rol" data-nombre="{{ strtolower($role->name) }}">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="roles[]"
                                           value="{{ $role->id }}"
                                           id="rol-{{ $role->id }}"
                                           {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rol-{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No hay roles registrados todavía.</p>
                        @endforelse
                    </div>

                    <p class="text-muted text-sm mt-1 d-none" id="sin-resultados">No se encontraron roles.</p>
                </div>

                <button type="submit" class="btn btn-dark mt-4">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buscador = document.getElementById('buscar-rol');
        const items = document.querySelectorAll('.item-rol');
        const sinResultados = document.getElementById('sin-resultados');

        buscador.addEventListener('input', function () {
            const texto = this.value.trim().toLowerCase();
            let visibles = 0;

            items.forEach(function (item) {
                const coincide = item.dataset.nombre.includes(texto);
                item.classList.toggle('d-none', !coincide);
                if (coincide) visibles++;
            });

            sinResultados.classList.toggle('d-none', visibles > 0);
        });
    });
    </script>
</x-layouts::admin>