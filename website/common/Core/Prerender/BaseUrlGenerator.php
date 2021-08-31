<?php

namespace Common\Core\Prerender;

use Common\Pages\CustomPage;
use Common\Core\Contracts\AppUrlGenerator;
use Illuminate\Support\Str;

class BaseUrlGenerator implements AppUrlGenerator
{
    const SEPARATOR = '-';

    /**
     * @param array|CustomPage $page
     * @return string
     */
    public function page($page)
    {
        $slug = slugify($page['slug']);
        return url("pages/{$page['id']}/$slug");
    }

    /**
     * @return string
     */
    public function home()
    {
        return url('');
    }

    /**
     * Generate url based on called method name, if there's no specific method.
     *
     * @param string $name
     * @param array $arguments
     * @return string
     */
    public function __call($name, $arguments)
    {
        return url(Str::kebab($name));
    }
}
