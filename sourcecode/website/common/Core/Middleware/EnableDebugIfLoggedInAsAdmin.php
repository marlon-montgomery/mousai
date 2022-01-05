<?php

namespace Common\Core\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class EnableDebugIfLoggedInAsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->loggedInAsAdmin()) {
            config(['app.debug' => true]);
        }

        return $next($request);
    }

    /**
     * @return bool
     */
    protected function loggedInAsAdmin()
    {
        return Auth::user() && Auth::user()->hasPermission('admin');
    }
}
