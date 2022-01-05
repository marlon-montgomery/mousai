<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Repost extends Model
{
    protected $guarded = ['id'];

    /**
     * @return BelongsTo
     */
    public function repostable()
    {
        return $this->morphTo()
            ->withCount('likes', 'reposts');
    }

//    public function getRepostableTypeAttribute($value)
//    {
//        if ($value === Album::class) {
//            return AlbumWithTracks::class;
//        } else {
//            return $value;
//        }
//    }
}
