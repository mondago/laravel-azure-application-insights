<?php

namespace Mondago\ApplicationInsights\Listeners;

use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\MigrationStarted;
use Mondago\ApplicationInsights\ApplicationInsights;
use ReflectionClass;

class LogMigration
{
    public function __construct(private ApplicationInsights $insights)
    {
    }

    public function handle($event)
    {
        if ($event instanceof MigrationsStarted) {
            return $this->insights->trackEvent("Migration run about to start");
        }
        if ($event instanceof MigrationsEnded) {
            return $this->insights->trackEvent("Migration run finished");
        }

        $migrationName = basename((new ReflectionClass($event->migration))->getFileName());
        if ($event instanceof MigrationStarted) {
            return $this->insights->trackEvent("Migration $migrationName {$event->method} command started");
        }
        if ($event instanceof MigrationEnded) {
            return $this->insights->trackEvent("Migration $migrationName {$event->method} command ended");
        }
    }
}
