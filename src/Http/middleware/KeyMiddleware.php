<?php

namespace Kwaadpepper\ResponsiveFileManager\Middleware;

use \Closure;
use Illuminate\Http\Request;

/**
 * Start Session on Interface
 */
class KeyMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('akey') || !config('rfm.access_keys')) {
            throw new AccessDeniedHttpException();
        }
        
        if (!in_array(strip_tags(preg_replace(
            "/[^a-zA-Z0-9\._-]/",
            '',
            $request->get('akey')
        )), config('rfm.access_keys'))) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
