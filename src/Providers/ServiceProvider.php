<?php

namespace Spinen\Ncentral\Providers;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * Class ServiceProvider
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishes();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/ncentral.php', 'ncentral');
    }

    /**
     * There are several resources that get published
     *
     * Only worry about telling the application about them if running in the console.
     */
    protected function registerPublishes()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

            $this->publishes(
                groups: 'ncentral-config',
                paths: [
                    __DIR__.'/../../config/ncentral.php' => config_path('ncentral.php'),
                ],
            );

            $this->publishes(
                groups: 'ncentral-migrations',
                paths: [
                    __DIR__.'/../../database/migrations' => database_path('migrations'),
                ],
            );
        }
    }
}
