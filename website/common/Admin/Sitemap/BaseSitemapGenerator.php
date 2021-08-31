<?php

namespace Common\Admin\Sitemap;

use Carbon\Carbon;
use Common\Core\Contracts\AppUrlGenerator;
use Common\Pages\CustomPage;
use File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseSitemapGenerator
{
    /**
     * @var integer
     */
    protected $queryChunkSize = 1000;

    /**
     * @var string
     */
    protected $currentDateTimeString;

    /**
     * @var int
     */
    protected $currentResourceSitemapCount = 0;

    /**
     * @var string
     */
    protected $currentXml;

    /**
     * @var int
     */
    protected $currentLineCount = 0;

    public function __construct()
    {
        @ini_set('memory_limit', '160M');
        @ini_set('max_execution_time', 7200);
        $this->currentDateTimeString = Carbon::now()->toDateTimeString();
    }

    protected function getAppQueries(): array
    {
        return [];
    }

    protected function getAppStaticUrls(): array
    {
        return [];
    }

    public function generate()
    {
        $index = [];

        $queries = array_merge([
            app(CustomPage::class)->select(['id', 'title', 'slug']),
        ], $this->getAppQueries());

        foreach ($queries as $query) {
            $resourceName = str_replace('_', '-', $query->getModel()->getTable());
            $index[$resourceName] = $this->createSitemapForResource($query, $resourceName);
        }

        $this->makeStaticMap();
        $this->makeIndex($index);
    }

    protected function createSitemapForResource(Builder $model, string $name): int
    {
        $model->orderBy('id')->chunk($this->queryChunkSize, function($records) use($name) {
            foreach ($records as $record) {
                $this->addNewLine(
                    $this->getModelUrl($record),
                    $this->getModelUpdatedAt($record),
                    $name
                );
            }
        });

        if ($this->currentLineCount) {
            $this->save("$name-sitemap-{$this->currentResourceSitemapCount}");
        }

        $numberOfSitemapsGenerated = $this->currentResourceSitemapCount;

        $this->currentResourceSitemapCount = 0;
        $this->currentLineCount = 0;

        return $numberOfSitemapsGenerated;
    }

    protected function addNewLine(string $url, string $updatedAt, string $name)
    {
        if ( ! $this->currentXml) {
            $this->startNewXmlFile();
        }

        if ($this->currentLineCount === 50000) {
            $this->save("$name-sitemap-{$this->currentResourceSitemapCount}");
            $this->startNewXmlFile();
        }

        $updatedAt = $this->formatDate($updatedAt);

        $line = "\t"."<url>\n\t\t<loc>".htmlspecialchars($url)."</loc>\n\t\t<lastmod>".$updatedAt."</lastmod>\n\t\t<changefreq>weekly</changefreq>\n\t\t<priority>1.00</priority>\n\t</url>\n";

        $this->currentXml .= $line;

        $this->currentLineCount++;
    }

    protected function save(string $fileName)
    {
        $this->currentXml .= "\n</urlset>";

        File::ensureDirectoryExists(public_path("storage/sitemaps"));
        File::put(public_path("storage/sitemaps/$fileName.xml"), $this->currentXml);

        $this->currentXml = null;
        $this->currentLineCount = 0;
        $this->currentResourceSitemapCount++;
    }

    protected function startNewXmlFile()
    {
        $this->currentXml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
    }

    protected function makeStaticMap(): void
    {
        $urls = array_merge([
            '', 'login', 'register',
        ], $this->getAppStaticUrls());

        $urls = array_map(function($data) {
            if (is_string($data)) {
                return ['path' => $data, 'updated_at' => $this->currentDateTimeString];
            } else {
                return $data;
            }
        }, $urls);

        foreach ($urls as $url) {
            $this->addNewLine(url($url['path']), $url['updated_at'], 'static-urls');
        }

        $this->save("static-urls-sitemap");
    }

    protected function makeIndex(array $index): void
    {
        $baseUrl = url('storage/sitemaps');

        $string = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($index as $resourceName => $resourceSitemapCount) {
            for ($i=0; $i <= $resourceSitemapCount; $i++) {
                $url = "{$baseUrl}/{$resourceName}-sitemap-$i.xml";
                $string .= "\t<sitemap>\n"."\t\t<loc>$url</loc>\n"."\t\t<lastmod>{$this->formatDate()}</lastmod>\n"."\t</sitemap>\n";
            }
        }

        $string .= "\t<sitemap>\n\t\t<loc>{$baseUrl}/static-urls-sitemap.xml</loc>\n\t\t<lastmod>{$this->formatDate()}</lastmod>\n\t</sitemap>\n";

        $string .= '</sitemapindex>';

        File::put(public_path('storage/sitemaps/sitemap-index.xml'), $string);
    }

    protected function getModelUrl(Model $model): string
    {
        $resourceName = strtolower(class_basename($model));
        return app(AppUrlGenerator::class)->$resourceName($model);
    }


    protected function formatDate(string $date = null): string
    {
        if ( ! $date) $date = $this->currentDateTimeString;
        return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    protected function getModelUpdatedAt(Model $model): string
    {
        return ( ! $model->updated_at || $model->updated_at === '0000-00-00 00:00:00')
            ? $this->currentDateTimeString
            : $model->updated_at;
    }
}
