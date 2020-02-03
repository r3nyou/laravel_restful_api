<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $domains = [$host];

        $origin = $request->server()['HTTP_ORIGIN'];

        if (in_array($origin, $domains)) {
            return $next($request)
                ->header('Access-Contro-Allow-Origin', $origin)
                ->header('Access-Contro-Allow-Headers', 'Origin')
                ->header('Access-Contro-Allow-Methods', 'PUT, GET, POST, DELETE, OPTIONS');
        }

        return $next($request);
    }
}
