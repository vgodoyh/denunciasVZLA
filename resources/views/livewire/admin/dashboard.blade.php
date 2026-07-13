<div>
    <div class="mb-4 d-flex align-items-center" style="gap: 12px;">
        <div>
            <h4 class="mb-0">Resumen general</h4>
            <p class="text-sm text-muted mb-0">Monitoreo de denuncias ciudadanas tras el sismo del 24 de junio de 2026 en Venezuela</p>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-6 col-md-3 mb-2">
            <a href="{{ route('denuncia.index') }}" class="cursor-pointer text-decoration-none">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column flex-md-row align-items-center align-items-md-center text-center text-md-start">
                        <div class="d-flex align-items-center justify-content-center rounded mb-2 mb-md-0 me-md-3"
                            style="width:40px; height:40px; background:#F1EFE8;">
                            <i class="fas fa-hourglass-half" style="font-size:1.1rem; color:#888780;"></i>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Pendientes</p>
                            <p class="text-bold mb-0" style="font-size: 1.5rem; color:#888780;">{{ $pendientes }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3 mb-2">
            <div class="card h-100">
                <div class="card-body d-flex flex-column flex-md-row align-items-center align-items-md-center text-center text-md-start">
                    <div class="d-flex align-items-center justify-content-center rounded mb-2 mb-md-0 me-md-3"
                        style="width:40px; height:40px; background:#EAF3DE;">
                        <i class="fas fa-check-circle" style="font-size:1.1rem; color:#3B6D11;"></i>
                    </div>
                    <div>
                        <p class="text-sm text-muted mb-1">Aceptadas</p>
                        <p class="text-bold mb-0" style="font-size: 1.5rem; color:#3B6D11;">{{ $aceptadas }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3 mb-2">
            <div class="card h-100">
                <div class="card-body d-flex flex-column flex-md-row align-items-center align-items-md-center text-center text-md-start">
                    <div class="d-flex align-items-center justify-content-center rounded mb-2 mb-md-0 me-md-3"
                        style="width:40px; height:40px; background:#FCEBEB;">
                        <i class="fas fa-times-circle" style="font-size:1.1rem; color:#A32D2D;"></i>
                    </div>
                    <div>
                        <p class="text-sm text-muted mb-1">Descartadas</p>
                        <p class="text-bold mb-0" style="font-size: 1.5rem; color:#A32D2D;">{{ $descartadas }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3 mb-2">
            <div class="card h-100">
                <div class="card-body d-flex flex-column flex-md-row align-items-center align-items-md-center text-center text-md-start">
                    <div class="d-flex align-items-center justify-content-center rounded mb-2 mb-md-0 me-md-3"
                        style="width:40px; height:40px; background:#EAF2FB;">
                        <i class="fas fa-bullhorn" style="font-size:1.1rem; color:#2D6DA3;"></i>
                    </div>
                    <div>
                        <p class="text-sm text-muted mb-1">Emisores activos</p>
                        <p class="text-bold mb-0" style="font-size: 1.5rem; color:#2D6DA3;">{{ $emisoresActivos }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle me-2"
                            style="width:32px; height:32px; background:#EAF2FB;">
                            <i class="fas fa-chart-pie" style="font-size:0.85rem; color:#2D6DA3;"></i>
                        </div>
                        <p class="text-sm text-bold mb-0">Denuncias aceptadas por tipo</p>
                    </div>

                    @if ($porTipo->isEmpty())
                        <div class="d-flex flex-column align-items-center justify-content-center text-center" style="height:220px;">
                            <i class="fas fa-chart-pie mb-2" style="font-size:1.8rem; color:#D3D1C7;"></i>
                            <p class="text-muted text-sm mb-0">Aún no hay denuncias aceptadas clasificadas.</p>
                        </div>
                    @else
                        <div class="d-flex flex-wrap mb-2" id="leyenda-tipo" style="gap: 16px; font-size: 12px;"></div>
                        <div style="position: relative; height: 220px;">
                            <canvas id="chartTipo" role="img" aria-label="Dona de denuncias aceptadas por tipo de denuncia"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle me-2"
                            style="width:32px; height:32px; background:#EAF3DE;">
                            <i class="fas fa-map-marker-alt" style="font-size:0.85rem; color:#3B6D11;"></i>
                        </div>
                        <p class="text-sm text-bold mb-0">Top 5 estados con más denuncias</p>
                    </div>

                    @if ($porEstado->isEmpty())
                        <div class="d-flex flex-column align-items-center justify-content-center text-center" style="height:220px;">
                            <i class="fas fa-map-marker-alt mb-2" style="font-size:1.8rem; color:#D3D1C7;"></i>
                            <p class="text-muted text-sm mb-0">Aún no hay estados asignados a denuncias.</p>
                        </div>
                    @else
                        <div style="position: relative; height: 220px;">
                            <canvas id="chartEstado" role="img" aria-label="Barras horizontales de denuncias por estado"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle me-2"
                            style="width:32px; height:32px; background:#F1EFE8;">
                            <i class="fas fa-chart-line" style="font-size:0.85rem; color:#888780;"></i>
                        </div>
                        <p class="text-sm text-bold mb-0">Denuncias ingresadas por semana</p>
                    </div>

                    @if ($tendencia->isEmpty())
                        <div class="d-flex flex-column align-items-center justify-content-center text-center" style="height:200px;">
                            <i class="fas fa-chart-line mb-2" style="font-size:1.8rem; color:#D3D1C7;"></i>
                            <p class="text-muted text-sm mb-0">Aún no hay denuncias registradas.</p>
                        </div>
                    @else
                        <div style="position: relative; height: 200px;">
                            <canvas id="chartTendencia" role="img" aria-label="Línea de tendencia de denuncias ingresadas por semana"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @script
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>
        function renderCharts() {
            const colores = ['#2a78d6', '#1baf7a', '#eda100', '#4a3aa7', '#e34948', '#e87ba4', '#eb6834'];

            ['chartTipo', 'chartEstado', 'chartTendencia'].forEach(function (id) {
                const existente = Chart.getChart(id);
                if (existente) existente.destroy();
            });

            const tipoLabels = @json($porTipo->pluck('name'));
            const tipoData = @json($porTipo->pluck('denuncia_count'));
            const tipoTotal = tipoData.reduce((a, b) => a + b, 0);
            const leyendaTipo = document.getElementById('leyenda-tipo');
            leyendaTipo.innerHTML = '';

            tipoLabels.forEach(function (label, i) {
                const porcentaje = tipoTotal > 0 ? Math.round((tipoData[i] / tipoTotal) * 100) : 0;
                const item = document.createElement('span');
                item.style.display = 'flex';
                item.style.alignItems = 'center';
                item.style.gap = '4px';
                item.style.color = '#67748e';
                item.innerHTML = `<span style="width:10px; height:10px; border-radius:2px; background:${colores[i % colores.length]};"></span>${label} ${porcentaje}%`;
                leyendaTipo.appendChild(item);
            });

            if (tipoLabels.length > 0) {
                new Chart(document.getElementById('chartTipo'), {
                    type: 'doughnut',
                    data: {
                        labels: tipoLabels,
                        datasets: [{ data: tipoData, backgroundColor: colores, borderColor: '#fff', borderWidth: 2 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false } } }
                });
            }

            const estadoLabels = @json($porEstado->pluck('name'));
            const estadoData = @json($porEstado->pluck('denuncias_count'));

            if (estadoLabels.length > 0) {
                new Chart(document.getElementById('chartEstado'), {
                    type: 'bar',
                    data: { labels: estadoLabels, datasets: [{ data: estadoData, backgroundColor: '#1baf7a', borderRadius: 4, maxBarThickness: 20 }] },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, grid: { color: '#e1e0d9' } }, y: { grid: { display: false } } }
                    }
                });
            }

            const tendenciaLabels = @json($tendencia->pluck('semana'));
            const tendenciaData = @json($tendencia->pluck('total'));

            if (tendenciaLabels.length > 0) {
                new Chart(document.getElementById('chartTendencia'), {
                    type: 'line',
                    data: {
                        labels: tendenciaLabels,
                        datasets: [{ data: tendenciaData, borderColor: '#4a3aa7', backgroundColor: 'rgba(74,58,167,0.1)', fill: true, tension: 0.3, pointRadius: 3 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, grid: { color: '#e1e0d9' } }, x: { grid: { display: false } } }
                    }
                });
            }
        }

        renderCharts();
    </script>
    @endscript
</div>