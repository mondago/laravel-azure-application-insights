<?php

namespace Mondago\ApplicationInsights;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @see \Mondago\ApplicationInsights\ApplicationInsights
 */
class Facade extends LaravelFacade
{
    protected static function getFacadeAccessor()
    {
        return 'insights';
    }
}