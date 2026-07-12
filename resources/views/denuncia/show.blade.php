<x-layouts::admin :title="'Detalle de Denuncia'">
    <div class="mb-2" style="font-size: 0.875rem;">
        <a href="{{ route('denuncia.index') }}" class="text-muted text-decoration-none">Denuncias</a>
        <i class="fas fa-chevron-right mx-1" style="font-size: 0.65rem;"></i>
        <span class="text-dark fw-bold">Ver detalles</span>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-gray-800 me-3"
                         style="width:56px; height:56px; border: 2px solid #A32D2D;flex-shrink: 0;">
                        <i class="fas fa-flag text-light" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ Str::limit($denuncia->titular, 70) }}</h5>
                        <p class="text-sm text-muted mb-0">
                            {{ $denuncia->emisor_red_social->emisor->name ?? '—' }}
                            — {{ \Carbon\Carbon::parse($denuncia->fecha)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                <div class="text-end">
                    @if ($denuncia->estatus === 'aceptada')
                        <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-xs font-weight-600 text-success"
                              style="background:#EAF3DE; border:1px solid #3B6D11; width:fit-content;">
                            Aceptada
                        </span>
                    @elseif ($denuncia->estatus === 'descartada')
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
                </div>
            </div>

            <div class="mb-4">
                <p class="text-xs text-muted mb-1 text-uppercase">URL</p>
                <a href="{{ $denuncia->url }}" target="_blank" class="text-sm">{{ $denuncia->url }}</a>
            </div>

            <div class="mb-4">
                <p class="text-xs text-muted mb-1 text-uppercase">Contenido</p>
                <p class="text-sm">{{ $denuncia->contenido }}</p>
            </div>

            @if ($denuncia->observacion)
                <div class="mb-4">
                    <p class="text-xs text-muted mb-1 text-uppercase">Observación</p>
                    <p class="text-sm">{{ $denuncia->observacion }}</p>
                </div>
            @endif

            <div class="pt-3" style="border-top:1px solid #E9ECEF;">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-xs text-muted mb-1 text-uppercase">Tipo de denuncia</p>
                        <p class="text-bold mb-0">{{ $denuncia->tipoDenuncia->name ?? 'Sin clasificar' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-xs text-muted mb-1 text-uppercase">Clasificado por</p>
                        <p class="text-bold mb-0">{{ $denuncia->user->name ?? '—' }}</p>
                    </div>
                </div>

                <p class="text-sm text-bold mb-2">Estados involucrados</p>
                @forelse ($denuncia->estados as $estado)
                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-2 me-2 text-xs font-weight-600 text-info"
                          style="background:#EAF2FB; border:1px solid #2D6DA3; width:fit-content;">
                        {{ $estado->name }}
                    </span>
                @empty
                    <p class="text-muted text-sm mb-3">Sin estados asignados.</p>
                @endforelse

                <p class="text-sm text-bold mb-2 mt-3">Palabras clave</p>
                @forelse ($denuncia->palabrasClaves as $palabra)
                    <span class="d-inline-flex align-items-center px-2 py-1 rounded-pill mb-2 me-2 text-xs font-weight-600 bg-gray-100 text-dark"
                          style="border:1px solid #dee2e6; width:fit-content;">
                        {{ $palabra->palabra }}
                    </span>
                @empty
                    <p class="text-muted text-sm">Sin palabras clave asignadas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts::admin>