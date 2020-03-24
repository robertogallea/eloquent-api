<?php

namespace robertogallea\EloquentApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use robertogallea\EloquentApi\Reader\JsonReader;
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
        static::retrieved(
            function (Model $model) {
                foreach ($model->getAttributes() as $key => $attribute) {
                    if (static::shouldCastToArray($model, $key, $attribute)) {
                        $model->mergeCasts([$key => 'array']);
                    }
                }
            }
        );
    }
    
    /**
     * @param $model
     * @param $key
     * @param $attribute
     * @return bool
     */
    private static function shouldCastToArray(Model $model, $key, $attribute): bool
    {
        return !array_key_exists($key, $model->getCasts()) &&
            is_array(json_decode($attribute, true));
    }
    
    public function invalidateCache()
    {
        unlink(
            config('sushi.cache-path') . '/' .
            config('sushi.cache-prefix', 'sushi') . '-' .
            Str::kebab(str_replace('\\', '', static::class)) . '.sqlite'
        );
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
        return !is_null($this->getConnection()) ? explode(
            '.',
            basename($this->getConnection()->getDatabaseName())
        )[0] : null;
    }
    
    /**
     * @return LazyCollection
     */
    public function loadFromApi()
    {
        return JsonReader::read($this->endpoint, $this->nextPageField, $this->dataField);
    }
}
