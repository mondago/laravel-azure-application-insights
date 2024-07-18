<?php

namespace Mondago\ApplicationInsights;

use ApplicationInsights\Channel\Contracts\Cloud;
use ApplicationInsights\Telemetry_Client;
use ApplicationInsights\Telemetry_Context;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    const DISPLAY_NAME = 'application_insights';

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/application_insights.php' => config_path(static::DISPLAY_NAME),
        ]);

        if (config(static::DISPLAY_NAME . '.is_enabled')) {
            DB::listen(function (QueryExecuted $query) {
                $this->app[static::DISPLAY_NAME]->trackDependency($query->connection->getConfig('host') ?? 'db', intval($query->time), 'SQL', [
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
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/application_insights.php', static::DISPLAY_NAME);

        $cloudContext = new Cloud();
        $cloudContext->setRole(env('WEBSITE_SITE_NAME'));
        $cloudContext->setRoleInstance(env('WEBSITE_INSTANCE_ID'));
        $context = new Telemetry_Context();
        $context->setCloudContext($cloudContext);
        $client = new Telemetry_Client($context);

        $this->app->singleton(ApplicationInsights::class, function () use ($client) {
            return new ApplicationInsights(
                $client,
                config(static::DISPLAY_NAME . '.instrumentation_key'),
                config(static::DISPLAY_NAME . '.is_enabled'),
                config(static::DISPLAY_NAME . '.track_anonymous_users')
            );
        });

        $this->app->alias(ApplicationInsights::class, static::DISPLAY_NAME);
        $this->app->register(EventServiceProvider::class);

    }
}
