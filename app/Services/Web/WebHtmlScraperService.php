<?php

namespace App\Services\Web;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebHtmlScraperService
{
    /**
     * Recorre páginas WordPress:
     * /
     * /page/2/
     * /page/3/
     */
    public function scrape(
        string $baseUrl,
        int $maxPaginas = 10,
        int $limitePorPagina = 30
    ): array {
        $baseUrl = $this->normalizarBaseUrl($baseUrl);

        $items = [];
        $urlsVistas = [];

        for ($pagina = 1; $pagina <= $maxPaginas; $pagina++) {
            $urlPagina = $pagina === 1
                ? $baseUrl
                : rtrim($baseUrl, '/') . '/page/' . $pagina . '/';

            try {
                Log::info('HTML scrape: procesando página', [
                    'pagina' => $pagina,
                    'url' => $urlPagina,
                ]);

                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131 Safari/537.36',
                    'Accept-Language' => 'es-ES,es;q=0.9',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                    ->timeout(25)
                    ->retry(2, 1000)
                    ->get($urlPagina);

                if (!$response->ok()) {
                    Log::warning('HTML scrape: respuesta no OK', [
                        'url' => $urlPagina,
                        'status' => $response->status(),
                    ]);

                    break;
                }

                $html = trim($response->body());

                if ($html === '') {
                    break;
                }

                $itemsPagina = $this->parseHtml(
                    $html,
                    $urlPagina,
                    $limitePorPagina
                );

                if (empty($itemsPagina)) {
                    Log::info('HTML scrape: página sin noticias', [
                        'pagina' => $pagina,
                        'url' => $urlPagina,
                    ]);

                    break;
                }

                $nuevosEnPagina = 0;

                foreach ($itemsPagina as $item) {
                    $url = $this->normalizarUrlParaComparar($item['url']);

                    if (isset($urlsVistas[$url])) {
                        continue;
                    }

                    $urlsVistas[$url] = true;
                    $items[] = $item;
                    $nuevosEnPagina++;
                }

                /*
                 * Si una página contiene únicamente noticias ya vistas,
                 * probablemente el sitio redirigió a la primera página.
                 */
                if ($nuevosEnPagina === 0) {
                    break;
                }

                sleep(1);

            } catch (\Throwable $e) {
                Log::error('HTML scrape: excepción', [
                    'pagina' => $pagina,
                    'url' => $urlPagina,
                    'error' => $e->getMessage(),
                ]);

                break;
            }
        }

        return $items;
    }

    private function parseHtml(
        string $html,
        string $urlPagina,
        int $limite
    ): array {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_NOERROR | LIBXML_NOWARNING
        );

        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $items = [];
        $seenUrls = [];

        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            if (count($items) >= $limite) {
                break;
            }

            $href = trim($link->getAttribute('href'));
            $titulo = trim(preg_replace('/\s+/u', ' ', $link->textContent));

            if ($href === '') {
                continue;
            }

            if (
                str_starts_with($href, '#') ||
                str_starts_with(strtolower($href), 'javascript:') ||
                str_starts_with(strtolower($href), 'mailto:') ||
                str_starts_with(strtolower($href), 'tel:')
            ) {
                continue;
            }

            if (preg_match(
                '#\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|rar)(\?.*)?$#i',
                $href
            )) {
                continue;
            }

            if (str_contains($href, '/wp-content/')) {
                continue;
            }

            $href = $this->resolverUrlAbsoluta($href, $urlPagina);

            if (!$href) {
                continue;
            }

            $hrefComparacion = $this->normalizarUrlParaComparar($href);

            if (isset($seenUrls[$hrefComparacion])) {
                continue;
            }

            if (!$this->mismaWeb($href, $urlPagina)) {
                continue;
            }

            /*
             * Heurística para detectar artículos.
             */
            if (!preg_match(
                '#/(20\d{2}|noticia|noticias|news|articulo|publicacion|actualidad|venezuela)/#i',
                $href
            )) {
                continue;
            }

            if (mb_strlen($titulo) < 15) {
                continue;
            }

            $contenedor = $xpath->query(
                'ancestor::article[1] | ancestor::*[contains(concat(" ", normalize-space(@class), " "), " post ")][1]',
                $link
            )->item(0);

            $contenido = $titulo;
            $fecha = null;

            if ($contenedor) {
                $textoContenedor = trim(
                    preg_replace('/\s+/u', ' ', $contenedor->textContent)
                );

                if (mb_strlen($textoContenedor) > mb_strlen($titulo)) {
                    $contenido = $textoContenedor;
                }

                $fechaNode = $xpath->query(
                    './/time[@datetime]',
                    $contenedor
                )->item(0);

                if ($fechaNode) {
                    $fecha = $this->convertirFecha(
                        $fechaNode->getAttribute('datetime')
                    );
                }
            }

            /*
             * Si no encontramos <time>, intentamos sacar la fecha de URL:
             * /2026/08/20/nombre-de-la-noticia/
             */
            if (!$fecha) {
                $fecha = $this->fechaDesdeUrl($href);
            }

            $seenUrls[$hrefComparacion] = true;

            $items[] = [
                'url' => $href,
                'titulo' => $titulo,
                'contenido' => $contenido,
                'fecha' => $fecha,
            ];
        }

        return $items;
    }

    private function normalizarBaseUrl(string $url): string
    {
        $url = trim($url);

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return rtrim($url, '/');
    }

    private function resolverUrlAbsoluta(
        string $href,
        string $urlPagina
    ): ?string {
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }

        if (str_starts_with($href, '//')) {
            $scheme = parse_url($urlPagina, PHP_URL_SCHEME) ?: 'https';

            return $scheme . ':' . $href;
        }

        $scheme = parse_url($urlPagina, PHP_URL_SCHEME);
        $host = parse_url($urlPagina, PHP_URL_HOST);
        $port = parse_url($urlPagina, PHP_URL_PORT);

        if (!$scheme || !$host) {
            return null;
        }

        $origen = $scheme . '://' . $host;

        if ($port) {
            $origen .= ':' . $port;
        }

        if (str_starts_with($href, '/')) {
            return $origen . $href;
        }

        $path = parse_url($urlPagina, PHP_URL_PATH) ?: '/';
        $directorio = preg_replace('#/[^/]*$#', '/', $path);

        return $origen . rtrim($directorio, '/') . '/' . ltrim($href, '/');
    }

    private function mismaWeb(string $url, string $baseUrl): bool
    {
        $hostUrl = preg_replace(
            '/^www\./i',
            '',
            (string) parse_url($url, PHP_URL_HOST)
        );

        $hostBase = preg_replace(
            '/^www\./i',
            '',
            (string) parse_url($baseUrl, PHP_URL_HOST)
        );

        return $hostUrl !== '' && $hostUrl === $hostBase;
    }

    private function normalizarUrlParaComparar(string $url): string
    {
        return rtrim(strtok(trim($url), '?'), '/');
    }

    private function fechaDesdeUrl(string $url): ?string
    {
        if (preg_match(
            '#/(20\d{2})/([01]?\d)/([0-3]?\d)(?:/|$)#',
            $url,
            $matches
        )) {
            try {
                return Carbon::create(
                    (int) $matches[1],
                    (int) $matches[2],
                    (int) $matches[3]
                )->toDateTimeString();
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    private function convertirFecha(?string $fecha): ?string
    {
        if (!$fecha) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}