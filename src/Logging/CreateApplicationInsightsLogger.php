<?php
 
namespace Mondago\ApplicationInsights\Logging;

use Mondago\ApplicationInsights\ApplicationInsights;
use Mondago\ApplicationInsights\Logging\ApplicationInsightsHandler;
use Monolog\Level;
use Monolog\Logger;

class CreateApplicationInsightsLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config): Logger
    {
        $logLevel = $config['log_level'] ?? Level::Debug;
        $client = app()->make(ApplicationInsights::class);
        $handler = new ApplicationInsightsHandler($client, $logLevel);
        return new Logger('app-insights', [$handler]);
    }
}
