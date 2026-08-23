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

                $estadisticas = [
                    'procesadas' => 0,
                    'sin_fecha' => 0,
                    'fuera_rango' => 0,
                    'sin_url' => 0,
                    'sin_contenido' => 0,
                    'sin_evento' => 0,
                    'sin_ubicacion' => 0,
                    'sin_palabras_clave' => 0,
                    'duplicadas' => 0,
                    'guardadas' => 0,
                ];

                $fechaMasAntigua = null;
                $fechaMasNueva = null;

                foreach ($posts as $post) {
                    $estadisticas['procesadas']++;

                    /*
                    * Obtener la fecha según el campo
                    * que devuelva el actor de Apify.
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
                        $timestamp = (int) $fechaValor;

                        /*
                        * Algunos actores devuelven el timestamp
                        * en milisegundos.
                        */
                        if ($timestamp > 9999999999) {
                            $timestamp = (int) floor($timestamp / 1000);
                        }

                        try {
                            $fechaPost = Carbon::createFromTimestamp(
                                $timestamp,
                                'UTC'
                            )->timezone($this->timezone);
                        } catch (\Throwable) {
                            $fechaPost = null;
                        }
                    } else {
                        $fechaPost = $this->parseFecha(
                            $fechaValor
                        );
                    }

                    if (!$fechaPost) {
                        $estadisticas['sin_fecha']++;
                        continue;
                    }

                    /*
                    * Registrar el rango real recibido desde Apify.
                    */
                    if (
                        !$fechaMasAntigua ||
                        $fechaPost->lt($fechaMasAntigua)
                    ) {
                        $fechaMasAntigua = $fechaPost->copy();
                    }

                    if (
                        !$fechaMasNueva ||
                        $fechaPost->gt($fechaMasNueva)
                    ) {
                        $fechaMasNueva = $fechaPost->copy();
                    }

                    /*
                    * Validar el rango solicitado.
                    */
                    if (
                        $fechaPost->lt($desde) ||
                        ($hasta && $fechaPost->gt($hasta))
                    ) {
                        $estadisticas['fuera_rango']++;
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
                        $estadisticas['sin_url']++;
                        continue;
                    }

                    /*
                    * Obtener el contenido de la publicación.
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
                        $estadisticas['sin_contenido']++;
                        continue;
                    }

                    /*
                    * 1. Debe mencionar el evento:
                    * terremoto, sismo, temblor, réplica o epicentro.
                    */
                    $tieneTerminoEvento = false;

                    foreach ($terminosEvento as $termino) {
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
                        $estadisticas['sin_evento']++;
                        continue;
                    }

                    /*
                    * 2. Debe tener relación con Venezuela.
                    */
                    $tieneUbicacionVenezuela = false;

                    foreach ($terminosUbicacion as $termino) {
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
                        $estadisticas['sin_ubicacion']++;

                        $this->warn(
                            "Omitido: terremoto sin relación con Venezuela: {$url}"
                        );

                        continue;
                    }

                    /*
                    * 3. Identificar las palabras clave generales.
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
                        $estadisticas['sin_palabras_clave']++;
                        continue;
                    }

                    /*
                    * Evitar duplicados, incluso si fueron eliminados.
                    */
                    $existeEnDenuncia = Denuncia::withTrashed()
                        ->where('url', $url)
                        ->exists();

                    if ($existeEnDenuncia) {
                        $estadisticas['duplicadas']++;

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

                    $estadisticas['guardadas']++;

                    $this->info(
                        "Guardado en denuncia: {$url} (" .
                        count($matchedIds) .
                        ' palabra(s) clave)'
                    );
                }

                /*
                * Mostrar el rango recibido desde Apify.
                */
                $this->newLine();

                if ($fechaMasAntigua && $fechaMasNueva) {
                    $this->line(
                        'Rango recibido: ' .
                        $fechaMasAntigua->format('d/m/Y H:i:s') .
                        ' → ' .
                        $fechaMasNueva->format('d/m/Y H:i:s')
                    );
                }

                /*
                * Mostrar resumen de la cuenta.
                */
                $this->info("Resumen de {$username}:");
                $this->line(
                    "  Procesadas: {$estadisticas['procesadas']}"
                );
                $this->line(
                    "  Sin fecha: {$estadisticas['sin_fecha']}"
                );
                $this->line(
                    "  Fuera del rango: {$estadisticas['fuera_rango']}"
                );
                $this->line(
                    "  Sin URL: {$estadisticas['sin_url']}"
                );
                $this->line(
                    "  Sin contenido: {$estadisticas['sin_contenido']}"
                );
                $this->line(
                    "  Sin término del evento: {$estadisticas['sin_evento']}"
                );
                $this->line(
                    "  Sin relación con Venezuela: {$estadisticas['sin_ubicacion']}"
                );
                $this->line(
                    "  Sin palabras clave: {$estadisticas['sin_palabras_clave']}"
                );
                $this->line(
                    "  Duplicadas: {$estadisticas['duplicadas']}"
                );
                $this->line(
                    "  Guardadas: {$estadisticas['guardadas']}"
                );

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
                1000
            ),
        ];

        $this->line(
            "Solicitando publicaciones de {$username} desde " .
            $desde->format('d/m/Y')
        );

        $response = Http::timeout(310)
            ->withQueryParameters([
                'token' => $token,
                'maxTotalChargeUsd' => 1.10,
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