<x-layouts::admin :title="'Denuncias'">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Denuncias</h5>
            <a href="{{ route('denuncia.create') }}" class="btn btn-dark">
                <i class="fa-solid fa-plus-circle"></i> Crear denuncia
            </a>
        </div>

        <div class="card-body">

            <form action="{{ route('denuncia.index') }}" method="GET" class="d-flex mb-3" style="gap: 8px;">
                <input type="hidden" name="perPage" value="{{ $perPage }}">
                <input type="hidden" name="estatus" value="{{ $estatus }}">

                <div class="input-group">
                    <input type="text"
                           name="buscar"
                           class="form-control"
                           placeholder="Buscar por titular o URL..."
                           value="{{ request('buscar') }}">
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                @if (request('buscar'))
                    <a href="{{ route('denuncia.index', ['perPage' => $perPage, 'estatus' => $estatus]) }}" class="btn btn-outline-dark">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>

            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap: 12px;">

                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="text-xs" style="color: #67748e;">Mostrar</span>
                    <div class="d-inline-flex" style="background: #F1EFE8; padding: 2px; border-radius: 6px; gap: 2px;">
                        @foreach (['10' => '10', '50' => '50', 'all' => 'Todos'] as $val => $label)
                            <a href="{{ route('denuncia.index', array_merge(request()->except('page'), ['perPage' => $val])) }}"
                               class="border-0 perpage-pill text-decoration-none {{ (string) $perPage === (string) $val ? 'active' : '' }}"
                               style="padding: 3px 12px; font-size: 11px; font-weight: 600; border-radius: 4px;">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex align-items-center" style="gap: 8px;">
                    <div class="d-inline-flex" style="background: #F1EFE8; padding: 2px; border-radius: 6px; gap: 2px;">
                        @foreach (['pendiente' => 'Pendientes', 'aceptada' => 'Aceptadas', 'descartada' => 'Descartadas', 'todos' => 'Todos'] as $val => $label)
                            <a href="{{ route('denuncia.index', array_merge(request()->except(['page', 'estatus']), ['estatus' => $val])) }}"
                               class="border-0 perpage-pill text-decoration-none {{ $estatus === $val ? 'active' : '' }}"
                               style="padding: 3px 12px; font-size: 11px; font-weight: 600; border-radius: 4px;">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Vista tabla: desktop --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-sm text-bold ps-2">ID</th>
                            <th class="text-sm text-bold ps-2">Fecha</th>
                            <th class="text-sm text-bold ps-2">Titular</th>
                            <th class="text-sm text-bold ps-2">Emisor</th>
                            <th class="text-sm text-bold ps-2">Estatus</th>
                            <th class="text-sm text-bold ps-2">Tipo</th>
                            <th class="text-sm text-bold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($denuncias as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($item->titular, 60) }}</td>
                                <td>{{ $item->emisor_red_social->emisor->name ?? '—' }}</td>
                                <td>
                                    @if ($item->estatus === 'aceptada')
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-success"
                                              style="background:#EAF3DE; border:1px solid #3B6D11; width:fit-content;">
                                            Aceptada
                                        </span>
                                    @elseif ($item->estatus === 'descartada')
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-danger"
                                              style="background:#FCEBEB; border:1px solid #A32D2D; width:fit-content;">
                                            Descartada
                                        </span>
                                    @else
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-muted"
                                              style="background:#F1EFE8; border:1px solid #888780; width:fit-content;">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $item->tipoDenuncia->name ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="action-group" wire:click.stop>
                                        <a href="{{ route('denuncia.show', $item->id) }}"
                                           class="act-btn show"
                                           data-bs-toggle="tooltip" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('denuncia.edit', $item->id) }}"
                                           class="act-btn edit"
                                           data-bs-toggle="tooltip" title="Editar / Clasificar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <form action="{{ route('denuncia.destroy', $item->id) }}" method="post" class="form-eliminar d-inline">
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
                                <td colspan="7" class="text-center text-muted">No hay denuncias registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Vista cards: mobile --}}
            <div class="d-md-none d-flex flex-column" style="gap: 10px;">
                @forelse ($denuncias as $item)
                    <div class="card border shadow-none">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-xs fw-bold" style="color:#67748e;">#{{ $item->id }}</span>
                                <span class="text-xs text-muted">{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }}</span>
                            </div>

                            <p class="mb-2 fw-bold text-sm">{{ \Illuminate\Support\Str::limit($item->titular, 80) }}</p>

                            <div class="d-flex justify-content-between align-items-center">
                                @if ($item->estatus === 'aceptada')
                                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-success"
                                          style="background:#EAF3DE; border:1px solid #3B6D11; width:fit-content;">
                                        Aceptada
                                    </span>
                                @elseif ($item->estatus === 'descartada')
                                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-danger"
                                          style="background:#FCEBEB; border:1px solid #A32D2D; width:fit-content;">
                                        Descartada
                                    </span>
                                @else
                                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-muted"
                                          style="background:#F1EFE8; border:1px solid #888780; width:fit-content;">
                                        Pendiente
                                    </span>
                                @endif

                                <div class="d-flex" style="gap: 14px;" wire:click.stop>
                                    <a href="{{ route('denuncia.show', $item->id) }}" class="act-btn show" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('denuncia.edit', $item->id) }}" class="act-btn edit" title="Editar / Clasificar">
                                        <i class="fas fa-pencil-alt text-success"></i>
                                    </a>
                                    <form action="{{ route('denuncia.destroy', $item->id) }}" method="post" class="form-eliminar d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="act-btn del"
                                                style="background: transparent; border: none; padding: 0; margin: 0; font: inherit; line-height: 1;"
                                                title="Eliminar">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        No hay denuncias registradas.
                    </div>
                @endforelse
            </div>
            @if ($perPage !== 'all')
                <div class="mt-3">
                    {{ $denuncias->links('pagination::bootstrap-5') }}
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