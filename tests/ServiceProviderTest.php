<?php

namespace Mondago\ApplicationInsights\Tests;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Orchestra\Testbench\TestCase;
use Mondago\ApplicationInsights\ServiceProvider;

class ServiceProviderTest extends TestCase
{

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class
        ];
    }

    /**
     * Check that is loaded correctly by laravel
     * @return void
     */
    public function test_that_it_loads_correctly()
    {
        $this->app['config']->set(ServiceProvider::DISPLAY_NAME . '.instrumentation_key', 'notarealinstrumentationkey');

        $insights = $this->app['insights'];
        $this->assertTrue($insights->isEnabled());

        $this->assertInstanceOf(\Mondago\ApplicationInsights\ApplicationInsights::class, $insights);

    }

    /**
     * Check that is disabled if configuration is set
     * @return void
     */
    public function test_that_it_is_disabled_if_set_in_configuration()
    {
        $this->app['config']->set(ServiceProvider::DISPLAY_NAME . '.instrumentation_key', 'notarealinstrumentationkey');
        $this->app['config']->set(ServiceProvider::DISPLAY_NAME . '.is_enabled', false);

        $insights = $this->app['insights'];
        $this->assertFalse($insights->isEnabled());

        $this->assertInstanceOf(\Mondago\ApplicationInsights\ApplicationInsights::class, $insights);

    }

    /**
     * Check that it tries to send data
     * This test will throw an exception because the instrumentation key it's not correct
     * @return void
     */
    public function test_that_it_tries_to_send_data()
    {
        $this->app['config']->set(ServiceProvider::DISPLAY_NAME . '.instrumentation_key', 'notarealinstrumentationkey');

        $insights = $this->app['insights'];
        $this->assertInstanceOf(\Mondago\ApplicationInsights\ApplicationInsights::class, $insights);
        $this->assertTrue($insights->isEnabled());

        $this->expectException(ClientException::class);
        $insights->shouldThrowExceptions(true);

        $insights->trackRequest(new Request(), new Response());

    }

}