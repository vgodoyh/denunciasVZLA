<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\EmisorRedSocial;
use App\Models\PalabraClave;
use App\Models\Denuncia;

use App\Services\Web\RssReaderService;
use App\Services\Web\WebHtmlScraperService;

use App\Helpers\WebFeedHelper;
use App\Helpers\KeywordMatcher;
use App\Models\PalabrasClaves;

class DenunciasWeb extends Command
{
    protected $signature = 'denuncias:web {--desde=2026-06-24 : Fecha desde la cual capturar} {--hasta= : Fecha límite superior (opcional, por defecto sin tope)}';
    protected $description = 'Monitorea páginas web (RSS + HTML fallback) y guarda denuncias de acuerdo a palabras claves';

    public function handle(
        RssReaderService $rss,
        WebHtmlScraperService $htmlScraper
    ) {
        $this->info('▶ Iniciando denuncias:web');

        $fechaDesde = Carbon::parse($this->option('desde'))->startOfDay();
        $fechaHasta = $this->option('hasta') ? Carbon::parse($this->option('hasta'))->endOfDay() : null;

        // Helper local para normalizar texto (sin tildes, minúsculas)
        $norm = function (?string $s): string {
            $s = mb_strtolower(trim((string) $s));
            $map = [
                'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n'
            ];
            return strtr($s, $map);
        };

        /* =====================================================
         * Palabras clave GLOBALES (activas), aplican a todos los canales
         * ===================================================== */
        $palabrasClave = PalabrasClaves::where('activo', 1)
            ->pluck('palabra')
            ->map(fn ($p) => $norm($p))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($palabrasClave)) {
            $this->warn('No hay palabras clave activas, se aborta la corrida (no hay criterio de filtrado).');
            return Command::SUCCESS;
        }

        // Canales WEB
        $canalesWeb = EmisorRedSocial::with(['tipo_red_social', 'emisor'])
            ->whereHas('tipo_red_social', function ($q) {
                $q->where('name', 'Web');
            })
            ->get();

        if ($canalesWeb->isEmpty()) {
            $this->warn('No hay canales web configurados');
            return Command::SUCCESS;
        }

        foreach ($canalesWeb as $canal) {

            $this->line("Procesando: {$canal->name}");

            try {
                /* =====================================================
                 * Construir feed (sin esquema)
                 * ===================================================== */
                $feedPath = WebFeedHelper::feedPath($canal->name);

                /* =====================================================
                 * RSS: HTTPS → HTTP
                 * ===================================================== */
                $items = $rss->leer('https://' . $feedPath);

                if (empty($items)) {
                    $this->warn('HTTPS no respondió, probando HTTP');
                    $items = $rss->leer('http://' . $feedPath);
                }

                /* =====================================================
                 * HTML fallback
                 * ===================================================== */
                if (empty($items)) {
                    $this->warn('RSS no disponible, usando HTML fallback');

                    $baseUrl = preg_replace('#/feed$#i', '', 'https://' . $feedPath);
                    $items = $htmlScraper->scrape($baseUrl);
                }

                if (empty($items)) {
                    $this->warn("Sin resultados para {$canal->name}");
                    continue;
                }

                /* =====================================================
                 * Procesar ítems
                 * ===================================================== */
                foreach ($items as $item) {

                    if (!is_array($item) || empty($item['url'])) {
                        continue;
                    }

                    // Normalizar estructura
                    $item = array_merge([
                        'titulo' => null,
                        'contenido' => '',
                        'fecha' => Carbon::now(),
                    ], $item);

                    /* ---------------------------------------------
                     * Normalizar fecha
                     * --------------------------------------------- */
                    try {
                        $fecha = Carbon::parse($item['fecha']);
                    } catch (\Throwable $e) {
                        $fecha = Carbon::now();
                    }

                    /* ---------------------------------------------
                    * FILTRO: RANGO DE FECHAS DEL EVENTO
                    * --------------------------------------------- */
                    if ($fecha->lt($fechaDesde) || ($fechaHasta && $fecha->gt($fechaHasta))) {
                        continue;
                    }

                    /* ---------------------------------------------
                     * FILTRO POR PALABRAS CLAVE (global, todos los canales)
                     * --------------------------------------------- */
                    $texto = $norm(
                        ($item['titulo'] ?? '') . ' ' . ($item['contenido'] ?? '')
                    );

                    if (!KeywordMatcher::matches($texto, $palabrasClave)) {
                        continue;
                    }

                    $url = trim($item['url']);

                    $existeEnDenuncia = Denuncia::withTrashed()
                        ->where('url', $url)
                        ->exists();

                    if ($existeEnDenuncia) {
                        $this->warn("Omitido: URL ya existe, incluso eliminada: {$url}");
                        continue;
                    }

                    Denuncia::create([
                        'fecha'              => $fecha,
                        'url'                => $url,
                        'titular'            => $item['titulo'] ?: null,
                        'contenido'          => $item['contenido'] ?: ($item['titulo'] ?? ''),
                        'estatus'            => 'pendiente',
                        'emisor_id'          => $canal->emisor?->id,
                        'emisorredsocial_id' => $canal->id,
                    ]);

                    $this->info("Guardado en denuncia: {$url}");
                }

            } catch (\Throwable $e) {

                Log::error('Error en denuncias:web', [
                    'canal' => $canal->name,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Error procesando {$canal->name}");
            }

            // ⏱️ Pausa de cortesía
            sleep(2);
        }

        $this->info('✔ denuncias:web finalizado');

        return Command::SUCCESS;
    }
}