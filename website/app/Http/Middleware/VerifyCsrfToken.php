<?php

namespace App\Http\Middleware;

use Common\Core\BaseVerifyCsrfToken;

class VerifyCsrfToken extends BaseVerifyCsrfToken
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'secure/auth/login',
        'secure/auth/register',
        'secure/auth/logout',
        'secure/auth/password/email',
        'secure/update/run',
        'secure/track/plays/*/log',
        'secure/player/tracks'
    ];
}
