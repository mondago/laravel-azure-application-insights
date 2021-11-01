<?php

namespace Mondago\ApplicationInsights\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackRequest
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		return $next($request);
	}

	public function terminate($request, $response)
	{
		app('insights')->trackRequest($request, $response, true);
	}
}
