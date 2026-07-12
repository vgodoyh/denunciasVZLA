<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\EmisorRedSocial;
use App\Models\PalabrasClaves;
use App\Models\Denuncia;

use App\Services\Web\RssReaderService;
use App\Services\Web\WebHtmlScraperService;

use App\Helpers\WebFeedHelper;

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
         * Términos del evento (terremoto) — deben aparecer SIEMPRE
         * (no viven en la tabla palabras_claves, solo en config)
         * ===================================================== */
        $terminosEvento = collect(config('denuncias.terminos_evento'))
            ->map(fn ($p) => $norm($p))
            ->filter()
            ->values()
            ->toArray();

        /* =====================================================
         * Palabras clave GLOBALES (activas) — mapa: palabra normalizada => id
         * Así podemos guardar en la pivot cuáles matchearon exactamente
         * ===================================================== */
        $palabrasClaveMap = PalabrasClaves::where('activo', 1)
            ->get(['id', 'palabra'])
            ->mapWithKeys(fn ($p) => [$norm($p->palabra) => $p->id])
            ->reject(fn ($id, $palabra) => in_array($palabra, $terminosEvento))
            ->filter(fn ($id, $palabra) => $palabra !== '')
            ->all();

        if (empty($terminosEvento)) {
            $this->warn('No hay términos de evento configurados en config/denuncias.php, se aborta la corrida.');
            return Command::SUCCESS;
        }

        if (empty($palabrasClaveMap)) {
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
                $feedPath = WebFeedHelper::feedPath($canal->name);

                $items = $rss->leer('https://' . $feedPath);

                if (empty($items)) {
                    $this->warn('HTTPS no respondió, probando HTTP');
                    $items = $rss->leer('http://' . $feedPath);
                }

                if (empty($items)) {
                    $this->warn('RSS no disponible, usando HTML fallback');

                    $baseUrl = preg_replace('#/feed$#i', '', 'https://' . $feedPath);
                    $items = $htmlScraper->scrape($baseUrl);
                }

                if (empty($items)) {
                    $this->warn("Sin resultados para {$canal->name}");
                    continue;
                }

                foreach ($items as $item) {

                    if (!is_array($item) || empty($item['url'])) {
                        continue;
                    }

                    $item = array_merge([
                        'titulo' => null,
                        'contenido' => '',
                        'fecha' => Carbon::now(),
                    ], $item);

                    try {
                        $fecha = Carbon::parse($item['fecha']);
                    } catch (\Throwable $e) {
                        $fecha = Carbon::now();
                    }

                    if ($fecha->lt($fechaDesde) || ($fechaHasta && $fecha->gt($fechaHasta))) {
                        continue;
                    }

                    $texto = $norm(
                        ($item['titulo'] ?? '') . ' ' . ($item['contenido'] ?? '')
                    );

                    /* ---------------------------------------------
                     * 1) ¿Menciona el terremoto en sí? (gate, no se guarda)
                     * --------------------------------------------- */
                    $tieneTerminoEvento = false;
                    foreach ($terminosEvento as $termino) {
                        if (str_contains($texto, $termino)) {
                            $tieneTerminoEvento = true;
                            break;
                        }
                    }

                    if (!$tieneTerminoEvento) {
                        continue;
                    }

                    /* ---------------------------------------------
                     * 2) ¿Qué palabras clave generales matchearon?
                     *    (esto sí se guarda, en la pivot)
                     * --------------------------------------------- */
                    $matchedIds = [];
                    foreach ($palabrasClaveMap as $palabraNorm => $id) {
                        if (str_contains($texto, $palabraNorm)) {
                            $matchedIds[] = $id;
                        }
                    }

                    if (empty($matchedIds)) {
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

                    $denuncia = Denuncia::create([
                        'fecha'              => $fecha,
                        'url'                => $url,
                        'titular'            => $item['titulo'] ?: null,
                        'contenido'          => $item['contenido'] ?: ($item['titulo'] ?? ''),
                        'estatus'            => 'pendiente',
                        'emisor_id'          => $canal->emisor?->id,
                        'emisorredsocial_id' => $canal->id,
                    ]);

                    $denuncia->palabrasClaves()->attach($matchedIds);

                    $this->info("Guardado en denuncia: {$url} (" . count($matchedIds) . " palabra(s) clave)");
                }

            } catch (\Throwable $e) {

                Log::error('Error en denuncias:web', [
                    'canal' => $canal->name,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Error procesando {$canal->name}");
            }

            sleep(2);
        }

        $this->info('✔ denuncias:web finalizado');

        return Command::SUCCESS;
    }
}