<x-layouts::admin :title="'Editar Palabra Clave'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('palabras_clave.index') }}" class="text-muted text-decoration-none">Palabras Claves</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Editar</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Editar Palabra Clave: {{ $palabraClave->palabra }}</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('palabras_clave.update', $palabraClave->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="palabra" class="form-label">Palabra</label>
                    <input type="text"
                           name="palabra"
                           id="palabra"
                           class="form-control @error('palabra') is-invalid @enderror"
                           value="{{ old('palabra', $palabraClave->palabra) }}">
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
                           {{ old('activo', $palabraClave->activo) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Activa</label>
                </div>

                <button type="submit" class="btn btn-dark mt-4">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
            </form>
        </div>
    </div>
</x-layouts::admin>