<?php

namespace App\Services\Artists;

use App\Artist;
use App\Genre;
use Arr;

class CrupdateArtist
{
    /**
     * @var Artist
     */
    private $artist;

    /**
     * @var Genre
     */
    private $genre;

    public function __construct(Artist $artist, Genre $genre)
    {
        $this->artist = $artist;
        $this->genre = $genre;
    }

    public function execute($data, Artist $artist = null): Artist
    {
        if ( ! $artist) {
            $artist = $this->artist->newInstance();
        }

        $artist->fill([
            'name' => $data['name'],
            'verified' => $data['verified'] ?? false,
            'image_small' => $data['image_small'],
            'auto_update' => $data['auto_update'] ?? false,
            'spotify_id' => $data['spotify_id'] ?? Arr::get($artist, 'spotify_id'),
        ])->save();

        $genreIds = $this->genre->insertOrRetrieve(Arr::get($data, 'genres'))->pluck('id');
        $artist->genres()->sync($genreIds);

        $artist->profile()->updateOrCreate(
            ['artist_id' => $artist->id],
            [
                'description' => $data['description'] ?? null,
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
            ]
        );

        $artist->profileImages()->delete();
        $profileImages = array_map(function($img) {
            return is_string($img) ? ['url' => $img] : $img;
        }, array_filter($data['profile_images']));
        $artist->profileImages()->createMany($profileImages);

        if (array_key_exists('links', $data)) {
            $artist->links()->delete();
            $artist->links()->createMany($data['links']);
        }

        return $artist->load('albums.tracks', 'genres', 'profile', 'profileImages', 'links');
    }
}
