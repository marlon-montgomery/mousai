<?php namespace App\Http\Requests;

use Common\Core\BaseFormRequest;

class ModifyAlbums extends BaseFormRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required', 'string', 'min:1', 'max:255',
            ],
            'spotify_popularity' => 'integer|min:1|max:100|nullable',
            'release_date'       => 'date_format:Y-m-d|nullable',
            'image'              => 'nullable',
            'tracks.*.name' => 'required|string|min:1|max:190',
            'artists'            => 'required|array|min:1',
            'artists.*'          => ['required', 'regex:/[0-9]+|CURRENT_USER/i'],
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => __('Artist already has album with this name.'),
            'artists.required' => __('Could not automatically determine album artists. Select artists manually.'),
        ];
    }
}
