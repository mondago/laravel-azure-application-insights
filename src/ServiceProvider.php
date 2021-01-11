<?php

namespace Mondago\ApplicationInsights;

use ApplicationInsights\Telemetry_Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    const DISPLAY_NAME = 'insights';

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/application_insights.php' => config_path('application_insights.php'),
        ]);

        if (config(static::DISPLAY_NAME . '.is_enabled')) {
            DB::listen(function ($query) {
                $this->app[static::DISPLAY_NAME]->trackDependency($query->connection->getConfig('host'), $query->time, 'SQL', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'connection' => $query->connectionName,
                ]);
            });
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/application_insights.php', static::DISPLAY_NAME);

        $this->app->singleton(ApplicationInsights::class, function () {
            return new ApplicationInsights(
                new Telemetry_Client(),
                config(static::DISPLAY_NAME . '.instrumentation_key'),
                config(static::DISPLAY_NAME . '.is_enabled')
            );
        });

        $this->app->alias(ApplicationInsights::class, static::DISPLAY_NAME);

    }
}
