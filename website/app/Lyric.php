<?php

namespace App;

use Common\Search\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lyric extends Model
{
    use Searchable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Track this lyric belongs to.
     *
     * @return BelongsTo
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
