<?php

namespace App\Services\Providers;

use Illuminate\Support\Collection;

interface ContentProvider
{
    /**
     * @return Collection
     */
    public function getContent();
}