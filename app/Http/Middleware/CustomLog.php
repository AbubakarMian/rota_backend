<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Monolog;

class CustomLog {

	/**
	 * Prepare all logs in general
	 *
	 * @param Illuminate\Http\Request $request
	 * @param Closure $next
	 */
	public function handle($request, Closure $next)
	{
		//PreMiddleware: Log request Headers & Parameters

		Log::info($request->fullUrl());
		Log::info($request->method());
		Log::info($request->headers . $request->getContent());

		\DB::enableQueryLog();

		//Executed request & response
		$response = $next($request);

		//PostMiddleware: Log the database queries
		$queries = \DB::getQueryLog();
		Log::info($queries);

		return $response;
	}
}
