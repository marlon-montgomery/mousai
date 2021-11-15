<?php

namespace Common\Core\Prerender\Actions;

use Common\Core\Contracts\AppUrlGenerator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReplacePlaceholders
{
    /**
     * @var AppUrlGenerator
     */
    private $urls;

    /**
     * @var array
     */
    private $allData;

    /**
     * @param AppUrlGenerator $urls
     */
    public function __construct(AppUrlGenerator $urls)
    {
        $this->urls = $urls;
    }

    /**
     * @param array|string $config
     * @param array $data
     * @return array|string
     */
    public function execute($config, $data)
    {
        $this->allData = $data;
        return $this->replace($config);
    }

    /**
     * @param array|string $config
     * @return array|string
     */
    private function replace($config)
    {
        if (is_array($config)) {
            if (array_key_exists('_ifNotNull', $config)) {
                if (is_null(Arr::get($this->allData, $config['_ifNotNull']))) {
                    return [];
                }
                unset($config['_ifExists']);
            }

            if (Arr::get($config, '_type') === 'loop') {
                return $this->replaceLoop($config);
            } else {
                return array_map(function($item) {
                    return $this->replace($item);
                }, $config);
            }
        } else {
            return $this->replaceString($config, $this->allData);
        }
    }

    /**
     * @param $config
     * @return array
     */
    private function replaceLoop($config)
    {
        $dataSelector = strtolower($config['dataSelector']);
        $loopData = Arr::get($this->allData, $dataSelector);

        // won't be able to access paginator data via dot notation
        // selector (items.data), need to extract it manually
        if ($loopData instanceof AbstractPaginator) {
            $loopData = $loopData->items();
        }
        $loopData = collect($loopData);

        // apply filter (if provided), filter will specify which array
        // prop of loop item should match what value. For example:
        // ['key' => 'pivot.department', 'value' => 'cast' will get
        // only cast from movie credits array instead of full credits
        if ($filter = Arr::get($config, 'filter')) {
            $loopData = $loopData->filter(function($loopItem) use($filter) {
                return Arr::get($loopItem, $filter['key']) === $filter['value'];
            });
        }

        if ($limit = Arr::get($config, 'limit')) {
            $loopData = $loopData->slice(0, $limit);
        }

        // if _type is "nested" we only need to return the first item so instead
        // of nested [['name' => 'foo'], ['name' => 'bar']] only ['name' => 'foo']
        if ($returnFirstOnly = Arr::get($config, 'returnFirstOnly')) {
            $loopData->slice(0, 1);
        }

        $generated = collect($loopData)->map(function($loopItem) use($config) {
            // make sure template can access data via dot notation (TAG.NAME)
            // so instead of passing just tag, pass ['tag' => $tag]
            $name = strtolower(class_basename($loopItem));
            return $this->replaceString($config['template'], [$name => $loopItem]);
        });

        return $returnFirstOnly ? $generated->first() : $generated->values()->toArray();
    }

    /**
     * @param string|string[] $template
     * @param array $originalData
     * @return string|string[]
     */
    private function replaceString($template, $originalData)
    {
        $data = [];
        foreach ($originalData as $key => $value) {
            $data[Str::lower($key)] = $value;
        }

        return preg_replace_callback('/{{([\w\.\-\?\:]+?)}}/', function($matches) use($data) {
            if ( ! isset($matches[1])) return $matches[0];

            $placeholder = $matches[1];

            // replace site name
            if ($placeholder === 'site_name') {
                return config('app.name');
            }

            // replace base url
            if ($placeholder === 'url.base') {
                return url('');
            }

            // replace by url generator url
            if (Str::startsWith($placeholder, 'url.')) {
                // "url.movie" => "movie"
                $resource = str_replace('url.', '', $placeholder);
                // "new_releases" => "newReleases"
                $method = Str::camel($resource);
                return $this->urls->$method(Arr::get($data, $resource) ?: $data);
            }

            // replace placeholder with actual value.
            // supports dot notation: 'artist.bio.text' as well as ?:
            $replacement = $this->findUsingDotNotation($data, $placeholder);

            // prefix relative image urls with base site url
            if ($replacement && Str::startsWith($replacement, 'storage/')) {
                $replacement = config('app.url') . "/$replacement"; url();
            }

            // return null if we could not replace placeholder
            if ( ! $replacement) {
                return null;
            }

            return Str::limit(strip_tags($this->replaceString($replacement, $data), '<br>'), 400);
        }, $template);
    }

    /**
     * @param array $data
     * @param string $item
     * @return mixed|void
     */
    private function findUsingDotNotation($data, $item)
    {
        foreach (explode('?:', $item) as $itemVariant) {
            if ($value = Arr::get($data, $itemVariant)) {
                return $value;
            }
        }
    }
}
