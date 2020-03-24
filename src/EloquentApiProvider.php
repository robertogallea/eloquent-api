<?php

namespace robertogallea\EloquentApi;

use robertogallea\EloquentApi\Commands\MakeApiModelCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use robertogallea\EloquentApi\Reader\JsonApiReader;
use robertogallea\EloquentApi\Reader\JsonPageReader;
use robertogallea\EloquentApi\Reader\JsonPageReaderDefault;
use robertogallea\EloquentApi\Reader\LaravelJsonApiReader;
use robertogallea\EloquentApi\Utils\UrlNormalizer;
use robertogallea\EloquentApi\Utils\UrlNormalizerDefault;

class EloquentApiProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'eloquent-api');
        
        $this->app->singleton(JsonApiReader::class, LaravelJsonApiReader::class);
        $this->app->singleton(UrlNormalizer::class, UrlNormalizerDefault::class);
        $this->app->singleton(JsonPageReader::class, JsonPageReaderDefault::class);
    }
    
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->commands(
            [
                MakeApiModelCommand::class,
            ]
        );
        Config::set('eloquent-api.app_path', app_path());
    }
}
