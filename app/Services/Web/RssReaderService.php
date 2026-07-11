<?php

namespace App\Services\Web;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RssReaderService
{
    public function leer(string $url, int $limit = 10): array
    {
        $xmlString = @file_get_contents($url);

        if (!$xmlString || trim($xmlString) === '') {
            Log::error('RSS: no se pudo descargar', ['url' => $url]);
            return [];
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $loaded = $dom->loadXML($xmlString);

        libxml_clear_errors();

        if (!$loaded) {
            Log::error('RSS: no se pudo parsear XML', ['url' => $url]);
            return [];
        }

        $xpath = new \DOMXPath($dom);

        // Buscar todos los <item> sin importar namespaces
        $itemsNodes = $xpath->query('//*[local-name()="item"]');

        if (!$itemsNodes || $itemsNodes->length === 0) {
            Log::warning('RSS: no se encontraron items', ['url' => $url]);
            return [];
        }

        $items = [];

        foreach ($itemsNodes as $i => $itemNode) {
            if ($i >= $limit) break;

            $tituloNode = $xpath->query('./*[local-name()="title"]', $itemNode)->item(0);
            $linkNode   = $xpath->query('./*[local-name()="link"]', $itemNode)->item(0);
            $dateNode   = $xpath->query('./*[local-name()="pubDate"]', $itemNode)->item(0);

            // content:encoded (WordPress)
            $contentNode = $xpath->query('./*[local-name()="encoded"]', $itemNode)->item(0);

            // fallback description
            if (!$contentNode) {
                $contentNode = $xpath->query('./*[local-name()="description"]', $itemNode)->item(0);
            }

            $items[] = [
                'titulo'    => $tituloNode ? trim($tituloNode->textContent) : null,
                'url'       => $linkNode ? trim($linkNode->textContent) : null,
                'contenido' => $contentNode ? trim(strip_tags($contentNode->textContent)) : null,
                'fecha'     => $dateNode
                    ? Carbon::parse($dateNode->textContent)
                    : Carbon::now(),
            ];
        }

        return $items;
    }
}