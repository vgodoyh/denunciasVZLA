<x-layouts::admin :title="'Crear Palabra Clave'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('palabras_clave.index') }}" class="text-muted text-decoration-none">Palabras Claves</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Crear</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Crear Palabra Clave</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('palabras_clave.store') }}" method="POST">
                @csrf

                <div class="mb-3 col-6">
                    <label for="palabra" class="form-label">Palabra</label>
                    <input type="text"
                           name="palabra"
                           id="palabra"
                           class="form-control @error('palabra') is-invalid @enderror"
                           value="{{ old('palabra') }}"
                           placeholder="Ej: corrupción, soborno, fraude">
                    @error('palabra')
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
                    <label class="form-check-label" for="activo">Activa</label>
                </div>

                <button type="submit" class="btn btn-dark mt-4">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>
        </div>
    </div>
</x-layouts::admin>