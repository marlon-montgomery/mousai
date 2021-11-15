<?php

namespace Common\Core;

use Common\Domains\CustomDomain;
use Config;
use DB;
use Illuminate\Support\Arr;
use Str;

class AppUrl
{
    /**
     * If host in .env file and current request did not match, but
     * we were able to find a matching custom domain in database.
     *
     * @var CustomDomain|null
     */
    public $matchedCustomDomain = null;

    /**
     * Url "app.url" config item was changed to dynamically.
     *
     * @var string|null
     */
    public $newAppUrl;

    /**
     * Whether hosts from APP_URL in .env file and current request match.
     * This will strip "www" and schemes from both and only compare hosts.
     *
     * @var bool
     */
    public $envAndCurrentHostsAreEqual;

    /**
     * @var string
     */
    public $htmlBaseUri;

    public function init()
    {
        $this->maybeDynamicallyUpdate();
        $this->registerHtmlBaseUri();
        return $this;
    }

    private function maybeDynamicallyUpdate()
    {
        $request = app('request');
        $requestHost = $request->getHost();

        $envParts = parse_url(config('app.url'));

        $schemeIsDifferent =
            $request->getScheme() !== Arr::get($envParts, 'scheme');
        $this->envAndCurrentHostsAreEqual =
            $this->getHostFrom($requestHost) ===
            $this->getHostFrom(Arr::get($envParts, 'host'));
        $hostsWithWwwAreEqual = $requestHost === Arr::get($envParts, 'host');
        $customDomainsEnabled = config('common.site.enable_custom_domains');
        $endsWithSlash = Str::endsWith(Arr::get($envParts, 'path'), '/');

        if (
            $this->envAndCurrentHostsAreEqual &&
            ($schemeIsDifferent || $endsWithSlash || !$hostsWithWwwAreEqual)
        ) {
            $this->newAppUrl =
                $request->getSchemeAndHttpHost() .
                rtrim(Arr::get($envParts, 'path'), '/');
            config(['app.url' => $this->newAppUrl]);
            // update social auth urls as well
            foreach (config('services') as $serviceName => $serviceConfig) {
                if (isset($serviceConfig['redirect'])) {
                    Config::set(
                        "services.$serviceName.redirect",
                        "$this->newAppUrl/secure/auth/social/$serviceName/callback",
                    );
                }
            }
        } elseif (!$this->envAndCurrentHostsAreEqual && $customDomainsEnabled) {
            $this->matchedCustomDomain = DB::table('custom_domains')
                ->where('host', $requestHost)
                ->orWhere('host', $request->getSchemeAndHttpHost())
                // if there are multiple domains with same host, get the one that has resource attached to it first
                ->orderBy('resource_id', 'desc')
                ->first();
            if ($this->matchedCustomDomain) {
                $this->newAppUrl = $request->getSchemeAndHttpHost();
                config(['app.url' => $this->newAppUrl]);
            }
        }
    }

    private function registerHtmlBaseUri()
    {
        $htmlBaseUri = '/';

        //get uri for html "base" tag
        if (substr_count(config('app.url'), '/') > 2) {
            $htmlBaseUri = parse_url(config('app.url'))['path'] . '/';
        }

        $this->htmlBaseUri = $htmlBaseUri;
    }

    /**
     * @return string
     */
    public function getRequestHost()
    {
        return $this->getHostFrom(app('request')->getHost());
    }

    public function requestHostMatches(
        $hostOrUrl,
        $subdomainMatch = false
    ): bool {
        $hostOrUrl = $this->getHostFrom($hostOrUrl);
        $requestHost = $this->getRequestHost();
        return $hostOrUrl === $requestHost ||
            ($subdomainMatch && Str::endsWith($requestHost, $hostOrUrl));
    }

    /*
     * Extract host from full or partial url.
     * This will remove scheme, port, "www", path and query params.
     */
    public function getHostFrom($hostOrUrl)
    {
        // if there's no scheme, add // so it's parsed properly
        if (!preg_match('/^([a-z][a-z0-9\-\.\+]*:)|(\/)/', $hostOrUrl)) {
            $hostOrUrl = '//' . $hostOrUrl;
        }

        $parts = parse_url($hostOrUrl);
        return preg_replace('/^www\./i', '', $parts['host']);
    }
}
