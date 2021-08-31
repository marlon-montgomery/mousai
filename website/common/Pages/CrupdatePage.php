<?php

namespace Common\Pages;

use Arr;
use Auth;
use Common\Workspaces\ActiveWorkspace;

class CrupdatePage
{
    public function execute(CustomPage $page, array $data): CustomPage
    {
        $attributes = [
            'title' => $data['title'],
            'body' => $data['body'],
            'slug' => Arr::get($data, 'slug') ?: Arr::get($data, 'title'),
            'user_id' => Auth::id(),
            'hide_nav' => $data['hide_nav'] ?? false,
            'workspace_id' => app(ActiveWorkspace::class)->id,
        ];

        $page->fill($attributes)->save();

        return $page;
    }
}
