<x-layouts::admin :title="'Detalle del Emisor'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('emisor.index') }}" class="text-muted text-decoration-none">Emisores</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Ver detalles</span>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-gray-800 me-3"
                         style="width:56px; height:56px; border: 2px solid #A32D2D;">
                        <i class="fas fa-bullhorn text-light" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $emisor->name }}</h5>
                        <p class="text-sm text-muted mb-0">{{ $emisor->tipoemisor->name ?? '—' }}</p>
                    </div>
                </div>

                <div class="text-end">
                    <p class="text-xs text-muted mb-0 text-uppercase">Fecha de creación</p>
                    <p class="text-bold mb-0">{{ $emisor->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>

            <div class="pt-3" style="border-top:1px solid #E9ECEF;">
                <p class="text-sm text-bold mb-3">
                    Redes sociales
                    <span class="badge bg-gray-100 text-dark ms-1">{{ $emisor->emisor_red_social->count() }}</span>
                </p>

                @forelse ($emisor->emisor_red_social as $red)
                    <div class="d-flex align-items-center mb-2">
                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill me-2 text-xs font-weight-600 text-info"
                              style="background:#EAF2FB; border:1px solid #2D6DA3; width:fit-content;">
                            {{ $red->tipo_red_social->name ?? '—' }}
                        </span>
                        <span class="text-sm">{{ $red->name }}</span>
                    </div>
                @empty
                    <p class="text-muted">Este emisor no tiene redes sociales registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts::admin>