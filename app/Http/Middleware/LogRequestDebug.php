<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestDebug
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $targetUri = 'orders/bulk-update'; // Use your last tested, non-conflicting URI

        // Log the details for every request to orders/bulk-update
        if ($request->path() === $targetUri) {
            Log::warning('!!! MASS UPDATE DEBUG START !!!');
            Log::info('Method Received: ' . $request->method());
            Log::info('Full URI Received: ' . $request->fullUrl());
            Log::info('Path Received: ' . $request->path());
            Log::warning('!!! MASS UPDATE DEBUG END !!!');
        }

        return $next($request);
    }
}