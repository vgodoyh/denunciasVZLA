<?php

namespace App\Helpers;

class KeywordMatcher
{
    public static function matches(string $text, array $keywords): bool
    {
        $text = mb_strtolower($text);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }
}