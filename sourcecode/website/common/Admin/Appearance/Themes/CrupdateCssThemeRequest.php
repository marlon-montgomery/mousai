<?php

namespace Common\Admin\Appearance\Themes;

use Auth;
use Common\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

class CrupdateCssThemeRequest extends BaseFormRequest
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
        $ignore = $this->getMethod() === 'PUT' ? $this->route('css_theme')->id : '';
        $userId = $this->route('css_theme') ? $this->route('css_theme')->user_id : Auth::id();

        return [
            'name' => [
                $required, 'string', 'min:3',
                Rule::unique('css_themes')->where('user_id', $userId)->ignore($ignore)
            ],
            'is_dark' => 'boolean',
            'default_dark' => 'boolean',
            'default_light' => 'boolean',
            'colors' => 'array',
        ];
    }
}
