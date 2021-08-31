<?php

namespace Common\Files\Actions;

class GetServerMaxUploadSize
{
    protected $configKeys = ['post_max_size', 'upload_max_filesize', 'memory_limit'];

    /**
     * @return array
     */
    public function execute()
    {
        $configValues = collect($this->configKeys)
            ->map(function($key) {
                $value = ini_get($key);
                return ['original' => $value, 'bytes' => $this->getBytes($value)];
            })->filter(function($value) {
                return $value['bytes'] > 0;
            });

        return $configValues->where('bytes', $configValues->min('bytes'))->first();
    }

    /**
     * @param int|string $value
     * @return int
     */
    protected function getBytes($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $metric = strtoupper(substr($value, -1));

        switch ($metric) {
            case 'K':
                return (int) $value * 1024;
            case 'M':
                return (int) $value * 1048576;
            case 'G':
                return (int) $value * 1073741824;
            default:
                return (int) $value;
        }
    }
}
