<?php

namespace App\Http\Requests;

use Common\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

class CrupdateChannelRequest extends BaseFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $required = $this->getMethod() === 'POST' ? 'required' : '';
        $ignore = $this->getMethod() === 'PUT' ? $this->route('channel')->id : '';

        return [
            'name' => [
                $required, 'string', 'min:3',
                Rule::unique('channels')->ignore($ignore)
            ],
        ];
    }
}
