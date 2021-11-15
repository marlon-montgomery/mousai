<?php

namespace App\Services\Tracks;

use App\Album;
use App\Artist;
use Arr;
use Auth;
use Carbon\Carbon;
use Common\Files\Actions\CreateFileEntry;
use Common\Files\Actions\UploadFile;
use Common\Files\FileEntry;
use Common\Files\Traits\GetsEntryTypeFromMime;
use Common\Settings\Settings;
use getID3;
use getid3_lib;
use Illuminate\Http\Request;
use Str;

class TrackUploadResponseTransformer
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

    public function __construct(Request $request, Artist $artist, Album $album)
    {
        $this->request = $request;
        $this->artist = $artist;
        $this->album = $album;
    }

    public function transform(array $response): array
    {
        /** @var FileEntry $fileEntry */
        $fileEntry = $response["fileEntry"];

        if (!$fileEntry) {
            return $response;
        }

        $autoMatch =
            app(Settings::class)->get("uploads.autoMatch", true) &&
            (Auth::user()->hasPermission("music.update") ||
                Auth::user()->getRestrictionValue(
                    "music.create",
                    "artist_selection",
                ));

        $getID3 = new getID3();

        if ($upload = $this->request->file("file")) {
            $metadata = $getID3->analyze(
                $upload->getPathname(),
                $fileEntry->file_size,
                $fileEntry->name,
            );
        } else {
            $metadata = $getID3->analyze(
                null,
                $fileEntry->file_size,
                $fileEntry->name,
                $fileEntry->getDisk()->readStream($fileEntry->getStoragePath()),
            );
        }

        getid3_lib::CopyTagsToComments($metadata);

        $normalizedMetadata = array_map(function ($item) {
            return $item && is_array($item) ? Arr::first($item) : $item;
        }, Arr::except(Arr::get($metadata, "comments", []), [
            "music_cd_identifier",
            "text",
        ]));

        // store thumbnail
        if (isset($normalizedMetadata["picture"])) {
            $normalizedMetadata = $this->storeMetadataPicture(
                $normalizedMetadata,
            );
        }

        if (isset($metadata["playtime_seconds"])) {
            $normalizedMetadata["duration"] =
                floor($metadata["playtime_seconds"]) * 1000;
        }

        if (isset($normalizedMetadata["unsynchronised_lyric"])) {
            $lyric = preg_replace(
                '/[\n]/',
                "<br>",
                $normalizedMetadata["unsynchronised_lyric"],
            );
            $normalizedMetadata["lyrics"] = $lyric;
            unset($normalizedMetadata["unsynchronised_lyric"]);
        }

        if (isset($normalizedMetadata["genre"])) {
            $normalizedMetadata["genres"] = explode(
                ",",
                $normalizedMetadata["genre"],
            );
            unset($normalizedMetadata["genre"]);
        }

        if (isset($normalizedMetadata["artist"])) {
            $normalizedMetadata["artist_name"] = $normalizedMetadata["artist"];
            unset($normalizedMetadata["artist"]);
            if ($autoMatch) {
                $normalizedMetadata["artist"] = $this->artist->firstOrCreate([
                    "name" => $normalizedMetadata["artist_name"],
                ]);
            }
        }

        if (isset($normalizedMetadata["album"])) {
            $normalizedMetadata["album_name"] = $normalizedMetadata["album"];
            unset($normalizedMetadata["album"]);
            $autoMatchAlbum = filter_var(
                $this->request->get("autoMatchAlbum"),
                FILTER_VALIDATE_BOOLEAN,
            );
            if (
                $autoMatch &&
                $autoMatchAlbum &&
                isset($normalizedMetadata["artist"])
            ) {
                $album = $normalizedMetadata["artist"]
                    ->albums()
                    ->where("name", $normalizedMetadata["album_name"])
                    ->first();
                if (!$album) {
                    $album = $normalizedMetadata["artist"]->albums()->create([
                        "name" => $normalizedMetadata["album_name"],
                        "release_date" => Carbon::now(),
                        "image" => $normalizedMetadata["image"]["url"] ?? null,
                        "full_scraped" => true,
                        "auto_update" => false,
                        "owner_id" => Auth::id(),
                    ]);
                }
                $normalizedMetadata["album"] = $album;
            }
        }

        if (isset($normalizedMetadata["date"])) {
            $normalizedMetadata["release_date"] = Carbon::parse(
                $normalizedMetadata["date"],
            )->toDateString();
            unset($normalizedMetadata["date"]);
        }

        if (!isset($normalizedMetadata["title"])) {
            $name = pathinfo($fileEntry->name, PATHINFO_FILENAME);
            $normalizedMetadata["title"] = Str::title($name);
        }

        $response["metadata"] = $normalizedMetadata;
        return $response;
    }

    /**
     * @param array $normalizedMetadata
     * @return array
     */
    private function storeMetadataPicture($normalizedMetadata)
    {
        $mime = $normalizedMetadata["picture"]["image_mime"];
        $fileData = [
            "name" => "thumbnail.png",
            "file_name" => Str::random(40),
            "mime" => $mime,
            "type" => $this->getTypeFromMime($mime),
            "file_size" =>
                $normalizedMetadata["picture"]["datalength"] ??
                strlen($normalizedMetadata["picture"]["data"]),
            "extension" => last(explode("/", $mime)),
        ];

        $params = ["diskPrefix" => "track_image_media"];
        $fileEntry = app(CreateFileEntry::class)->execute($fileData, $params);
        app(UploadFile::class)->execute(
            "public",
            $normalizedMetadata["picture"]["data"],
            $params,
            $fileEntry,
        );
        unset($normalizedMetadata["picture"]);
        $normalizedMetadata["image"] = $fileEntry;
        return $normalizedMetadata;
    }
}
