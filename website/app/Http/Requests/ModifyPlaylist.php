<?php namespace App\Http\Requests;

use Auth;
use Common\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

class ModifyPlaylist extends BaseFormRequest
{
    public function messages()
    {
        return [
            "name.unique" =>
                "You have already created a playlist with this name.",
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            "name" => [
                "string",
                "min:3",
                "max:250",
                Rule::unique("playlists", "name")
                    ->where("owner_id", Auth::id())
                    ->ignore($this->route("playlist")->id ?? null),
            ],
            "description" => "min:20|max:170|nullable",
            "public" => "boolean",
            "collaborative" => "boolean",
        ];

        if ($this->method() === "POST") {
            array_unshift($rules['name'], 'required');
        }

        return $rules;
    }
}
