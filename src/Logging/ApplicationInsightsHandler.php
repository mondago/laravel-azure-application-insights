<?php

namespace Mondago\ApplicationInsights\Logging;

use ApplicationInsights\Channel\Contracts\Message_Severity_Level;
use Mondago\ApplicationInsights\ApplicationInsights;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class ApplicationInsightsHandler extends AbstractProcessingHandler
{
    const InsightToLoggingInterfaceMapping = [
        Logger::EMERGENCY => Message_Severity_Level::CRITICAL,
        Logger::ALERT     => Message_Severity_Level::CRITICAL,
        Logger::CRITICAL  => Message_Severity_Level::CRITICAL,
        Logger::ERROR     => Message_Severity_Level::ERROR,
        Logger::WARNING   => Message_Severity_Level::WARNING,
        Logger::NOTICE    => Message_Severity_Level::INFORMATION,
        Logger::INFO      => Message_Severity_Level::INFORMATION,
        Logger::DEBUG     => Message_Severity_Level::VERBOSE,
    ];

    /**
     * @var ApplicationInsights
     */
    protected $client;

    public function __construct(ApplicationInsights $client, $level = Logger::INFO, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        if (isset($record['context']['exception'])) {
            $this->client->trackException($record['context']['exception']);
        } else {
            $this->client->trackMessage(
                (string) $record['message'],
                self::InsightToLoggingInterfaceMapping[$record['level']],
                $record['context'],
            );
        }
    }
}
