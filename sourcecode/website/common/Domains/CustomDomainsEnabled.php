<?php

namespace Common\Domains;

use Closure;
use Illuminate\Http\Request;

class CustomDomainsEnabled
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! config('common.site.enable_custom_domains')) {
            abort(404);
        }

        return $next($request);
    }
}
