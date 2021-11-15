<?php

namespace Common\Core\Prerender;

use Common\Core\Contracts\AppUrlGenerator;
use Common\Pages\CustomPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseUrlGenerator implements AppUrlGenerator
{
    const SEPARATOR = '-';

    /**
     * @param array|CustomPage $page
     */
    public function page($page): string
    {
        $slug = slugify($page['slug']);
        return url("pages/{$page['id']}/$slug");
    }

    public function home(): string
    {
        return url('');
    }

    /**
     * @param Model|array $model
     */
    public function generate($model): string
    {
        $method =
            $model instanceof Model ? $model::MODEL_TYPE : $model['modelType'];
        return $this->$method($model);
    }

    public function __call(string $name, array $arguments): string
    {
        return url(Str::kebab($name));
    }
}
