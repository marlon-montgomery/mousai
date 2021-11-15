<?php

namespace Common\Settings\Validators;

use Cache;
use Carbon\Carbon;
use Common\Settings\DotEnvEditor;
use Exception;
use Throwable;
use Illuminate\Support\Arr;

class CacheConfigValidator
{
    const KEYS = ['cache_driver'];

    public function fails($settings)
    {
        $this->setConfigDynamically($settings);

        try {
            $driverName = Arr::get($settings, 'cache_driver', config('cache.default'));
            $driver = Cache::driver($driverName);
            $driver->put('foo', 'bar', 1);
            if ($driver->get('foo') !== 'bar') {
                return $this->getDefaultErrorMessage();
            }
        } catch (Exception $e) {
            return $this->getErrorMessage($e);
        } catch (Throwable $e) {
            return $this->getErrorMessage($e);
        }
    }

    private function setConfigDynamically($settings)
    {
        app(DotEnvEditor::class)->write(Arr::except($settings, ['cache_driver']));
    }

    /**
     * @param Exception|Throwable $e
     * @return array
     */
    private function getErrorMessage($e)
    {
        $message = $e->getMessage();

        if (\Str::contains($message, 'apc_fetch')) {
            return ['cache_group' => "Could not enable APC. $message"];
        } else if (\Str::contains($message, 'Memcached')) {
            return ['cache_group' => "Could not enable Memcached. $message"];
        } else if (\Str::contains($message, 'Connection refused')) {
            return ['cache_group' => 'Could not connect to redis server.'];
        } else {
            return $this->getDefaultErrorMessage();
        }
    }

    /**
     * @return array
     */
    private function getDefaultErrorMessage()
    {
        return ['cache_group' => 'Could not enable this cache method.'];
    }
}
