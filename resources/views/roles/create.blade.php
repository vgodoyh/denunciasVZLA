<x-layouts::admin :title="'Crear Rol'">
    <div class="mb-2" style="font-size: 0.87rem;">
        <a href="{{ route('role.index') }}" class="text-muted text-decoration-none">Roles</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Crear</span>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('role.store') }}" method="POST">
                @csrf

                <div class="mb-3 col-6">
                    <label for="name" class="form-label">Nombre del rol</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Ej: editor, supervisor">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 col-12">
                    <label class="form-label">Permisos</label>

                    <input type="text"
                        id="buscar-permiso"
                        class="form-control mb-3"
                        placeholder="Buscar permiso...">

                    <div class="row" id="lista-permisos">
                        @forelse ($permisos as $permiso)
                            <div class="col-md-4 mb-2 item-permiso" data-nombre="{{ strtolower($permiso->name) }}">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permiso->id }}"
                                        id="permiso-{{ $permiso->id }}"
                                        {{ in_array($permiso->id, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permiso-{{ $permiso->id }}">
                                        {{ $permiso->name }}
                                    </label>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No hay permisos registrados todavía.</p>
                        @endforelse
                    </div>

                    <p class="text-muted text-sm mt-1 d-none" id="sin-resultados">No se encontraron permisos.</p>
                </div>

                <button type="submit" class="btn btn-dark mt-4">
                    <i class="fas fa-save"></i> Guardar
                </button>

            </form>
        </div>
    </div>
</x-layouts::admin>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador = document.getElementById('buscar-permiso');
    const items = document.querySelectorAll('.item-permiso');
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