<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Reader;

use Illuminate\Support\LazyCollection;
use robertogallea\EloquentApi\Utils\UrlNormalizer;

class LaravelJsonApiReader implements JsonApiReader
{
    /**
     * @var UrlNormalizer
     */
    private $urlNormalizer;
    
    /**
     * @var JsonPageReader
     */
    private $pageReader;
    
    public function __construct(UrlNormalizer $urlNormalizer, JsonPageReader $pageReader)
    {
        $this->urlNormalizer = $urlNormalizer;
        $this->pageReader = $pageReader;
    }
    
    public function read(string $url, ?string $nextPageField = null, ?string $dataField = null): LazyCollection
    {
        return LazyCollection::make(
            function () use ($url, $nextPageField, $dataField) {
                $count = 0;
                
                $nextPage = $this->urlNormalizer->normalize($url);
                
                while (!is_null($nextPage)) {
                    $page = $this->pageReader->read($nextPage, $nextPageField, $dataField);
                    
                    $data = $page->getData();
                    $nextPage = $page->getNextPage();
                    
                    $data = $this->offsetKeys($data, $count);
                    
                    $count += sizeof($data);
                    
                    yield from $data;
                }
            }
        );
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
