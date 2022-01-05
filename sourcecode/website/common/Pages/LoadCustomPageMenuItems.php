<?php

namespace Common\Pages;

use Illuminate\Support\Collection;

class LoadCustomPageMenuItems
{
    /**
     * @return Collection
     */
    public function execute()
    {
        return app(CustomPage::class)
            ->limit(20)
            ->where('type', 'default')
            ->get()
            ->map(function(CustomPage $page) {
                return [
                    'label' => $page->title ?: $page->slug,
                    'action' => "pages/{$page->id}/{$page->slug}",
                    'model_id' => $page->id,
                    'type' => 'route'
                ];
            });
    }
}