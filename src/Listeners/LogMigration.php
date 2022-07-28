<?php

namespace Mondago\ApplicationInsights\Listeners;

use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationEvent;
use Illuminate\Database\Events\MigrationStarted;
use ReflectionClass;

class LogMigration
{
    public function handle(MigrationEvent $event)
    {
        $migrationName = basename((new ReflectionClass($event->migration))->getFileName());

        if ($event instanceof MigrationStarted) {
            app('insights')->trackEvent("Migration $migrationName {$event->method} command started");
        }
        if ($event instanceof MigrationEnded) {
            app('insights')->trackEvent("Migration $migrationName {$event->method} command ended");
        }
    }
}
