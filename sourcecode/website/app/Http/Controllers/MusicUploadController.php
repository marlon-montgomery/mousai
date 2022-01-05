<?php

namespace App\Http\Controllers;

use App\Album;
use App\Artist;
use Common\Core\BaseController;
use Common\Files\Actions\UploadFile;
use Common\Files\FileEntry;
use Common\Files\Traits\GetsEntryTypeFromMime;
use Illuminate\Http\Request;

class MusicUploadController extends BaseController
{
    use GetsEntryTypeFromMime;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Artist
     */
    private $artist;

    /**
     * @var Album
     */
    private $album;

    /**
     * @param Request $request
     * @param Artist $artist
     * @param Album $album
     */
    public function __construct(Request $request, Artist $artist, Album $album)
    {
        $this->request = $request;
        $this->artist = $artist;
        $this->album = $album;
    }

    public function upload()
    {
        $this->authorize('store', FileEntry::class);

        $this->validate($this->request, [
            'file' => 'required|file'
        ]);

        $fileEntry = $this->storePublicFile();


        return $this->success(['fileEntry' => $fileEntry, 'metadata' => $normalizedMetadata], 201);
    }

    /**
     * @return FileEntry
     */
    private function storePublicFile()
    {
        $uploadFile = $this->request->file('file');
        $params = $this->request->all();
        $params['diskPrefix'] = 'track_media';

        return app(UploadFile::class)->execute('public', $uploadFile, $params);
    }
}
