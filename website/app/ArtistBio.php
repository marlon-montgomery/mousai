<?php

namespace App;

use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\ArtistBio
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Eloquent
 */
class ArtistBio extends Model
{
    protected $guarded = ['id'];

     protected $casts = [
         'id' => 'integer',
         'artist_id' => 'integer',
     ];
}
