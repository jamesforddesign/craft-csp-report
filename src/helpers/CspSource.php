<?php

namespace jfd\craftcspreport\helpers;

class CspSource
{
    private const KEYWORDS = [
        'inline', 'eval', 'self', 'data', 'blob',
        'about', 'filesystem', 'wasm-eval',
    ];

    public static function normalize(string $blockedUri): string
    {
        $value = trim($blockedUri);

        if ($value === '') {
            return 'unknown';
        }

        if (in_array($value, self::KEYWORDS, true)) {
            return $value;
        }

        if (str_starts_with($value, '//')) {
            return self::extractOrigin('https:' . $value) ?? $value;
        }

        if (str_starts_with($value, '/')) {
            return 'self';
        }

        if (preg_match('#^https?://#i', $value)) {
            return self::extractOrigin($value) ?? $value;
        }

        return $value;
    }

    private static function extractOrigin(string $url): ?string
    {
        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['scheme'], $parsed['host'])) {
            return null;
        }

        $origin = $parsed['scheme'] . '://' . $parsed['host'];

        if (isset($parsed['port'])) {
            $origin .= ':' . $parsed['port'];
        }

        return $origin;
    }
}
