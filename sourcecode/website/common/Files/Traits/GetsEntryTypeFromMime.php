<?php

namespace Common\Files\Traits;

trait GetsEntryTypeFromMime
{
    /**
     * Get type of file entry from specified mime.
     *
     * @param string $mime
     * @return string
     */
    protected function getTypeFromMime($mime)
    {
        $default = explode('/', $mime)[0];

        switch ($mime) {
            case 'application/x-zip-compressed':
            case 'application/zip':
                return 'archive';
            case 'application/pdf':
                return 'pdf';
            case 'vnd.android.package-archive':
                return 'android package';
            case \Str::contains($mime, ['xls', 'excel', 'spreadsheetml']):
                return 'spreadsheet';
            case \Str::contains($mime, 'photoshop'):
                return 'photoshop';
            case \Str::contains($mime, 'officedocument.presentation'):
                return 'powerPoint';
            case \Str::contains($mime, ['application/msword', 'wordprocessingml.document']):
                return 'word';
            case \Str::contains($mime, ['postscript', 'x-eps']):
                return 'postscript';
            default:
                return $default === 'application' ? 'file' : $default;
        }
    }
}
