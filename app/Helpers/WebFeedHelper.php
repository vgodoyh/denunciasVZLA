<?php

namespace App\Helpers;

class WebFeedHelper
{
    /**
     * Devuelve el path normalizado del feed (/feed)
     */
    public static function feedPath(string $input): string
    {
        $url = trim($input);

        // Quitar esquema si viene
        $url = preg_replace('#^https?://#i', '', $url);

        // Quitar slash final
        $url = rtrim($url, '/');

        // Si ya termina en feed
        if (preg_match('#/feed$#i', $url)) {
            return $url;
        }

        return $url . '/feed';
    }
}
