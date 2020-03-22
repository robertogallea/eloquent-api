<?php


namespace robertogallea\EloquentApi;


use robertogallea\EloquentApi\Commands\MakeApiModelCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class EloquentApiProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'eloquent-api');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->commands([
            MakeApiModelCommand::class,
        ]);
        Config::set('eloquent-api.app_path', app_path());
    }
}