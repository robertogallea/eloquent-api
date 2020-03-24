<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Utils;

class UrlNormalizerDefault implements UrlNormalizer
{
    public function normalize(string $url): string
    {
        $urlData = parse_url($url);
        
        return $urlData['scheme'] . '://' . $urlData['host'] . (isset($urlData['port']) ? ':' . $urlData['port'] : '') . ($urlData['path'] ?? '');
    }
}
