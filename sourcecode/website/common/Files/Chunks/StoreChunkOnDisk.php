<?php

namespace Common\Files\Chunks;

use Exception;
use File;
use Illuminate\Http\UploadedFile;

class StoreChunkOnDisk
{
    use HandlesUploadChunks;

    public function execute(string $fingerprint, int $chunkIndex, UploadedFile $chunkFile)
    {
        $chunkDir = $this->chunkDir($fingerprint);

        $chunkPath = "$chunkDir/$chunkIndex";

        try {
            File::ensureDirectoryExists($chunkDir);
        } catch (Exception $e) {
            //
        }

        $stream = fopen($chunkPath, 'w+b');
        $resource = fopen($chunkFile->getRealPath(), 'r+');
        stream_copy_to_stream($resource, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        if (is_resource($resource)) {
            fclose($resource);
        }
    }
}
