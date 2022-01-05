<?php

namespace Common\Files\Traits;

use File;
use Illuminate\Support\Arr;

trait TransformsFileEntryResponse
{
    /**
     * @param array $response
     * @param array $params
     * @return array
     */
    protected function transformFileEntryResponse($response, $params)
    {
        $path = config_path('common/file-entry-transformers.php');
        $transformers = [];


        if (File::exists($path)) {
            $transformers = File::getRequire($path);
        }

        foreach ($transformers as $diskPrefix => $transformer) {
            if ($diskPrefix === '*' || $diskPrefix === Arr::get($params, 'diskPrefix')) {
                $response = app($transformer)->transform($response);
            }
        }

        return $response;
    }
}
