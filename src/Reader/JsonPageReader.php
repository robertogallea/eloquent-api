<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Reader;

interface JsonPageReader
{
    public function read(string $url, ?string $nextPageField = null, ?string $dataField = null): JsonPage;
}
