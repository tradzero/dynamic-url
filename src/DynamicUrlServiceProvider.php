<?php

namespace Tradzero\DynamicUrl;

use Illuminate\Support\ServiceProvider;
use Tradzero\DynamicUrl\Commands\SyncUrlAvailableStatusCommand;

class DynamicUrlServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/dynamic_url.php' => config_path('dynamic_url.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncUrlAvailableStatusCommand::class,
            ]);
        }
    }
}
