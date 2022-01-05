<?php

namespace Common\Validation\Validators;

class MultiDateFormatValidator
{
    /**
     * @param string $attribute
     * @param string $value
     * @param array $formats
     * @return bool
     */
    public function validate($attribute, $value, $formats)
    {
        foreach($formats as $format) {
            $parsed = date_parse_from_format($format, $value);
            if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                return true;
            }
        }
        return false;
    }
}
