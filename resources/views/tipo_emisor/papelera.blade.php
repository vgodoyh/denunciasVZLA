<x-layouts::admin :title="'Papelera - Tipos de Emisor'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('tipo_emisor.index') }}" class="text-muted text-decoration-none">Tipos de Emisor</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Papelera</span>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Papelera</h5>
            @if ($tiposEmisor->count() > 0)
                <form action="{{ route('tipo_emisor.restoreAll') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-trash-restore"></i> Restaurar todos
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
                            <th class="text-sm text-bold ps-2">Nombre</th>
                            <th class="text-sm text-bold ps-2">Eliminado el</th>
                            <th class="text-sm text-bold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tiposEmisor as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->deleted_at->format('d/m/Y H:i:s') }}</td>
                                <td class="text-end">
                                    <form action="{{ route('tipo_emisor.restore', $item->id) }}" method="post">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="act-btn edit border-0" data-bs-toggle="tooltip" title="Restaurar">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
                                    </form>
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