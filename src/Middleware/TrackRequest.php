<?php

namespace Mondago\ApplicationInsights\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mondago\ApplicationInsights\ApplicationInsights;

class TrackRequest
{
	public function __construct(private ApplicationInsights $insights)
	{
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		if ($request->hasSession()) {
			// In this case, both the user id and session id are equal.
			$this->insights->setAnonymousUserId($request->session()->getId());
			$this->insights->setSessionId($request->session()->getId());
		}
		return $next($request);
	}

	public function terminate($request, $response)
	{
		$this->insights->trackRequest($request, $response);
	}
}
