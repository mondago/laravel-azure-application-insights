<?php
 
namespace Mondago\ApplicationInsights\Logging;

use Mondago\ApplicationInsights\ApplicationInsights;
use Mondago\ApplicationInsights\Logging\ApplicationInsightsHandler;
use Monolog\Logger;
 
class CreateApplicationInsightsLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logLevel = $config['log_level'] ?? Logger::DEBUG;
        $handler = new ApplicationInsightsHandler(app()->make(ApplicationInsights::class), $logLevel);
        return new Logger('app-insights', [$handler]);
    }
}
