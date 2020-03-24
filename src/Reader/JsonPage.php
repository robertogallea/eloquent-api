<?php

declare(strict_types=1);

namespace robertogallea\EloquentApi\Reader;

final class JsonPage
{
    /** @var array */
    private $data;
    
    /** @var string */
    private $nextPage;
    
    public function __construct(array $data, ?string $nextPage)
    {
        $this->data = $data;
        $this->nextPage = $nextPage;
    }
    
    public function getData(): array
    {
        return $this->data;
    }
    
    public function getNextPage(): ?string
    {
        return $this->nextPage;
    }
}
