<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = [
        'id',
        'user_id',
        'artist_id',
        'created_at',
        'updated_at',
    ];

    public function getHeaderColorsAttribute($value)
    {
        if ($value) {
            return json_decode($value, true);
        } else {
            return [];
        }
    }

    public function setHeaderColorsAttribute($value)
    {
        if ( ! is_string($value)) {
            $value = json_encode($value);
        }
        $this->attributes['header_colors'] = $value;
    }
}
