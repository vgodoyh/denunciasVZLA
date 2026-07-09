<x-layouts::admin :title="'Editar Permiso'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('permission.index') }}" class="text-muted text-decoration-none">Permisos</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Editar</span>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Editar Permiso</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('permission.update', $permission->id) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre del permiso</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="guard_name" class="form-label">Guard</label>
                            <select name="guard_name" id="guard_name" class="form-control @error('guard_name') is-invalid @enderror">
                                <option value="web" {{ old('guard_name', $permission->guard_name) == 'web' ? 'selected' : '' }}>web</option>
                                <option value="api" {{ old('guard_name', $permission->guard_name) == 'api' ? 'selected' : '' }}>api</option>
                            </select>
                            @error('guard_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark mt-4">
                    <i class="fas fa-save"></i> Actualizar
                </button>
            </form>
        </div>
    </div>
</x-layouts::admin>