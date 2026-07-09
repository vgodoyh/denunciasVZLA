<x-layouts::admin :title="'Palabras Claves'">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Palabras Claves</h5>
            <div>
                <a href="{{ route('palabras_clave.papelera') }}" class="btn btn-outline-dark me-2">
                    <i class="fas fa-trash"></i> Papelera
                </a>
                <a href="{{ route('palabras_clave.create') }}" class="btn btn-dark">
                    <i class="fa-solid fa-plus-circle"></i> Crear palabra clave
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-sm text-bold ps-2">ID</th>
                            <th class="text-sm text-bold ps-2">Palabra</th>
                            <th class="text-sm text-bold ps-2">Estado</th>
                            <th class="text-sm text-bold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($palabrasClaves as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->palabra }}</td>
                                <td>
                                    @if ($item->activo)
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-success"
                                              style="background:#EAF3DE; border:1px solid #3B6D11; width:fit-content;">
                                            Activa
                                        </span>
                                    @else
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-muted"
                                              style="background:#F1EFE8; border:1px solid #888780; width:fit-content;">
                                            Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-group" wire:click.stop>

                                        <a href="{{ route('palabras_clave.edit', $item->id) }}"
                                           class="act-btn edit"
                                           data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <form action="{{ route('palabras_clave.destroy', $item->id) }}" method="post" class="form-eliminar d-inline">
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
                                <td colspan="4" class="text-center text-muted">No hay palabras claves registradas.</td>
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