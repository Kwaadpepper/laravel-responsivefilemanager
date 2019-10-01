<?php

namespace Kwaadpepper\ResponsiveFileManager\Middleware;

use \Closure;
use Illuminate\Http\Request;

/**
 * Purify parameters with strip_tags
 * to prevent XSS
 */
class XssMiddleware
{

    public function handle(Request $request, Closure $next)
    {

        $input = $request->all();

        array_walk_recursive($input, function (&$input) {

            $input = strip_tags($input);
        });

        $request->merge($input);

        return $next($request);
    }
}
