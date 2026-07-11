<x-layouts::admin :title="'Clasificar Denuncia'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('denuncia.index') }}" class="text-muted text-decoration-none">Denuncias</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Clasificar</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Clasificar Denuncia: {{ Str::limit($denuncia->titular, 60) }}</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('denuncia.update', $denuncia->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Datos base --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date"
                               name="fecha"
                               id="fecha"
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha', \Carbon\Carbon::parse($denuncia->fecha)->format('Y-m-d')) }}">
                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="emisorredsocial_id" class="form-label">Emisor / Canal</label>
                        <select name="emisorredsocial_id" id="emisorredsocial_id" class="form-control @error('emisorredsocial_id') is-invalid @enderror">
                            @foreach ($emisoresRedSocial as $red)
                                <option value="{{ $red->id }}" {{ old('emisorredsocial_id', $denuncia->emisorredsocial_id) == $red->id ? 'selected' : '' }}>
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
                           value="{{ old('url', $denuncia->url) }}">
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
                           value="{{ old('titular', $denuncia->titular) }}">
                    @error('titular')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="contenido" class="form-label">Contenido</label>
                    <textarea name="contenido"
                              id="contenido"
                              rows="6"
                              class="form-control @error('contenido') is-invalid @enderror">{{ old('contenido', $denuncia->contenido) }}</textarea>
                    @error('contenido')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Clasificación --}}
                <div class="pt-3 mb-3" style="border-top: 1px solid #E9ECEF;">
                    <p class="text-sm text-bold mb-3">Clasificación</p>

                    <div class="mb-3">
                        <label for="estatus" class="form-label">Estatus</label>
                        <select name="estatus" id="estatus" class="form-control @error('estatus') is-invalid @enderror">
                            <option value="pendiente" {{ old('estatus', $denuncia->estatus) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="aceptada" {{ old('estatus', $denuncia->estatus) == 'aceptada' ? 'selected' : '' }}>Aceptada</option>
                            <option value="descartada" {{ old('estatus', $denuncia->estatus) == 'descartada' ? 'selected' : '' }}>Descartada</option>
                        </select>
                        @error('estatus')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea name="observacion"
                                  id="observacion"
                                  rows="2"
                                  class="form-control @error('observacion') is-invalid @enderror"
                                  placeholder="Motivo de descarte o notas de clasificación">{{ old('observacion', $denuncia->observacion) }}</textarea>
                        @error('observacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Bloque que solo aplica si se acepta --}}
                    <div id="bloque-clasificacion" style="{{ old('estatus', $denuncia->estatus) === 'aceptada' ? '' : 'display:none;' }}">

                        <div class="mb-3">
                            <label for="tipo_denuncia_id" class="form-label">Tipo de denuncia</label>
                            <select name="tipo_denuncia_id" id="tipo_denuncia_id" class="form-control @error('tipo_denuncia_id') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                @foreach ($tiposDenuncia as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_denuncia_id', $denuncia->tipo_denuncia_id) == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_denuncia_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estados de Venezuela involucrados</label>
                            <div class="row">
                                @foreach ($estados as $estado)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="estados[]"
                                                   value="{{ $estado->id }}"
                                                   id="estado-{{ $estado->id }}"
                                                   {{ in_array($estado->id, old('estados', $estadosSeleccionados)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="estado-{{ $estado->id }}">
                                                {{ $estado->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Palabras clave asociadas</label>

                            <input type="text" id="buscar-palabra" class="form-control mb-2" placeholder="Buscar palabra clave...">

                            <div class="row" id="lista-palabras">
                                @foreach ($palabrasClaves as $palabra)
                                    <div class="col-md-4 mb-2 item-palabra" data-nombre="{{ strtolower($palabra->palabra) }}">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="palabras_claves[]"
                                                   value="{{ $palabra->id }}"
                                                   id="palabra-{{ $palabra->id }}"
                                                   {{ in_array($palabra->id, old('palabras_claves', $palabrasSeleccionadas)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="palabra-{{ $palabra->id }}">
                                                {{ $palabra->palabra }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar clasificación
                </button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const estatusSelect = document.getElementById('estatus');
        const bloqueClasificacion = document.getElementById('bloque-clasificacion');

        estatusSelect.addEventListener('change', function () {
            bloqueClasificacion.style.display = this.value === 'aceptada' ? 'block' : 'none';
        });

        const buscador = document.getElementById('buscar-palabra');
        const items = document.querySelectorAll('.item-palabra');

        buscador.addEventListener('input', function () {
            const texto = this.value.trim().toLowerCase();
            items.forEach(function (item) {
                item.classList.toggle('d-none', !item.dataset.nombre.includes(texto));
            });
        });
    });
    </script>
</x-layouts::admin>