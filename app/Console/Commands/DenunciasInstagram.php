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
    //protected $signature = 'denuncias:instagram {--desde=2026-06-24 : Fecha desde la cual capturar} {--hasta= : Fecha límite superior (opcional)}';
    protected $signature = 'denuncias:instagram
    {--desde=2026-06-24 : Fecha desde la cual capturar}
    {--hasta= : Fecha límite superior opcional}
    {--cuenta= : Procesar únicamente esta cuenta}
    {--max-cuentas= : Cantidad máxima de cuentas a procesar}
    {--offset=0 : Cantidad de cuentas que se omitirán al comenzar}';

    protected $description = 'Monitorea Instagram (vía Apify) y guarda denuncias de acuerdo a palabras claves';

    private string $timezone = 'America/Caracas';

    public function handle()
    {
        $this->info('▶ Iniciando denuncias:instagram');

        $desde = Carbon::parse(
            $this->option('desde'),
            $this->timezone
        )->startOfDay();

        $hasta = $this->option('hasta')
            ? Carbon::parse(
                $this->option('hasta'),
                $this->timezone
            )->endOfDay()
            : null;

        $this->line(
            "Buscando publicaciones desde {$desde->format('d/m/Y H:i:s')}" .
            ($hasta
                ? " hasta {$hasta->format('d/m/Y H:i:s')}"
                : ' en adelante')
        );

        /*
        * Normaliza el texto:
        * minúsculas y sin acentos.
        */
        $norm = function (?string $texto): string {
            $texto = mb_strtolower(
                trim((string) $texto)
            );

            return strtr($texto, [
                'á' => 'a',
                'é' => 'e',
                'í' => 'i',
                'ó' => 'o',
                'ú' => 'u',
                'ü' => 'u',
                'ñ' => 'n',
            ]);
        };

        /*
        * Busca palabras o frases completas.
        */
        $contieneTermino = function (
            string $texto,
            string $termino
        ): bool {
            return preg_match(
                '/(?<![\pL\pN])' .
                preg_quote($termino, '/') .
                '(?![\pL\pN])/u',
                $texto
            ) === 1;
        };

        /*
        * Términos del evento:
        * terremoto, sismo, temblor, etc.
        */
        $terminosEvento = collect(
            config('denuncias.terminos_evento', [])
        )
            ->map(fn ($termino) => $norm($termino))
            ->filter()
            ->values()
            ->toArray();

        /*
        * Términos de ubicación:
        * Venezuela, estados y ciudades.
        */
        $terminosUbicacion = collect(
            config('denuncias.terminos_ubicacion', [])
        )
            ->map(fn ($termino) => $norm($termino))
            ->filter()
            ->values()
            ->toArray();

        /*
        * Palabras clave activas.
        */
        $palabrasClaveMap = PalabrasClaves::where(
            'activo',
            1
        )
            ->get(['id', 'palabra'])
            ->mapWithKeys(
                fn ($palabra) => [
                    $norm($palabra->palabra) => $palabra->id,
                ]
            )
            ->reject(
                fn ($id, $palabra) =>
                    in_array($palabra, $terminosEvento, true)
            )
            ->filter(
                fn ($id, $palabra) => $palabra !== ''
            )
            ->all();

        if (empty($terminosEvento)) {
            $this->warn(
                'No hay términos del evento configurados.'
            );

            return Command::SUCCESS;
        }

        if (empty($terminosUbicacion)) {
            $this->warn(
                'No hay términos de ubicación configurados.'
            );

            return Command::SUCCESS;
        }

        if (empty($palabrasClaveMap)) {
            $this->warn(
                'No hay palabras clave activas, se aborta la corrida.'
            );

            return Command::SUCCESS;
        }

        /*
        * Buscar canales de Instagram.
        */
        $canalesInstagram = EmisorRedSocial::with([
            'emisor.tipoemisor',
            'tipo_red_social',
        ])
            ->whereHas(
                'tipo_red_social',
                function ($query) {
                    $query->whereRaw(
                        'UPPER(name) = ?',
                        ['INSTAGRAM']
                    );
                }
            )
            ->get();

        /*
        * Ejecución por cuenta o por lotes.
        */
        $cuenta = trim(
            (string) $this->option('cuenta')
        );

        $offset = max(
            0,
            (int) $this->option('offset')
        );

        $maxCuentas = $this->option('max-cuentas')
            ? max(
                1,
                (int) $this->option('max-cuentas')
            )
            : null;

        if ($cuenta !== '') {
            $cuenta = $this->normalizarUsuarioInstagram(
                $cuenta
            );

            $canalesInstagram = $canalesInstagram
                ->filter(function ($canal) use ($cuenta) {
                    return $this->normalizarUsuarioInstagram(
                        $canal->name
                    ) === $cuenta;
                })
                ->values();
        } else {
            $canalesInstagram = $canalesInstagram
                ->slice($offset, $maxCuentas)
                ->values();
        }

        if ($canalesInstagram->isEmpty()) {
            $this->warn(
                'No se encontraron cuentas de Instagram para procesar.'
            );

            return Command::SUCCESS;
        }

        $this->info(
            'Cuentas que se procesarán: ' .
            $canalesInstagram->count()
        );

        /*
        * Procesar cuentas.
        */
        foreach ($canalesInstagram as $canal) {
            $username = $this->normalizarUsuarioInstagram(
                $canal->name
            );

            if (!$username) {
                continue;
            }

            $this->line('');
            $this->line(
                "Procesando Instagram: {$username}"
            );

            try {
                $posts = $this->obtenerPublicaciones(
                    $username
                );

                /*
                * Quitar respuestas noResults y elementos
                * que no sean publicaciones.
                */
                $posts = collect($posts)
                    ->filter(function ($post) {
                        return is_array($post)
                            && empty($post['noResults'])
                            && !empty(
                                $post['url']
                                ?? $post['permalink']
                                ?? null
                            );
                    })
                    ->values()
                    ->all();

                if (empty($posts)) {
                    $this->warn(
                        "Sin publicaciones válidas para {$username}"
                    );

                    continue;
                }

                $this->info(
                    'Publicaciones recibidas: ' .
                    count($posts)
                );

                foreach ($posts as $post) {
                    /*
                    * Obtener la fecha según el campo
                    * que devuelva el actor.
                    */
                    $fechaValor =
                        $post['createdAt']
                        ?? $post['created_at']
                        ?? $post['timestamp']
                        ?? $post['takenAt']
                        ?? $post['taken_at']
                        ?? $post['taken_at_timestamp']
                        ?? $post['date']
                        ?? null;

                    if (is_numeric($fechaValor)) {
                        $fechaPost = Carbon::createFromTimestamp(
                            (int) $fechaValor,
                            'UTC'
                        )->timezone($this->timezone);
                    } else {
                        $fechaPost = $this->parseFecha(
                            $fechaValor
                        );
                    }

                    if (!$fechaPost) {
                        $this->warn(
                            'Omitido: publicación sin fecha válida.'
                        );

                        continue;
                    }

                    /*
                    * Validar rango de fechas.
                    */
                    if (
                        $fechaPost->lt($desde) ||
                        ($hasta && $fechaPost->gt($hasta))
                    ) {
                        continue;
                    }

                    /*
                    * Obtener URL.
                    */
                    $url = trim(
                        (string) (
                            $post['url']
                            ?? $post['permalink']
                            ?? ''
                        )
                    );

                    if ($url === '') {
                        $this->warn(
                            'Omitido: publicación sin URL.'
                        );

                        continue;
                    }

                    /*
                    * Obtener y normalizar contenido.
                    */
                    $caption = trim(
                        (string) (
                            $post['caption']
                            ?? $post['text']
                            ?? ''
                        )
                    );

                    $texto = $norm($caption);

                    if ($texto === '') {
                        continue;
                    }

                    /*
                    * 1. Debe mencionar el evento:
                    * terremoto, sismo, temblor, etc.
                    */
                    $tieneTerminoEvento = false;

                    foreach (
                        $terminosEvento as $termino
                    ) {
                        if (
                            $contieneTermino(
                                $texto,
                                $termino
                            )
                        ) {
                            $tieneTerminoEvento = true;
                            break;
                        }
                    }

                    if (!$tieneTerminoEvento) {
                        continue;
                    }

                    /*
                    * 2. Debe tener relación con Venezuela.
                    */
                    $tieneUbicacionVenezuela = false;

                    foreach (
                        $terminosUbicacion as $termino
                    ) {
                        if (
                            $contieneTermino(
                                $texto,
                                $termino
                            )
                        ) {
                            $tieneUbicacionVenezuela = true;
                            break;
                        }
                    }

                    if (!$tieneUbicacionVenezuela) {
                        $this->warn(
                            "Omitido: terremoto sin relación con Venezuela: {$url}"
                        );

                        continue;
                    }

                    /*
                    * 3. Identificar palabras clave generales.
                    */
                    $matchedIds = [];

                    foreach (
                        $palabrasClaveMap as $palabraNorm => $id
                    ) {
                        if (
                            $contieneTermino(
                                $texto,
                                $palabraNorm
                            )
                        ) {
                            $matchedIds[] = $id;
                        }
                    }

                    $matchedIds = array_values(
                        array_unique($matchedIds)
                    );

                    if (empty($matchedIds)) {
                        continue;
                    }

                    /*
                    * Evitar duplicados, incluso eliminados.
                    */
                    $existeEnDenuncia = Denuncia::withTrashed()
                        ->where('url', $url)
                        ->exists();

                    if ($existeEnDenuncia) {
                        $this->warn(
                            "Omitido: URL ya existe, incluso eliminada: {$url}"
                        );

                        continue;
                    }

                    /*
                    * Guardar denuncia.
                    */
                    $denuncia = Denuncia::create([
                        'fecha' => $fechaPost,
                        'url' => $url,
                        'titular' => $this->generarTitular(
                            $caption,
                            $username
                        ),
                        'contenido' => $caption,
                        'estatus' => 'pendiente',
                        'emisor_id' => $canal->emisor?->id,
                        'emisorredsocial_id' => $canal->id,
                    ]);

                    $denuncia
                        ->palabrasClaves()
                        ->attach($matchedIds);

                    $this->info(
                        "Guardado en denuncia: {$url} (" .
                        count($matchedIds) .
                        ' palabra(s) clave)'
                    );
                }
            } catch (\Throwable $e) {
                Log::error(
                    'Error en denuncias:instagram',
                    [
                        'canal' => $canal->name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]
                );

                $this->error(
                    "Error procesando {$canal->name}: " .
                    $e->getMessage()
                );
            }

            sleep(2);
        }

        $this->info(
            '✔ denuncias:instagram finalizado'
        );

        return Command::SUCCESS;
    }

    private function obtenerPublicaciones(string $username): array
    {
        $actorConfigurado = config(
            'services.apify.instagram_actor',
            'apidojo/instagram-scraper'
        );

        $token = config('services.apify.token');

        if (!$token) {
            throw new \RuntimeException('No está configurado APIFY_TOKEN.');
        }

        $actor = str_replace('/', '~', $actorConfigurado);

        $desde = Carbon::parse(
            $this->option('desde'),
            $this->timezone
        )->startOfDay();

        /*
        * API Dojo usa "until" para indicar que solamente debe
        * recuperar publicaciones nuevas desde esta fecha.
        */
        $input = [
            'startUrls' => [
                [
                    'url' => "https://www.instagram.com/{$username}/",
                ],
            ],
            'until' => $desde->format('Y-m-d'),
            'maxItems' => (int) config(
                'services.apify.instagram_limit',
                500
            ),
        ];

        $this->line(
            "Solicitando publicaciones de {$username} desde " .
            $desde->format('d/m/Y')
        );

        $response = Http::timeout(300)
            ->retry(2, 2000)
            ->withQueryParameters([
                'token' => $token,
            ])
            ->post(
                "https://api.apify.com/v2/acts/{$actor}/run-sync-get-dataset-items",
                $input
            );

        if (!$response->successful()) {
            $this->warn(
                "Apify respondió con error {$response->status()}"
            );

            Log::warning('Error consultando Instagram en Apify', [
                'username' => $username,
                'actor' => $actorConfigurado,
                'status' => $response->status(),
                'respuesta' => mb_substr($response->body(), 0, 1000),
            ]);

            return [];
        }

        /*$posts = $response->json();

        return is_array($posts) ? $posts : [];*/
        $posts = $response->json();

        if (!is_array($posts)) {
            return [];
        }

        $posts = collect($posts)
            ->filter(function ($post) {
                return is_array($post)
                    && empty($post['noResults'])
                    && !empty($post['url'])
                    && !empty(
                        $post['createdAt']
                        ?? $post['created_at']
                        ?? $post['timestamp']
                        ?? $post['takenAt']
                        ?? $post['taken_at']
                        ?? null
                    );
            })
            ->values()
            ->all();

        if (empty($posts)) {
            $this->warn(
                'Apify no devolvió publicaciones válidas para esta cuenta.'
            );
        }

        return $posts;
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