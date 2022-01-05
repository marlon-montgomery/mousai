<?php

namespace Common\Files\Response;

use Carbon\Carbon;
use Common\Files\FileEntry;

class RemoteFileResponse implements FileResponse
{
    /**
     * @param FileEntry $entry
     * @param array $options
     * @return mixed
     */
    public function make(FileEntry $entry, $options)
    {
        if ($options['disposition'] === 'attachment') {
            $fileName = rawurlencode($entry->name);
            return $this->getTemporaryUrl($entry, $options,   [
                'ResponseContentType' => 'application/octet-stream',
                'ResponseContentDisposition' => "attachment;filename={$fileName}",
            ]);
        } else {
            if (config('common.site.use_presigned_s3_urls')) {
              return $this->getTemporaryUrl($entry, $options, [
                  'ResponseContentType' => $entry->mime,
              ]);
            } else {
                return redirect($entry->getDisk()->url($entry->getStoragePath($options['useThumbnail'])));
            }
        }
    }

    private function getTemporaryUrl(FileEntry $entry, array $entryOptions, array $urlOptions)
    {
        return redirect($entry->getDisk()->temporaryUrl(
            $entry->getStoragePath($entryOptions['useThumbnail']),
            Carbon::now()->addMinutes(5),
            $urlOptions
        ));
    }
}
