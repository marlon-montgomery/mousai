<?php

namespace Common\Files\Chunks;

use Common\Files\Traits\TransformsFileEntryResponse;
use File;

class GetAlreadyUploadedChunks
{
    use HandlesUploadChunks, TransformsFileEntryResponse;

    public function execute(string $fingerprint, int $totalChunks, string  $originalName, array $params): array
    {
        $chunkDir = $this->chunkDir($fingerprint);

        $response = ['uploadedChunks' => [], 'fileEntry' => null];

        if (File::exists($chunkDir)) {
            $response['uploadedChunks'] = $this->loadAlreadyUploadedChunks($chunkDir);
            if (count($response['uploadedChunks']) === $totalChunks) {
                $response['fileEntry'] = app(AssembleFileFromChunks::class)->execute(
                    $fingerprint, $originalName, $params
                );
            }

            $response = $this->transformFileEntryResponse($response, $params);
        }

        return $response;
    }

    private function loadAlreadyUploadedChunks(string $chunksDir): array
    {
        $chunkFilePaths = File::files($chunksDir);
        $chunks = array_map(function($path) {
            $size = filesize($path);
            $number = basename($path);
            // exclude corrupted chunks and fully assembled file
            if ($number === self::$finalFileName || $size <= 0) {
                return null;
            }
            return ['number' => (int) $number, 'size' => $size];
        }, $chunkFilePaths);
        $chunks = array_filter($chunks);
        asort($chunks, SORT_REGULAR);

        return array_values($chunks);
    }
}
