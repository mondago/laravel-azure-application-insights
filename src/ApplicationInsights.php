<?php

namespace Mondago\ApplicationInsights;

use ApplicationInsights\Telemetry_Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationInsights
{
    /**
     * @var Telemetry_Client
     */
    private $insights;

    /**
     * True if telemetry is enabled
     *
     * @var bool
     */
    private $isEnabled = true;

    /**
     *
     * @var bool
     */
    private $throwsExceptions = false;

    /**
     * Measurements to be sent along with request data
     *
     * @var array
     */
    private $requestMeasurements = [];

    /**
     * Request properties
     *
     * @var array
     */
    private $requestProperties = [];

    /**
     * ApplicationInsights constructor.
     *
     * @param Telemetry_Client $client
     * @param string $instrumentationKey
     * @param bool $isEnabled
     */
    public function __construct(Telemetry_Client $client, string $instrumentationKey, bool $isEnabled = true)
    {
        $this->insights = $client;
        $this->isEnabled = $isEnabled;
        if ($this->isEnabled()) {
            $this->insights->getContext()->setInstrumentationKey($instrumentationKey);
            $this->insights->getChannel()->setSendGzipped(true);
        }
        $this->shouldThrowExceptions = false;
    }

    /**
     * Check if application insights is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Defines if exceptions should be silently ignored or thrown
     *
     * @param bool $value
     */
    public function shouldThrowExceptions(bool $value)
    {
        $this->throwsExceptions = $value;
    }

    /**
     *  Reads relevant data from request and response to application insights trackrequest
     *
     * @param Request $request
     * @param Response $response
     * @param bool $sendAsync
     */
    public function trackRequest(Request $request, Response $response, bool $sendAsync = false)
    {
        if (!$this->isEnabled()) {
            return;
        }
        try {
            $this->setDefaultRequestProperties($request);

            $this->insights->trackRequest(
                'app ' . $request->getMethod() . ' ' . $request->getUri(),
                $request->fullUrl(),
                $this->getRequestStartTime(),
                $this->getRequestDurationTime(),
                $response->getStatusCode(),
                $response->isSuccessful(),
                $this->getRequestProperties(),
                $this->requestMeasurements
            );
            $this->insights->flush([], $sendAsync);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Add a measurement to be sent along with the request log
     *
     * @param string $key
     * @param float $value
     */
    public function addRequestMeasurement(string $key, float $value)
    {
        $this->requestMeasurements[$key] = $value;
    }

    /**
     * Start time of the request
     *
     * @return mixed
     */
    private function getRequestStartTime()
    {
        return $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Approximate duration of the request
     *
     * @return float|int
     */
    private function getRequestDurationTime()
    {
        return (microtime(true) - $this->getRequestStartTime()) * 1000;
    }

    /**
     * Extract default properties from request
     *
     * @param Request $request
     */
    private function setDefaultRequestProperties(Request $request)
    {
        $this->addRequestProperty('url', $request->fullUrl());
        $this->addRequestProperty('querystring', $request->getQueryString());
        $this->addRequestProperty('ip', $request->ip());
        $this->addRequestProperty('user-agent', $request->userAgent());
        $this->addRequestProperty('secure', $request->secure());
        $this->addRequestProperty('referer', $request->server->get('referer'));
        $this->addRequestProperty('method', $request->method());

        if ($request->route()) {
            $this->addRequestProperty('route', $request->route()->getName());
        }
    }

    /**
     *  Set request property
     *
     * @param string $key
     * @param $value
     */
    public function addRequestProperty(string $key, $value)
    {
        $this->requestProperties[$key] = $value;
    }

    /**
     * Information from the request
     *
     * @return array
     */
    private function getRequestProperties()
    {
        return $this->requestProperties;
    }

    /**
     * Sends exception to application insights
     *
     * @param \Exception $e
     * @param bool $sendAsync
     */
    public function trackException(\Exception $e, bool $sendAsync = false)
    {
        if (!$this->isEnabled()) {
            return;
        }

        # TODO: Exception can happen without a request being involved but it would be good to have request information when that's not the case
        try {

            $this->insights->trackException($e);
            $this->insights->flush([], $sendAsync);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Sends dependency to application insights
     *
     * @param string $name - The dependency name
     * @param int $durationInMilliseconds - The duration of the dependency, in milliseconds
     * @param string $type - The dependency type
     * @param array|null $properties - An array of name to value pairs to set
     */
    public function trackDependency(string $name, int $durationInMilliseconds, string $type = "", array $properties = null)
    {
        if (!$this->isEnabled()) {
            return;
        }

        try {
            $this->insights->trackDependency($name, $type, null, null, $durationInMilliseconds, true, null, $properties);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handles an exception
     *
     * @param \Exception $e
     * @throws \Exception
     */
    private function handleException(\Exception $e)
    {
        if ($this->throwsExceptions) {
            throw $e;
        }
    }

    /**
     * Proxy method calls to telemetry client
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([&$this->insights, $name], $arguments);
    }
}
