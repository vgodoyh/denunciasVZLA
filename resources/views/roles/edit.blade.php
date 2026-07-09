<x-layouts::admin :title="'Editar Rol'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('permission.index') }}" class="text-muted text-decoration-none">Roles</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Editar</span>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('role.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre del rol</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $role->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Permisos</label>

                    <div class="row">
                        @forelse ($permisos as $permiso)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="permissions[]"
                                           value="{{ $permiso->id }}"
                                           id="permiso-{{ $permiso->id }}"
                                           {{ in_array($permiso->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permiso-{{ $permiso->id }}">
                                        {{ $permiso->name }}
                                    </label>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No hay permisos registrados todavía.</p>
                        @endforelse
                    </div>
                </div>

                <button type="submit" class="btn btn-dark mt-4">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
            </form>
        </div>
    </div>
</x-layouts::admin>