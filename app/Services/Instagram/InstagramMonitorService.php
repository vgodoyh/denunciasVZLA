<?php

namespace App\Services\Instagram;

use App\Models\EmisorRedSocial;
use App\Models\NoticiaTemporal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramMonitorService
{
    private string $timezone = 'America/Caracas';

    public function ejecutar(): void
    {
        $desde = now($this->timezone)->subDay()->startOfDay();
        $hasta = now($this->timezone)->subDay()->endOfDay();

        /*$canalesInstagram = EmisorRedSocial::with(['emisor.tipoemisor', 'redsocial'])
                            ->whereHas('redsocial', function ($q) {
                                $q->whereRaw('UPPER(name) = ?', ['INSTAGRAM']);
                            })
                            ->get();*/

        $canalesInstagram = EmisorRedSocial::with(['emisor.tipoemisor', 'redsocial'])
            ->whereHas('redsocial', function ($q) {
                $q->whereRaw('UPPER(name) = ?', ['INSTAGRAM']);
            })
            ->where('name', 'mineduuniversitaria_ve')
            ->get();

        foreach ($canalesInstagram as $canal) {
            try {
                $this->procesarCanal($canal, $desde, $hasta);
            } catch (\Throwable $e) {
                Log::error('Error en monitor:instagram', [
                    'canal_id' => $canal->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function procesarCanal(EmisorRedSocial $canal, Carbon $desde, Carbon $hasta): void
    {
        $username = $this->normalizarUsuarioInstagram($canal->name ?? null);

        if (!$username) {
            return;
        }

        $posts = $this->obtenerPublicaciones($username);

        foreach ($posts as $post) {
            $fechaPost = $this->parseFecha($post['timestamp'] ?? $post['date'] ?? null);

            if (!$fechaPost || !$fechaPost->between($desde, $hasta)) {
                continue;
            }

            $url = $post['url'] ?? $post['permalink'] ?? null;

            if (!$url) {
                continue;
            }

            $caption = $post['caption'] ?? $post['text'] ?? '';

            NoticiaTemporal::updateOrCreate(
                ['url' => $url],
                [
                    'fecha'          => $fechaPost,
                    'plataforma'     => 'instagram',
                    'titular'        => $this->generarTitular($caption, $username),
                    'contenido'      => $caption,
                    'autor'          => $username,
                    'estatus'        => 'pendiente',
                    'tipo_emisor_id' => $canal->emisor?->tipoemisor_id,
                    'emisor_id'      => $canal->emisor_id,
                    'red_social_id'  => $canal->red_social_id,
                ]
            );
        }
    }

    private function obtenerPublicaciones(string $username): array
    {
        $actor = str_replace('/', '~', config('services.apify.instagram_actor'));

        $response = Http::timeout(180)
            ->post("https://api.apify.com/v2/acts/{$actor}/run-sync-get-dataset-items?token=" . config('services.apify.token'), [
                'directUrls' => [
                    "https://www.instagram.com/{$username}/",
                ],
                'resultsType' => 'posts',
                'resultsLimit' => (int) config('services.apify.instagram_limit', 5),
            ]);

        if (!$response->successful()) {
            Log::warning('Apify Instagram error', [
                'username' => $username,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        return $response->json() ?? [];
    }

    private function normalizarUsuarioInstagram(?string $valor): ?string
    {
        if (!$valor) {
            return null;
        }

        $valor = trim($valor);

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