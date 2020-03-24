<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Reader;

use Illuminate\Support\Facades\Http;

class JsonPageReaderDefault implements JsonPageReader
{
    public function read(string $url, ?string $nextPageField = null, ?string $dataField = null): JsonPage
    {
        // Retrieve tha data
        $response = Http::get($url);
    
        $data = $response->json();
    
        $nextPage = $data[$nextPageField] ?? null;
        $data = $dataField ? $data[$dataField] : $data;
    
        return new JsonPage($data, $nextPage);
    }
}
