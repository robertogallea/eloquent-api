<?php


namespace robertogallea\EloquentApi;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Sushi\Sushi;

class ApiModel extends Model
{
    use Sushi;

    protected $rows = [];
    protected $endpoint;
    protected $nextPageField = null;
    protected $dataField = null;
    public $primaryKey = 'id';
    public $cacheName;

    public function __construct()
    {
        parent::__construct();

        $this->cacheDirectory = realpath(config('sushi.cache-path', storage_path('framework/cache')));
        $this->cacheName = $this->getCacheName();
    }

    protected static function boot()
    {
        parent::boot();

        /*
         * Determine which attributes should be casted to arrays
         */
        static::retrieved(function($model) {
            foreach ($model->getAttributes() as $key => $attribute) {
                if (static::shouldCastToArray($model, $key, $attribute)) {
                    $model->mergeCasts([$key => 'array']);
                }
            }
        });
    }

    /**
     * @param $model
     * @param $key
     * @param $attribute
     * @return bool
     */
    private static function shouldCastToArray($model, $key, $attribute): bool
    {
        return !array_key_exists($key, $model->getCasts()) &&
            is_array(json_decode($attribute, true));
    }

    public function invalidateCache()
    {
        unlink(config('sushi.cache-path').'/'.config('sushi.cache-prefix', 'sushi').'-'.Str::kebab(str_replace('\\', '', static::class)).'.sqlite');
    }

    public function getRows()
    {
        if (empty($this->rows)) {
            $rows = $this->loadFromApi()->toArray();
            foreach ($rows as &$row) {
                foreach ($row as $key => &$column) {
                    if (is_array($column)) {
                        $column = json_encode($column);
                    }
                }
            }
            $this->rows = $rows;
        }

        return $this->rows;
    }

    public function getCacheName()
    {
        return !is_null($this->getConnection()) ? explode('.', basename($this->getConnection()->getDatabaseName()))[0] : null;
    }

    /**
     * @param $endpoint
     * @param array $options
     * @return LazyCollection
     */
    public function loadFromApi()
    {
        return LazyCollection::make(function () {
            $count = 0;

            $urlData = parse_url($this->endpoint);

            $baseUri = $urlData['scheme'] . '://' . $urlData['host'] . (isset($urlData['port']) ? ':' . $urlData['port'] : '') . ($urlData['path'] ?? '');

            $nextPage = $baseUri;
            while (!is_null($nextPage)) {
                list($data, $nextPage) = $this->getNextPage($nextPage);
                $data = $this->offsetKeys($data, $count);

                $count+=sizeof($data);

                yield from $data;
            }
        });
    }

    private function getNextPage(string $nextPage): array
    {
        $response = Http::get($nextPage);

        $data = $response->json();

        $nextPage = $data[$this->getNextPageField()] ?? null;

        if (!$this->getDataField()) {
            return array($data, $nextPage);
        }

        return array($data[$this->dataField], $nextPage);
    }

    private function offsetKeys(array $data, int $count)
    {
        $newData = [];

        foreach ($data as $key => $value) {
            $newData[$key + $count] = $value;
        }

        return $newData;
    }

    private function getNextPageField()
    {
        return $this->nextPageField ?? null;
    }

    private function getDataField()
    {
        return $this->dataField ?? null;
    }
}