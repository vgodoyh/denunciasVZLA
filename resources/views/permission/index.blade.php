<x-layouts::admin :title="'Permisos'">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Permisos</h5>
            <a href="{{ route('permission.create') }}" class="btn btn-dark">
                <i class="fa-solid fa-plus-circle"></i> Crear permiso
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-sm text-bold ps-2">ID</th>
                            <th class="text-sm text-bold ps-2">Nombre del permiso</th>
                            <th class="text-sm text-bold ps-2">Fecha Creación</th>
                            <th class="text-sm text-bold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permission as $permiso)
                            <tr>
                                <td>{{ $permiso->id }}</td>
                                <td>{{ $permiso->name }}</td>
                                <td>{{ date('d/m/Y (h:i:s)', strtotime($permiso->created_at)) }}</td>
                                {{-- Acciones agrupadas --}}
                                <td class="text-end">
                                    <div class="action-group" wire:click.stop>
                       
                                            <a href="{{ route('permission.edit', $permiso->id) }}"
                                            class="act-btn edit"
                                            data-bs-toggle="tooltip" title="Editar">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                      
                                            <form action="{{ route('permission.destroy', $permiso->id) }}" method="post" class="form-eliminar d-inline">
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
                                <td colspan="5" class="text-center text-muted">No hay permisos registrados.</td>
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
    @if (session('eliminar') == 'no-eliminar')
        <script src="{{ asset('alerts/no-eliminar-permiso.js') }}"></script>
    @endif
    <script src="{{ asset('alerts/form-eliminar-permiso.js') }}"></script>
</x-layouts::admin>