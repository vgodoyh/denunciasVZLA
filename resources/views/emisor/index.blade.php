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
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-sm text-bold ps-2">ID</th>
                            <th class="text-sm text-bold ps-2">Nombre</th>
                            <th class="text-sm text-bold ps-2">Tipo de emisor</th>
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
                                <td colspan="5" class="text-center text-muted">No hay emisores registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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