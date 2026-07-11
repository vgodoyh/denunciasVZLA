<?php

namespace App\Console\Commands;

use App\Models\EmisorRedSocial;
use App\Models\NoticiaTemporal;
use App\Models\Noticia;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DenunciasInstagram extends Command
{
    protected $signature = 'denuncias:instagram';

    protected $description = 'Monitorea Instagram y guarda denuncias';

    private string $timezone = 'America/Caracas';

    public function handle()
    {
        $this->info('▶ Iniciando denuncias:instagram');

        $desde = now($this->timezone)->subDay()->startOfDay();
        $hasta = now($this->timezone)->subDay()->endOfDay();

        $this->line("Buscando publicaciones desde {$desde->format('d/m/Y H:i:s')} hasta {$hasta->format('d/m/Y H:i:s')}");

        $norm = function (?string $s): string {
            $s = mb_strtolower(trim((string) $s));

            return strtr($s, [
                'á' => 'a',
                'é' => 'e',
                'í' => 'i',
                'ó' => 'o',
                'ú' => 'u',
                'ü' => 'u',
                'ñ' => 'n',
            ]);
        };

        $canalesInstagram = EmisorRedSocial::with([
                'emisor.tipoemisor',
                'redsocial.palabras_claves',
            ])
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

            $this->line('');
            $this->line("Procesando Instagram: {$username}");

            try {

                $tipo = $norm(optional(optional($canal->emisor)->tipoemisor)->name);
                $esPeriodico = ($tipo === 'periodico');

                if ($esPeriodico) {
                    $this->warn("Omitido: {$username} es periódico");
                    continue;
                }

                $posts = $this->obtenerPublicaciones($username);

                if (empty($posts)) {
                    $this->warn("Sin publicaciones devueltas para {$username}");
                    continue;
                }

                $this->info('Publicaciones recibidas: ' . count($posts));

                foreach ($posts as $post) {
                    $fechaPost = $this->parseFecha(
                        $post['timestamp']
                        ?? $post['date']
                        ?? $post['takenAt']
                        ?? null
                    );

                    $url = $post['url'] ?? $post['permalink'] ?? null;
                    $caption = $post['caption'] ?? $post['text'] ?? '';

                    $this->line('----------------------------------------');
                    $this->line('Fecha: ' . ($fechaPost ? $fechaPost->format('d/m/Y H:i:s') : 'sin fecha'));
                    $this->line('URL: ' . ($url ?: 'sin url'));
                    $this->line('Texto: ' . mb_substr(trim($caption), 0, 120));

                    if (!$fechaPost) {
                        $this->warn('Omitido: publicación sin fecha');
                        continue;
                    }

                    if (!$fechaPost->between($desde, $hasta)) {
                        $this->warn('Omitido: no corresponde al día anterior');
                        continue;
                    }

                    if (!$url) {
                        $this->warn('Omitido: publicación sin URL');
                        continue;
                    }

                    $existeEnTemporal = NoticiaTemporal::withTrashed()
                        ->where('url', $url)
                        ->exists();

                    $existeEnNoticia = Noticia::withTrashed()
                        ->where('url', $url)
                        ->exists();

                    if ($existeEnTemporal || $existeEnNoticia) {
                        $this->warn('Omitido: URL ya existe en noticia_temporal o noticia');
                        continue;
                    }

                    $noticia = NoticiaTemporal::create([
                        'fecha'      => $fechaPost,
                        'plataforma' => 'instagram',
                        'titular'    => $this->generarTitular($caption, $username),
                        'contenido'  => $caption,
                        'autor'      => optional($canal->emisor)->name ?? $username,
                        'estatus'    => 'pendiente',

                        'tipo_emisor_id' => $canal->emisor?->tipoemisor?->id,
                        'emisor_id'      => $canal->emisor?->id,
                        'red_social_id'  => $canal->id,
                    ]);

                    $this->info('Guardado en noticia_temporal');
                }
            } catch (\Throwable $e) {
                Log::error('Error en monitor:instagram', [
                    'canal' => $canal->name,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Error procesando {$canal->name}: {$e->getMessage()}");
            }

            sleep(2);
        }

        $this->info('✔ monitor:instagram finalizado');

        return Command::SUCCESS;
    }

    private function obtenerPublicaciones(string $username): array
    {
        $actor = str_replace('/', '~', config('services.apify.instagram_actor'));

        $response = Http::timeout(180)
            ->post(
                "https://api.apify.com/v2/acts/{$actor}/run-sync-get-dataset-items?token=" . config('services.apify.token'),
                [
                    'directUrls' => [
                        "https://www.instagram.com/{$username}/",
                    ],
                    'resultsType' => 'posts',
                    'resultsLimit' => (int) config('services.apify.instagram_limit', 10),
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