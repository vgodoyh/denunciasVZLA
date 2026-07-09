<x-layouts::admin :title="'Editar Tipo de Denuncia'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('tipo_denuncia.index') }}" class="text-muted text-decoration-none">Tipos de Denuncia</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Editar</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Editar Tipo de Denuncia: {{ $tipo_denuncia->name }}</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('tipo_denuncia.update', $tipo_denuncia->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $tipo_denuncia->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion"
                              id="descripcion"
                              rows="3"
                              class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $tipo_denuncia->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input"
                           type="checkbox"
                           name="activo"
                           id="activo"
                           value="1"
                           {{ old('activo', $tipo_denuncia->activo) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>

                <button type="submit" class="btn btn-dark">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
            </form>
        </div>
    </div>
</x-layouts::admin>