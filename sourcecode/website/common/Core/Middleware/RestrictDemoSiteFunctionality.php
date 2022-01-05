<?php namespace Common\Core\Middleware;

use Auth;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Arr;
use Str;
use Symfony\Component\HttpFoundation\Response;

class RestrictDemoSiteFunctionality
{

    /**
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user() && Auth::user()->email === 'Ic0OdCIodqz8q1r@demo.com') {
            return $next($request);
        }

        $uri = str_replace('secure/', '', $request->route()->uri());

        if ($this->shouldForbidRequest($request, $uri)) {
            abort(403, "You can't do that on demo site.");
        }

        if ($uri === 'settings') {
            return $this->manglePrivateSettings($next($request));
        }

        if ($uri === 'users' || $uri === 'billing/subscriptions') {
            return $this->mangleUserEmails($next($request));
        }

        if (($uri === 'billing/stripe/cards/add' || $uri === 'billing/subscriptions/paypal/agreement/create') && (Auth::user() && Auth::user()->email === 'admin@admin.com')) {
            abort(403, "Demo admin account can't subscribe to plans.");
        }

        return $next($request);
    }

    /**
     * Check if specified request should be forbidden on demo site.
     *
     * @param Request $request
     * @param string $uri
     * @return bool
     */
    private function shouldForbidRequest(Request $request, $uri)
    {
        $method = $request->method();

        foreach (config('common.demo-blocked-routes') as $route) {
            if ($method === $route['method'] && trim($uri) === trim($route['name'])) {
                $originMatches = true;
                $paramsMatch = true;

                //block this request only if it originated from specified origin, for example: admin area
                if (isset($route['origin'])) {
                    $originMatches = \Str::contains($request->server('HTTP_REFERER'), $route['origin']);
                }

                if (isset($route['params'])) {
                    $paramsMatch = collect($route['params'])->first(function($param, $key) use($request) {
                        $routeParam = $request->route($key);
                        if (is_array($param)) {
                            return in_array($routeParam, $param);
                        } else {
                            return $routeParam == $param;
                        }
                    }) !== null;
                }

                return $originMatches && $paramsMatch;
            }
        }

        return false;
    }

    /**
     * Mangle settings values, so they are not visible on demo site.
     *
     * @param Response $response
     * @return Response
     */
    private function manglePrivateSettings(Response $response)
    {
        $serverKeys = ['google_id', 'google_secret', 'twitter_id', 'twitter_secret', 'facebook_id',
            'facebook_secret', 'spotify_id', 'spotify_secret', 'lastfm_api_key', 'soundcloud_api_key',
            'sentry_dns', 'mailgun_secret', 'sentry_dsn', 'paypal_client_id', 'pusher_key', 'pusher_secret',
            'paypal_secret', 'stripe_key', 'stripe_secret', 'mail_password', 'tmdb_api_key'
        ];

        $clientKeys = ['youtube_api_key', 'logging.sentry_public', 'analytics.google_id',
            'builder.google_fonts_api_key', 'recaptcha.site_key', 'recaptcha.secret_key'];

        $settings = json_decode($response->getContent(), true);

        foreach ($serverKeys as $key) {
            if (isset($settings['server'][$key])) {
                $settings['server'][$key] = Str::random(30);
            }
        }

        foreach ($clientKeys as $key) {
            if (isset($settings['client'][$key])) {
                $settings['client'][$key] = Str::random(30);
            }
        }

        $response->setContent(json_encode($settings));

        return $response;
    }

    /**
     * Mangle settings values, so they are not visible on demo site.
     *
     * @param Response|JsonResponse $response
     * @return Response
     */
    private function mangleUserEmails($response)
    {
        $pagination = json_decode($response->getContent(), true);

        $pagination['data'] = array_map(function($item) {
            if (isset($item['email'])) {
                $item['email'] = 'hidden@demo.com';
            } else if (isset($item['user']['email'])) {
                $item['user']['email'] = 'hidden@demo.com';
            }

            return $item;
        }, Arr::get($pagination, 'data', []));

        $response->setContent(json_encode($pagination));

        return $response;
    }
}
