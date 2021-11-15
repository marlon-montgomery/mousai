<?php

namespace App;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $artist_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Eloquent
 */
class ProfileImage extends Model
{
    protected $guarded = ['id'];

     protected $casts = [
         'id' => 'integer',
         'artist_id' => 'integer',
         'user_id' => 'integer',
     ];

    protected $visible = [
        'url' => 'url',
    ];
}
