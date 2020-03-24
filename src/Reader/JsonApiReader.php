<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Reader;

use Illuminate\Support\LazyCollection;

interface JsonApiReader
{
    public function read(string $url, ?string $nextPageField = null, ?string $dataField = null): LazyCollection;
}
