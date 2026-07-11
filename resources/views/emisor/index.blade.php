<x-layouts::admin :title="'Emisores'">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Emisores</h5>
            <div>
                <a href="{{ route('emisor.papelera') }}" class="btn btn-outline-dark me-2">
                    <i class="fas fa-trash"></i> Papelera
                </a>
                <a href="{{ route('emisor.create') }}" class="btn btn-dark">
                    <i class="fa-solid fa-plus-circle"></i> Crear emisor
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">                          
                <div class="d-flex align-items-center justify-content-between mb-3 col-6">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="text-xs" style="color: #67748e;">Mostrar</span>
                        <div class="d-inline-flex" style="background: #F1EFE8; padding: 2px; border-radius: 6px; gap: 2px;">
                            @foreach (['10' => '10', '50' => '50', 'all' => 'Todos'] as $val => $label)
                                <a href="{{ route('emisor.index', array_merge(request()->except('page'), ['perPage' => $val])) }}"
                                class="border-0 perpage-pill text-decoration-none {{ (string) $perPage === (string) $val ? 'active' : '' }}"
                                style="padding: 3px 12px; font-size: 11px; font-weight: 600; border-radius: 4px;">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-6 align-items-center">
                    <form action="{{ route('emisor.index') }}" method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text"
                                name="buscar"
                                class="form-control"
                                placeholder="Buscar por nombre..."
                                value="{{ request('buscar') }}">

                            <button type="submit" class="btn btn-dark">
                                <i class="fas fa-search"></i>
                            </button>

                            @if (request('buscar'))
                                <a href="{{ route('emisor.index') }}" class="btn btn-outline-dark">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-sm text-bold ps-2">ID</th>
                            <th class="text-sm text-bold ps-2">Nombre</th>
                            <th class="text-sm text-bold ps-2">Tipo de emisor</th>
                            <th class="text-sm text-bold ps-2">Redes sociales</th>
                            <th class="text-sm text-bold ps-2">Estado</th>
                            <th class="text-sm text-bold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emisores as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->tipoemisor->name ?? '—' }}</td>
                                <td>
                                    @forelse ($item->emisor_red_social->take(3) as $red)
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 me-1 text-xs font-weight-600 text-info"
                                              style="background:#EAF2FB; border:1px solid #2D6DA3; width:fit-content;">
                                            {{ $red->tipo_red_social->name ?? '—' }}
                                        </span>
                                    @empty
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 text-xs font-weight-600 text-danger"
                                              style="background:#FCEBEB; border:1px solid #A32D2D; width:fit-content;">
                                            Sin redes
                                        </span>
                                    @endforelse

                                    @if ($item->emisor_red_social->count() > 3)
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 text-xs font-weight-600 bg-gray-100 text-muted"
                                              style="border:1px solid #dee2e6; width:fit-content;">
                                            +{{ $item->emisor_red_social->count() - 3 }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->activo)
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-success"
                                              style="background:#EAF3DE; border:1px solid #3B6D11; width:fit-content;">
                                            Activo
                                        </span>
                                    @else
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-muted"
                                              style="background:#F1EFE8; border:1px solid #888780; width:fit-content;">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-group" wire:click.stop>
                                        <a href="{{ route('emisor.show', $item->id) }}"
                                           class="act-btn show"
                                           data-bs-toggle="tooltip" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('emisor.edit', $item->id) }}"
                                           class="act-btn edit"
                                           data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <form action="{{ route('emisor.destroy', $item->id) }}" method="post" class="form-eliminar d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="act-btn del border-0"
                                                    data-bs-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay emisores registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($perPage !== 'all')
                <div class="mt-3">
                    {{ $emisores->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    @if (session('guardar') == 'ok-guardar')
        <script src="{{ asset('alerts/ok-guardar-permiso.js') }}"></script>
    @endif
    @if (session('modificar') == 'ok-modificar')
        <script src="{{ asset('alerts/ok-modificar-permiso.js') }}"></script>
    @endif
    @if (session('eliminar') == 'ok-eliminar')
        <script src="{{ asset('alerts/ok-eliminar.js') }}"></script>
    @endif
    <script src="{{ asset('alerts/form-eliminar-permiso.js') }}"></script>
</x-layouts::admin>