<?php

namespace Common\Files\Chunks;

use Auth;

trait HandlesUploadChunks
{
    protected static $finalFileName = 'final-file';

    /**
     * @param string $clientFingerprint
     * @return string
     */
    protected function getFingerprint($clientFingerprint)
    {
        $userId = Auth::id();
        return md5($clientFingerprint . "$userId");
    }

    protected function chunksRootDir()
    {
        return storage_path('app/chunks');
    }

    protected function chunkDir($clientFingerprint)
    {
        return "{$this->chunksRootDir()}/{$this->getFingerprint($clientFingerprint)}";
    }
}
