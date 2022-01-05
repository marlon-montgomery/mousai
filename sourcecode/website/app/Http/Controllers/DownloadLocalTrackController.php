<?php

namespace App\Http\Controllers;

use App\Track;
use Common\Files\FileEntry;
use Common\Files\Response\FileResponseFactory;
use Illuminate\Http\Request;
use Str;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Common\Core\BaseController;
use Common\Settings\Settings;

class DownloadLocalTrackController extends BaseController
{
    /**
     * @var Track
     */
    private $track;

    /**
     * @var FileEntry
     */
    private $fileEntry;

    public function __construct(Track $track, FileEntry $fileEntry)
    {
        $this->track = $track;
        $this->fileEntry = $fileEntry;
    }

    public function download($id) {
        $track = $this->track->findOrFail($id);

        $this->authorize('download', $track);

        if ( ! $track->url) {
            abort(404);
        }

        preg_match('/.*?\/?storage\/track_media\/(.+?\.[a-z0-9]+)/', $track->url, $matches);

        // track is local
        if (isset($matches[1])) {
            $entry = $this->fileEntry->where('file_name', $matches[1])->firstOrFail();

            $ext = pathinfo($track->url, PATHINFO_EXTENSION);
            $trackName = str_replace('%', '', Str::ascii($track->name)).".$ext";
            $entry->name = $trackName;

            return app(FileResponseFactory::class)->create($entry, 'attachment');

        // track is remote
        } else {
            $response = response()->stream(function() use($track) {
                echo file_get_contents($track->url);
            });

            $path = parse_url($track->url, PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'mp3';

            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                "$track->name.$extension",
                str_replace('%', '', Str::ascii("$track->name.$extension"))
            );

            $response->headers->replace([
                'Content-Type' => 'audio/mpeg',
                'Content-Disposition' => $disposition,
            ]);

            return $response;
        }
    }
}
