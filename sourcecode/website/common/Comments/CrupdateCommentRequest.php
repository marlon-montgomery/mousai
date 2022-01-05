<?php

namespace Common\Comments;

use Auth;
use Common\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

class CrupdateCommentRequest extends BaseFormRequest
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
        $ignore = $this->getMethod() === 'PUT' ? $this->route('comment')->id : '';
        $userId = $this->route('comment') ? $this->route('comment')->user_id : Auth::id();

        return [
            'content' => 'required|string|max:1000|min:4'
        ];
    }
}
