<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Reader;

use Illuminate\Support\Facades\Facade;

/**
 * @see JsonApiReader
 * @method static read(string $endpoint, ?string $getNextPageField = null, ?string $getDataField = null): LazyCollection
 */
class JsonReader extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return JsonApiReader::class;
    }
}
