<?php

namespace Kwaadpepper\ResponsiveFileManager\Middleware;

use \Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Start Session on Interface
 */
class SessionMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('RFMInterface') || $request->routeIs('RFMInterfaceNew')) {
            session()->start();
        } else {
            if (!session()->exists('RF') || session('RF.verify') != "RESPONSIVEfilemanager") {
                throw new AccessDeniedHttpException();
            }
        }

        return $next($request);
    }
}
