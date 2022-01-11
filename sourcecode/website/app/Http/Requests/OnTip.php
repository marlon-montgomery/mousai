<?php namespace App\Http\Requests;

use Common\Core\BaseFormRequest;

/**
 * @property-read string|null $donor
 * @property-read string $donee
 * @property-read string $amount
 * @property-read string $amount_usd
 */
class OnTip extends BaseFormRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'donor' => 'nullable|string',
            'donee' => 'required|string',
            'amount' => 'required|string',
            'amount_usd' => 'required|string'
        ];
    }
}
