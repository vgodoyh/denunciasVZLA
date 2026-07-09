<x-layouts::admin :title="'Detalle del Rol'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('role.index') }}" class="text-muted text-decoration-none">Roles</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Detalles rol</span>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Encabezado --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-gray-800 me-3"
                         style="width:56px; height:56px;">
                        <i class="fas fa-user-shield" style="font-size:1.3rem;color:#f5365c ;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $role->name }}</h5>
                        <p class="text-sm text-muted mb-0">Rol #{{ $role->id }}</p>
                    </div>
                </div>

                <div class="text-end">
                    <p class="text-xs text-muted mb-0 text-uppercase">Fecha de creación</p>
                    <p class="text-sm text-bold mb-0">{{ $role->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>

            {{-- Permisos --}}
            <div class="pt-3" style="border-top:1px solid #E9ECEF;">
                <p class="text-sm text-bold mb-3">
                    Permisos asignados
                    <span class="badge bg-gray-100 text-dark ms-1">{{ $role->permissions->count() }}</span>
                </p>

                @forelse ($role->permissions as $permiso)
                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-2 me-2 text-xs font-weight-600 text-info"
                          style="background:#EAF2FB; border:1px solid #2D6DA3; width:fit-content;">
                        <i class="fas fa-check-circle me-1" style="font-size:0.65rem;"></i>
                        {{ $permiso->name }}
                    </span>
                @empty
                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-1 text-xs font-weight-600 text-danger"
                          style="background:#FCEBEB; border:1px solid #A32D2D; width:fit-content;">
                        Sin permisos
                    </span>
                @endforelse
            </div>

        </div>
    </div>
</x-layouts::admin>