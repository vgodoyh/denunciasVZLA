<x-layouts::admin :title="'Crear Denuncia'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('denuncia.index') }}" class="text-muted text-decoration-none">Denuncias</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Crear</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Crear Denuncia</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('denuncia.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date"
                               name="fecha"
                               id="fecha"
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha') }}">
                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="emisorredsocial_id" class="form-label">Emisor / Canal</label>
                        <select name="emisorredsocial_id" id="emisorredsocial_id" class="form-select @error('emisorredsocial_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($emisoresRedSocial as $red)
                                <option value="{{ $red->id }}" {{ old('emisorredsocial_id') == $red->id ? 'selected' : '' }}>
                                    {{ $red->emisor->name ?? '—' }} — {{ $red->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('emisorredsocial_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="url" class="form-label">URL</label>
                    <input type="text"
                           name="url"
                           id="url"
                           class="form-control @error('url') is-invalid @enderror"
                           value="{{ old('url') }}"
                           placeholder="https://...">
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="titular" class="form-label">Titular</label>
                    <input type="text"
                           name="titular"
                           id="titular"
                           class="form-control @error('titular') is-invalid @enderror"
                           value="{{ old('titular') }}">
                    @error('titular')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="contenido" class="form-label">Contenido</label>
                    <textarea name="contenido"
                              id="contenido"
                              rows="6"
                              class="form-control @error('contenido') is-invalid @enderror">{{ old('contenido') }}</textarea>
                    @error('contenido')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-dark">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>
        </div>
    </div>
</x-layouts::admin>