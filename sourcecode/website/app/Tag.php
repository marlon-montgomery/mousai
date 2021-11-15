<?php

namespace App;

use Common\Tags\Tag as BaseTag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends BaseTag
{
    protected $hidden = [
        'type',
        'created_at',
        'updated_at',
    ];

    /**
     * @return MorphToMany
     */
    public function tracks()
    {
        return $this->morphedByMany(Track::class, 'taggable');
    }

    /**
     * @return MorphToMany
     */
    public function albums()
    {
        return $this->morphedByMany(Album::class, 'taggable');
    }
}
