<?php namespace App\Http\Requests;

use Auth;
use Common\Core\BaseFormRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class ModifyTracks extends BaseFormRequest
{
    public function messages()
    {
        return [
            'artists.required' => [
                __('Could not automatically determine track artists. Select artists manually.'),
            ]
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        $trackId = $this->route('id');

        $name = ['required', 'string', 'min:1', 'max:255'];

        if ($this->request->get('album_id')) {
            $name[] = Rule::unique('tracks')->where(function(Builder $query) {
                $query->where('album_id', $this->request->get('album_id'));
            })->ignore($trackId);
        }

        return [
            'name' => $name,
            'number'             => 'required_with:album_id|min:1',
            'album_name'         => 'required_with:album_id|min:1|max:255',
            'duration'           => 'required|integer|min:1',
            'spotify_popularity' => 'min:1|max:100|nullable',
            'album_id'           => 'integer|min:1|exists:albums,id',
            'artists'            => 'required|array|min:1',
            'artists.*'          => ['required', 'regex:/[0-9]+|CURRENT_USER/i'],
        ];
    }
}
