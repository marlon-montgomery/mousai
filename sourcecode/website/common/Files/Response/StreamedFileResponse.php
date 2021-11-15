<?php

namespace Common\Files\Response;

use Common\Files\FileEntry;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedFileResponse implements FileResponse
{
    /**
     * @param FileEntry $entry
     * @param array $options
     * @return mixed
     */
    public function make(FileEntry $entry, $options)
    {
        $path = $entry->getStoragePath($options['useThumbnail']);
        $response = new StreamedResponse;
        $disposition = $response->headers->makeDisposition(
            $options['disposition'], $entry->getNameWithExtension(), str_replace('%', '', Str::ascii($entry->getNameWithExtension()))
        );

        $fileSize = $options['useThumbnail'] ? $entry->getDisk()->size($path) : $entry->file_size;

        $response->headers->replace([
            'Content-Type' => $entry->mime,
            'Content-Length' => $fileSize,
            'Content-Disposition' => $disposition,
        ]);
        $response->setCallback(function () use ($entry, $path) {
            try {
                $stream = $entry->getDisk()->readStream($path);
            } catch (FileNotFoundException $e) {
                abort(404);
            }
            
            while ( ! feof($stream)) {
                echo fread($stream, 2048);
            }
            fclose($stream);
        });
        return $response;
    }
}
