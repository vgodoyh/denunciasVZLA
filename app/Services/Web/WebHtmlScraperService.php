<?php

namespace App\Services\Web;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebHtmlScraperService
{
    /**
     * Scraping HTML genérico como fallback cuando no hay RSS
     * Devuelve SOLO posibles noticias, nunca assets.
     */
    public function scrape(string $baseUrl, int $limit = 10): array
    {
        try {
            // Normalizar URL base
            $baseUrl = trim($baseUrl);
            if (!preg_match('#^https?://#i', $baseUrl)) {
                $baseUrl = 'https://' . $baseUrl;
            }

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept-Language' => 'es-ES,es;q=0.9',
            ])
                ->timeout(25)
                ->get($baseUrl);

            if (!$response->ok()) {
                Log::warning('HTML scrape: respuesta no OK', [
                    'url' => $baseUrl,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $html = $response->body();
            if (!$html || trim($html) === '') {
                return [];
            }

            return $this->parseHtml($html, $baseUrl, $limit);

        } catch (\Throwable $e) {
            Log::error('HTML scrape: excepción', [
                'url' => $baseUrl,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Parser HTML ultra defensivo
     */
    private function parseHtml(string $html, string $baseUrl, int $limit): array
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $items = [];
        $seenUrls = [];

        // Buscamos TODOS los links, pero filtramos fuerte
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            if (count($items) >= $limit) {
                break;
            }

            $href = trim($link->getAttribute('href'));
            $text = trim($link->textContent);

            if ($href === '') {
                continue;
            }

            // Ignorar anchors y JS
            if (
                str_starts_with($href, '#') ||
                str_starts_with($href, 'javascript:')
            ) {
                continue;
            }

            // Ignorar assets (imagenes, pdfs, etc.)
            if (preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|rar)$#i', $href)) {
                continue;
            }

            // Ignorar wp-content (assets de WordPress)
            if (str_contains($href, '/wp-content/')) {
                continue;
            }

            // Normalizar URL absoluta
            if (!preg_match('#^https?://#i', $href)) {
                $href = rtrim($baseUrl, '/') . '/' . ltrim($href, '/');
            }

            // Evitar duplicados
            if (isset($seenUrls[$href])) {
                continue;
            }
            $seenUrls[$href] = true;

            // Heurística mínima: debe parecer artículo
            if (!preg_match('#/(20\d{2}|noticia|news|articulo|publicacion)#i', $href)) {
                continue;
            }

            // Título mínimo
            if (mb_strlen($text) < 15) {
                continue;
            }

            $items[] = [
                'url' => $href,
                'titulo' => $text,
                // Nunca null
                'contenido' => $text,
                'fecha' => Carbon::now(),
            ];
        }

        return $items;
    }
}