<?php

namespace Mondago\ApplicationInsights;


use ApplicationInsights\Telemetry_Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     */
    public function trackRequest(Request $request, Response $response)
    {
        if (!$this->isEnabled()) {
            return;
        }
        try {
            $this->insights->trackRequest(
                'app ' . $request->getMethod() . ' ' . $request->getUri(),
                $request->fullUrl(),
                $this->getRequestStartTime(),
                $this->getRequestDurationTime(),
                $response->status(),
                $response->isSuccessful(),
                $this->getRequestProperties($request),
                $this->requestMeasurements
            );
            $this->insights->flush();

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
     * Information from the request
     *
     * @param Request $request
     * @return array
     */
    private function getRequestProperties(Request $request)
    {
        $props = [
            'url' => $request->fullUrl(),
            'querystring' => $request->getQueryString(),
            'ip' => $request->ip(),
            'user-agent' => $request->userAgent(),
            'secure' => $request->secure(),
            'referer' => $request->server->get('referer'),
            'method' => $request->method()
        ];

        if ($request->route()) {
            $props['route'] = $request->route()->getName();
        }

        return $props;
    }

    /**
     * Sends exception to application insights
     *
     * @param \Exception $e
     */
    public function trackException(\Exception $e)
    {
        if (!$this->isEnabled()) {
            return;
        }

        # TODO: Exception can happen without a request being involved but it would be good to have request information when that's not the case
        try {

            $this->insights->trackException($e);
            $this->insights->flush();

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
}