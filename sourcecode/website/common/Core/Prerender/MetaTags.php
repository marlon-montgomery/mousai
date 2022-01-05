<?php

namespace Common\Core\Prerender;

use Common\Core\Contracts\AppUrlGenerator;
use Common\Core\Prerender\Actions\ReplacePlaceholders;
use Common\Settings\Settings;
use Illuminate\Contracts\Support\Arrayable;
use Arr;
use Str;

class MetaTags implements Arrayable
{
    /**
     * Tag types that can be edited by the user.
     */
    const EDITABLE_TAGS = ['og:title', 'og:description', 'keywords'];

    /**
     * Data for replacing meta tag config placeholders.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Meta tag config before generation.
     *
     * @var array
     */
    protected $tags = [];

    /**
     * Final tags for appending to site head.
     *
     * @var array
     */
    protected $generatedTags = [];

    /**
     * Namespace for current tag config. "artist.show".
     *
     * @var string
     */
    protected $namespace;

    /**
     * @var AppUrlGenerator
     */
    public $urls;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct($tags, $data, $namespace)
    {
        $this->namespace = $namespace;
        $tags = $this->overrideTagsWithUserValues($tags);
        $this->tags = array_merge($tags, config('seo.common'));
        $this->data = $data;
        $this->urls = app(AppUrlGenerator::class);
        $this->settings = app(Settings::class);
        $this->generatedTags = $this->generateTags();
    }

    public function toArray()
    {
        // remove all tags to which placeholders could not be replaced with actual content
        $tags = collect($this->getAll())
            ->map(function($tag) {
                // ld+json tags will contain child arrays
                $strings = array_filter($tag, function($value) {
                    return !is_array($value);
                });
                $content = implode($strings);
                $shouldRemove = Str::contains($content, '{{') && Str::contains($content, '}}');

                // if could not replace title placeholder, return app name as title instead
                if ($shouldRemove && $tag['nodeName'] === 'title') {
                    $tag['_text'] = config('app.name');
                    $shouldRemove = false;
                };
                if ($shouldRemove && Arr::get($tag, 'property') === 'og:title') {
                    $tag['content'] = config('app.name');
                    $shouldRemove = false;
                };

                return $shouldRemove ? null : $tag;
            });

        return $tags->filter()->values()->toArray();
    }

    public function getTitle()
    {
        return $this->get('og:title');
    }

    public function getDescription()
    {
        return $this->get('og:description');
    }

    public function getImage()
    {
        return url($this->get('og:image'));
    }

    /**
     * @param string $position
     * @return array
     */
    public function getMenu($position)
    {
        $menus = $this->settings->getJson('menus');
        $default = ['items' => []];

        return Arr::first($menus, function($menu) use($position) {
            return $menu['position'] === $position;
        }, $default);
    }

    public function get($value, $prop = 'property')
    {
        $tag = Arr::first($this->generatedTags, function($tag) use($prop, $value) {
            return Arr::get($tag, $prop) === $value;
        }, []);

        return Arr::get($tag, 'content');
    }

    public function getData($selector = null, $default = null)
    {
        return Arr::get($this->data, $selector) ?? $default;
    }

    public function getAll(): array
    {
        return $this->generatedTags;
    }

    /**
     * Convert specified tag config into a string.
     *
     * @param array $tag
     * @return string
     */
    public function tagToString($tag)
    {
        $string = '';

        foreach(Arr::except($tag, 'nodeName') as $key => $value) {
            $value = is_array($value) ? implode(',', $value) : $value;
            $string .= "$key=\"$value\" ";
        }

        return trim($string);
    }

    private function generateTags(): array
    {
        $tags = $this->tags;

        $tags = array_map(function($tag) {
            return $this->replacePlaceholders($tag);
        }, $tags);
        $tags = $this->removeEmptyValues($tags);
        $tags = $this->duplicateTags($tags);
        $tags = $this->escapeValues($tags);

        $tags = array_map(function($tag) {
            // set nodeName to <meta> tag, if not already specified
            if ( ! array_key_exists('nodeName', $tag)) {
                $tag['nodeName'] = 'meta';
            }
            return $tag;
        }, $tags);

        return $tags;
    }

    function removeEmptyValues(array &$array): array {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->removeEmptyValues($value);
            }
            if (empty($value)) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    private function replacePlaceholders(array $tag): array
    {
        $replacer = app(ReplacePlaceholders::class);

        // if tag does not have "content" or "_text" prop, we can continue
        if (array_key_exists('content', $tag)) {
            $tag['content'] = $replacer->execute($tag['content'], $this->data);
        } else if (array_key_exists('_text', $tag)) {
            $tag['_text'] = $replacer->execute($tag['_text'], $this->data);
        }

        return $tag;
    }

    private function escapeValues(array $tags): array
    {
        // only escape meta tags that have "content" property
        foreach ($tags as $key => $tag) {
            if (array_key_exists('content', $tag)) {
                $tags[$key]['content'] = str_replace('"', '&quot;', $tag['content']);
            }
        }

        return $tags;
    }

    /**
     * Create duplicate tags from generated tags.
     * (for example: canonical link from og:url)
     *
     * @param array $tags
     * @return array
     */
    private function duplicateTags($tags)
    {
        foreach ($tags as $tag) {
            if ( ! isset($tag['content'])) continue;

            if (Arr::get($tag, 'property') === 'og:url') {
                $tags[] = [
                    'nodeName' => 'link',
                    'rel' => 'canonical',
                    'href' => $tag['content']
                ];
            }

            if (Arr::get($tag, 'property') === 'og:title') {
                $tags[] = [
                    'nodeName' => 'title',
                    '_text' => ucfirst($tag['content']),
                ];
            }

            if (Arr::get($tag, 'property') === 'og:description') {
                $tags[] = [
                    'name' => 'description',
                    'content' => $tag['content'],
                ];
            }
        }

        return $tags;
    }

    private function overrideTagsWithUserValues(array $metaTags): array
    {
        $overrides = app(Settings::class)->all();
        foreach ($metaTags as $key => $tagConfig) {
            $property = Arr::get($tagConfig, 'property');
            $settingKey = "seo.{$this->namespace}.{$property}";
            if (array_search($property, self::EDITABLE_TAGS) !== false && array_key_exists($settingKey, $overrides)) {
                $metaTags[$key]['content'] = $overrides[$settingKey];
            }
        }

        return $metaTags;
    }
}
