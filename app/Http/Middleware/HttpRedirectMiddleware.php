<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class HttpRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (
            (!$request->secure() || $request->header('host') !== Str::after(config('app.url'), '://'))
            && App::isProduction()
        ) {
            return redirect(config('app.url') . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
