<?php

namespace Mondago\ApplicationInsights\Tests;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Insights;
use Mondago\ApplicationInsights\ApplicationInsights;
use Mondago\ApplicationInsights\ServiceProvider;

class ServiceProviderTest extends TestCase
{

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

        $this->assertInstanceOf(\Mondago\ApplicationInsights\ApplicationInsights::class, $this->app['insights']);
        $this->assertTrue(Insights::isEnabled());

        $this->expectException(ClientException::class);
        Insights::shouldThrowExceptions(true);

        Insights::trackRequest(new Request(), new Response());
    }


    /**
     * Check it listens to DB correctly
     * 
     * @return void
     */
    public function test_that_it_listens_to_db_queries()
    {
        $this->app['config']->set(ServiceProvider::DISPLAY_NAME . '.instrumentation_key', 'notarealinstrumentationkey');

        $insights = $this->spy(ApplicationInsights::class);

        $connection = $this->getConnection();

        $queryTimeAsFloat = 3.34;
        $expectedQueryTimeAsInt = 3;

        Event::dispatch(new QueryExecuted(
            sql: "select * from users where name = ?",
            bindings: "mondy",
            time: $queryTimeAsFloat,
            connection: $connection
        ));

        $insights->shouldHaveReceived('trackDependency', [
            $connection->getConfig('host'),
            $expectedQueryTimeAsInt,
            'SQL',
            [
                'sql' => 'select * from users where name = ?',
                'bindings' => 'mondy',
                'connection' => $connection->getName()
            ]
        ]);
    }
}
