<?php

namespace Common\Auth\Requests;

use Common\Core\BaseFormRequest;

class SocialAuthBitclout extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jwt' => 'required',
            'publicKey' => 'required',
        ];
    }
}
