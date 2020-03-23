<?php

namespace robertogallea\EloquentApi;

use robertogallea\EloquentApi\Commands\MakeApiModelCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use robertogallea\EloquentApi\Reader\JsonApiReader;
use robertogallea\EloquentApi\Reader\LaravelJsonApiReader;

class EloquentApiProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'eloquent-api');
        
        $this->app->singleton(JsonApiReader::class, LaravelJsonApiReader::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->commands([
            MakeApiModelCommand::class,
        ]);
        Config::set('eloquent-api.app_path', app_path());
    }
}
