<?php

namespace App\Http\Requests;

use Auth;
use Common\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

class CrupdateBackstageRequestRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $required = $this->getMethod() === 'POST' ? 'required' : '';
        $ignore = $this->getMethod() === 'PUT' ? $this->route('backstageRequest')->id : '';
        $userId = $this->route('backstageRequest') ? $this->route('backstageRequest')->user_id : Auth::id();

        $rules = [
            'artist_name' => [
                $required, 'string', 'min:3',
            ],
            'data' => 'required|array'
        ];

        if ($this->request->get('type') === 'become-artist') {
            $rules['artist_name'][] = Rule::unique('backstage_requests')->where('user_id', $userId);
        }

        return $rules;
    }
}
