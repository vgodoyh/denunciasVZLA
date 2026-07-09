<x-layouts::admin :title="'Detalle del Usuario'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('user.index') }}" class="text-muted text-decoration-none">Usuarios</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Ver detalles</span>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Encabezado --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-gray-800 me-3"
                         style="width:56px; height:56px; border: 2px solid #A32D2D;">
                        <span class="text-light text-bold" style="font-size:1.1rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $user->name }}</h5>
                        <p class="text-sm text-muted mb-0">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="text-end">
                    <p class="text-xs text-muted mb-0 text-uppercase">Fecha de creación</p>
                    <p class="text-bold mb-0">{{ $user->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>

            {{-- Roles --}}
            <div class="pt-3" style="border-top:1px solid #E9ECEF;">
                <p class="text-sm text-bold mb-3">
                    Roles asignados
                    <span class="badge bg-gray-100 text-dark ms-1">{{ $user->roles->count() }}</span>
                </p>

                @forelse ($user->roles as $role)
                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-2 me-2 text-xs font-weight-600 text-info"
                          style="background:#EAF2FB; border:1px solid #2D6DA3; width:fit-content;">
                        <i class="fas fa-check-circle me-1" style="font-size:0.65rem;"></i>
                        {{ $role->name }}
                    </span>
                @empty
                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 text-xs font-weight-600 text-danger"
                          style="background:#FCEBEB; border:1px solid #A32D2D; width:fit-content;">
                        Sin rol asignado
                    </span>
                @endforelse
            </div>

        </div>
    </div>
</x-layouts::admin>