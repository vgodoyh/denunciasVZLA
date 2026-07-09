<x-layouts::admin :title="'Papelera - Palabras Claves'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('palabras_clave.index') }}" class="text-muted text-decoration-none">Palabras Claves</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Papelera</span>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Papelera</h5>
            @if ($palabrasClaves->count() > 0)
                <form action="{{ route('palabras_clave.restoreAll') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-trash-restore"></i> Restaurar todas
                    </button>
                </form>
            @endif
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-sm text-bold ps-2">ID</th>
                            <th class="text-sm text-bold ps-2">Palabra</th>
                            <th class="text-sm text-bold ps-2">Eliminada el</th>
                            <th class="text-sm text-bold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($palabrasClaves as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->palabra }}</td>
                                <td>{{ $item->deleted_at->format('d/m/Y H:i:s') }}</td>
                                <td class="text-end">
                                    <div class="action-group" wire:click.stop>
                                        <form action="{{ route('palabras_clave.restore', $item->id) }}" method="post">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="act-btn edit border-0" data-bs-toggle="tooltip" title="Restaurar">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">La papelera está vacía.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (session('restaurar') == 'ok-restaurar' || session('restaurar') == 'ok-restaurar-todo')
        <script src="{{ asset('alerts/ok-modificar-permiso.js') }}"></script>
    @endif
</x-layouts::admin>