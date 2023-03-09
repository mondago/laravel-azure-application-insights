<?php

namespace Mondago\ApplicationInsights\Logging;

use ApplicationInsights\Channel\Contracts\Message_Severity_Level;
use Mondago\ApplicationInsights\ApplicationInsights;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class ApplicationInsightsHandler extends AbstractProcessingHandler
{
    /**
     * @var ApplicationInsights
     */
    protected $client;

    public function __construct(ApplicationInsights $client, $level = Level::Info, bool $bubble = true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        if (isset($record->context['exception'])) {
            $this->client->trackException($record->context['exception']);
        } else {
            $this->client->trackMessage(
                (string) $record->message,
                $this->mapMonologLevelToLoggingInterface($record->level),
                $record->context,
            );
        }
    }

    private function mapMonologLevelToLoggingInterface(Level $level): int
    {
        return match ($level) {
            Level::Emergency->value => Message_Severity_Level::CRITICAL,
            Level::Alert->value     => Message_Severity_Level::CRITICAL,
            Level::Critical->value  => Message_Severity_Level::CRITICAL,
            Level::Error->value     => Message_Severity_Level::ERROR,
            Level::Warning->value   => Message_Severity_Level::WARNING,
            Level::Notice->value    => Message_Severity_Level::INFORMATION,
            Level::Info->value      => Message_Severity_Level::INFORMATION,
            Level::Debug->value     => Message_Severity_Level::VERBOSE,
            default                 => Message_Severity_Level::INFORMATION,
        };
    }
}
