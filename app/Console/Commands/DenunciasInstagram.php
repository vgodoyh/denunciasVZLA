<?php

namespace App\Console\Commands;

use App\Models\Denuncia;
use App\Models\EmisorRedSocial;
use App\Models\PalabrasClaves;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DenunciasInstagram extends Command
{
    protected $signature = 'denuncias:instagram {--desde=2026-06-24 : Fecha desde la cual capturar} {--hasta= : Fecha límite superior (opcional)}';

    protected $description = 'Monitorea Instagram (vía Apify) y guarda denuncias de acuerdo a palabras claves';

    private string $timezone = 'America/Caracas';

    public function handle()
    {
        $this->info('▶ Iniciando denuncias:instagram');

        $desde = Carbon::parse($this->option('desde'), $this->timezone)->startOfDay();
        $hasta = $this->option('hasta')
            ? Carbon::parse($this->option('hasta'), $this->timezone)->endOfDay()
            : null;

        $this->line("Buscando publicaciones desde {$desde->format('d/m/Y H:i:s')}" . ($hasta ? " hasta {$hasta->format('d/m/Y H:i:s')}" : ' en adelante'));

        $norm = function (?string $s): string {
            $s = mb_strtolower(trim((string) $s));
            return strtr($s, [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            ]);
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
         * Mismo patrón que denuncias:web, para poder guardar en la pivot
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
            $this->warn('No hay palabras clave activas, se aborta la corrida.');
            return Command::SUCCESS;
        }

        $canalesInstagram = EmisorRedSocial::with(['emisor.tipoemisor', 'tipo_red_social'])
            ->whereHas('tipo_red_social', function ($q) {
                $q->whereRaw('UPPER(name) = ?', ['INSTAGRAM']);
            })
            ->get();

        if ($canalesInstagram->isEmpty()) {
            $this->warn('No hay canales Instagram configurados');
            return Command::SUCCESS;
        }

        foreach ($canalesInstagram as $canal) {
            $username = $this->normalizarUsuarioInstagram($canal->name);

            if (!$username) {
                continue;
            }

            $this->line('');
            $this->line("Procesando Instagram: {$username}");

            try {
                $posts = $this->obtenerPublicaciones($username);

                if (empty($posts)) {
                    $this->warn("Sin publicaciones devueltas para {$username}");
                    continue;
                }

                $this->info('Publicaciones recibidas: ' . count($posts));

                foreach ($posts as $post) {
                    $fechaPost = $this->parseFecha(
                        $post['timestamp'] ?? $post['date'] ?? $post['takenAt'] ?? null
                    );

                    if (!$fechaPost) {
                        $this->warn('Omitido: publicación sin fecha');
                        continue;
                    }

                    if ($fechaPost->lt($desde) || ($hasta && $fechaPost->gt($hasta))) {
                        $this->warn('Omitido: fuera del rango de fechas');
                        continue;
                    }

                    $url = $post['url'] ?? $post['permalink'] ?? null;

                    if (!$url) {
                        $this->warn('Omitido: publicación sin URL');
                        continue;
                    }

                    $caption = $post['caption'] ?? $post['text'] ?? '';
                    $texto = $norm($caption);

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

                    $existeEnDenuncia = Denuncia::withTrashed()
                        ->where('url', $url)
                        ->exists();

                    if ($existeEnDenuncia) {
                        $this->warn("Omitido: URL ya existe, incluso eliminada: {$url}");
                        continue;
                    }

                    $denuncia = Denuncia::create([
                        'fecha'              => $fechaPost,
                        'url'                => $url,
                        'titular'            => $this->generarTitular($caption, $username),
                        'contenido'          => $caption,
                        'estatus'            => 'pendiente',
                        'emisor_id'          => $canal->emisor?->id,
                        'emisorredsocial_id' => $canal->id,
                    ]);

                    $denuncia->palabrasClaves()->attach($matchedIds);

                    $this->info("Guardado en denuncia: {$url} (" . count($matchedIds) . " palabra(s) clave)");
                }
            } catch (\Throwable $e) {
                Log::error('Error en denuncias:instagram', [
                    'canal' => $canal->name,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Error procesando {$canal->name}: {$e->getMessage()}");
            }

            sleep(2);
        }

        $this->info('✔ denuncias:instagram finalizado');

        return Command::SUCCESS;
    }

    private function obtenerPublicaciones(string $username): array
    {
        $actor = str_replace('/', '~', config('services.apify.instagram_actor'));

        $response = Http::timeout(180)
            ->post(
                "https://api.apify.com/v2/acts/{$actor}/run-sync-get-dataset-items?token=" . config('services.apify.token'),
                [
                    'directUrls' => ["https://www.instagram.com/{$username}/"],
                    'resultsType' => 'posts',
                    'resultsLimit' => (int) config('services.apify.instagram_limit', 10),
                    'onlyPostsNewerThan' => $this->option('desde'),
                ]
            );

        if (!$response->successful()) {
            $this->warn("Apify respondió con error {$response->status()}");
            $this->line($response->body());
            return [];
        }

        return $response->json() ?? [];
    }

    private function normalizarUsuarioInstagram(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (str_starts_with($valor, '@')) {
            return ltrim($valor, '@');
        }

        if (str_contains($valor, 'instagram.com')) {
            $path = parse_url($valor, PHP_URL_PATH);
            return trim($path, '/');
        }

        return $valor;
    }

    private function parseFecha(?string $fecha): ?Carbon
    {
        if (!$fecha) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->timezone($this->timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    private function generarTitular(string $caption, string $username): string
    {
        $texto = trim(strip_tags($caption));

        if ($texto === '') {
            return 'Publicación de Instagram de ' . $username;
        }

        return mb_substr($texto, 0, 120);
    }
}