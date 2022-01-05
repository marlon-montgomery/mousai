<?php

use Cocur\Slugify\Slugify;
use Illuminate\Http\Request;

if (!function_exists('slugify')) {
    /**
     * @param  string  $title
     * @param  string  $separator
     * @return string
     */
    function slugify($title, $separator = '-')
    {
        $slugified = (new Slugify())->slugify($title, $separator);
        // $slugified = Str::slug($title, $separator);

        if (!$slugified) {
            $slugified = strtolower(
                preg_replace('/[\s_]+/', $separator, $title),
            );
        }

        return $slugified;
    }
}

if (!function_exists('castToBoolean')) {
    /**
     * @param mixed $string
     * @return bool|null|string
     */
    function castToBoolean($string)
    {
        switch ($string) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            default:
                return (string) $string;
        }
    }
}

if (!function_exists('modelTypeToNamespace')) {
    function modelTypeToNamespace(string $modelType): string
    {
        if (Str::contains($modelType, 'App')) {
            return $modelType;
        }
        return 'App\\' . ucfirst($modelType);
    }
}

if (!function_exists('getIp')) {
    function getIp(): string
    {
        foreach (
            [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR',
            ]
            as $key
        ) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (
                        filter_var(
                            $ip,
                            FILTER_VALIDATE_IP,
                            FILTER_FLAG_NO_PRIV_RANGE |
                                FILTER_FLAG_NO_RES_RANGE,
                        ) !== false
                    ) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip();
    }
}
