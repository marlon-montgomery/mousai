<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfileLink extends Model
{
    protected $guarded = ['id'];

    protected $visible = [
        'url', 'title'
    ];

    public function getUrlAttribute($value)
    {
        return parse_url($value, PHP_URL_SCHEME) === null ? "https://$value" : $value;
    }
}
