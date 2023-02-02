<?php

namespace Mondago\ApplicationInsights;

use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Mondago\ApplicationInsights\Listeners\LogMigration;
use Mondago\ApplicationInsights\Listeners\NoPendingMigrations as NoPendingMigrationsListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NoPendingMigrations::class => [
            NoPendingMigrationsListener::class
        ],
        MigrationEnded::class => [
            LogMigration::class,
        ],
        MigrationStarted::class => [
            LogMigration::class,
        ],
        MigrationsStarted::class => [
            LogMigration::class,
        ],
        MigrationsEnded::class => [
            LogMigration::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
