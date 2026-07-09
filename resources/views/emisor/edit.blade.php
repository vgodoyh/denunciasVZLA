<x-layouts::admin :title="'Editar Emisor'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('emisor.index') }}" class="text-muted text-decoration-none">Emisores</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Editar</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Editar Emisor: {{ $emisor->name }}</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('emisor.update', $emisor->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $emisor->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tipoemisor_id" class="form-label">Tipo de emisor</label>
                        <select name="tipoemisor_id" id="tipoemisor_id" class="form-select @error('tipoemisor_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach ($tiposEmisor as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('tipoemisor_id', $emisor->tipoemisor_id) == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipoemisor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input"
                           type="checkbox"
                           name="activo"
                           id="activo"
                           value="1"
                           {{ old('activo', $emisor->activo) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Redes sociales</label>
                        <button type="button" class="btn btn-outline-dark btn-sm" id="agregar-red">
                            <i class="fas fa-plus"></i> Agregar red
                        </button>
                    </div>

                    <div id="redes-container">
                        @foreach ($emisor->emisor_red_social as $i => $red)
                            <div class="row align-items-start red-row mb-2">
                                <div class="col-md-4">
                                    <select name="redes[{{ $i }}][tiporedsocial_id]" class="form-select">
                                        <option value="">Red social...</option>
                                        @foreach ($tiposRedSocial as $tipoRed)
                                            <option value="{{ $tipoRed->id }}" {{ $red->tiporedsocial_id == $tipoRed->id ? 'selected' : '' }}>
                                                {{ $tipoRed->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" name="redes[{{ $i }}][name]" class="form-control" value="{{ $red->name }}" placeholder="Usuario o URL">
                                </div>
                                <div class="col-md-1 d-flex align-items-center justify-content-center">
                                    <button type="button"
                                            class="d-flex align-items-center mt-1 justify-content-center quitar-red border-0"
                                            style="width:32px; height:32px; border-radius:50%; background:#FCEBEB; color:#A32D2D;"
                                            data-bs-toggle="tooltip" title="Quitar red">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template id="red-template">
                        <div class="row align-items-start red-row mb-2">
                            <div class="col-md-4">
                                <select name="redes[__INDEX__][tiporedsocial_id]" class="form-select">
                                    <option value="">Red social...</option>
                                    @foreach ($tiposRedSocial as $tipoRed)
                                        <option value="{{ $tipoRed->id }}">{{ $tipoRed->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="redes[__INDEX__][name]" class="form-control" placeholder="Usuario o URL">
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <button type="button"
                                        class="d-flex align-items-center mt-1 justify-content-center quitar-red border-0"
                                        style="width:32px; height:32px; border-radius:50%; background:#FCEBEB; color:#A32D2D;"
                                        data-bs-toggle="tooltip" title="Quitar red">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="submit" class="btn btn-dark">
                    <i class="fa-solid fa-floppy-disk"></i> Actualizar
                </button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('redes-container');
        const template = document.getElementById('red-template');
        const btnAgregar = document.getElementById('agregar-red');
        let indice = {{ $emisor->emisor_red_social->count() }};

        btnAgregar.addEventListener('click', function () {
            const html = template.innerHTML.replaceAll('__INDEX__', indice);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            container.appendChild(wrapper.firstElementChild);
            indice++;
        });

        container.addEventListener('click', function (e) {
            if (e.target.closest('.quitar-red')) {
                e.target.closest('.red-row').remove();
            }
        });
    });
    </script>
</x-layouts::admin>