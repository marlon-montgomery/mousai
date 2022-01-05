<?php namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Common\Core\BaseFormRequest;

class ModifyArtists extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $artist = $this->route('artist');

        $rules = [
            'name' => [
                'required', 'string', 'min:1', 'max:255',
                Rule::unique('artists')->ignore($artist)
            ],
            'country' => 'nullable|string|min:2|max:100',
            'city' => 'nullable|string|min:2|max:100',
            'spotify_popularity' => 'integer|min:1|max:100|nullable',
            'spotify_followers' => 'integer|min:0|nullable',
            'image_small' => 'string|min:1|max:255|nullable',
            'image_large' => 'string|min:1|max:255|nullable',
            'description' => 'nullable|string',
            'genres' => 'array',
        ];

        if ($this->method() === 'POST') {
            $rules = array_merge($rules, [
                'albums' => 'array',
                'albums.*.name' => 'required|string|min:1|max:255',
                'albums.*.release_date' => 'string|min:1|max:255',
                'albums.*.image' => 'string|min:1|max:255',
                'albums.*.artist_id' => 'integer|exists:artists,id',
                'albums.*.spotify_popularity' => 'integer|min:1|max:100',

                'albums.*.tracks' => 'array',
                'albums.*.tracks.*.name' => 'required|string|min:1|max:255',
                'albums.*.tracks.*.album_name' => 'string|min:1|max:255',
                'albums.*.tracks.*.number' => 'required|integer|min:1',
                'albums.*.tracks.*.duration' => 'required|integer|min:1',
                'albums.*.tracks.*.artists' => 'string|nullable',
                'albums.*.tracks.*.youtube_id' => 'string|min:1|max:255',
                'albums.*.tracks.*.spotify_popularity' => 'integer|min:1|max:100',
                'albums.*.tracks.*.album_id' => 'integer|min:1',
                'albums.*.tracks.*.url' => 'string|min:1|max:255',
            ]);
        }

        return $rules;
    }
}
