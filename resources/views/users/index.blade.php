<x-layouts::admin :title="'Usuarios'">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Usuarios</h5>
            <a href="{{ route('user.create') }}" class="btn btn-dark">
                <i class="fa-solid fa-plus-circle"></i> Crear usuario
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-sm text-bold ps-2">ID</th>
                            <th class="text-sm text-bold ps-2">Nombre</th>
                            <th class="text-sm text-bold ps-2">Email</th>
                            <th class="text-sm text-bold ps-2">Roles</th>
                            <th class="text-sm text-bold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles->take(3) as $role)
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 me-1 text-xs font-weight-600 text-info"
                                              style="background:#EAF2FB; border:1px solid #2D6DA3; width:fit-content;">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 text-xs font-weight-600 text-danger"
                                              style="background:#FCEBEB; border:1px solid #A32D2D; width:fit-content;">
                                            Sin rol
                                        </span>
                                    @endforelse

                                    @if ($user->roles->count() > 3)
                                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 text-xs font-weight-600 bg-gray-100 text-muted"
                                              style="border:1px solid #dee2e6; width:fit-content;">
                                            +{{ $user->roles->count() - 3 }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-group" wire:click.stop>
                                        <a href="{{ route('user.show', $user->id) }}"
                                           class="act-btn show"
                                           data-bs-toggle="tooltip" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('user.edit', $user->id) }}"
                                           class="act-btn edit"
                                           data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <form action="{{ route('user.destroy', $user->id) }}" method="post" class="form-eliminar d-inline">
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
                                <td colspan="5" class="text-center text-muted">No hay usuarios registrados.</td>
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