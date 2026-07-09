<x-layouts::admin :title="'Crear Tipo de Emisor'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('tipo_emisor.index') }}" class="text-muted text-decoration-none">Tipos de Emisor</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Crear</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Crear Tipo de Emisor</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('tipo_emisor.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Ej: Institución pública, Medio de comunicación">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input"
                           type="checkbox"
                           name="activo"
                           id="activo"
                           value="1"
                           {{ old('activo', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>

                <button type="submit" class="btn btn-dark">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>
        </div>
    </div>
</x-layouts::admin>