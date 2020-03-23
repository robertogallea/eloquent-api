<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Reader;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use robertogallea\EloquentApi\Reader\JsonApiReader;

class LaravelJsonApiReader implements JsonApiReader
{
    
    public function read(string $url, ?string $nextPageField = null, ?string $dataField = null): LazyCollection
    {
        return LazyCollection::make(
            function () use ($url, $nextPageField, $dataField) {
                $count = 0;
                
                $urlData = parse_url($url);
                
                $baseUri = $urlData['scheme'] . '://' . $urlData['host'] . (isset($urlData['port']) ? ':' . $urlData['port'] : '') . ($urlData['path'] ?? '');
                
                $nextPage = $baseUri;
                
                while (!is_null($nextPage)) {
                    [$data, $nextPage] = $this->getNextPage($nextPage, $nextPageField, $dataField);
                    $data = $this->offsetKeys($data, $count);
                    
                    $count += sizeof($data);
                    
                    yield from $data;
                }
            }
        );
    }
    
    private function getNextPage(string $url, ?string $nextPageField, ?string $dataField): array
    {
        // Retrieve tha data
        $response = Http::get($url);
        
        $data = $response->json();
        
        $nextPage = $data[$nextPageField] ?? null;
        $data = $dataField ? $data[$dataField] : $data;
        
        return [$data, $nextPage];
    }
    
    private function offsetKeys(array $data, int $count)
    {
        $newData = [];
        
        foreach ($data as $key => $value) {
            $newData[$key + $count] = $value;
        }
        
        return $newData;
    }
}
