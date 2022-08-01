<?php

namespace Mondago\ApplicationInsights;

use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Mondago\ApplicationInsights\Listeners\LogMigration;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MigrationEnded::class => [
            LogMigration::class,
        ],
        MigrationStarted::class => [
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
