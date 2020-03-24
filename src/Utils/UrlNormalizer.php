<?php

namespace robertogallea\EloquentApi\Utils;

interface UrlNormalizer
{
    public function normalize(string $url): string;
}
