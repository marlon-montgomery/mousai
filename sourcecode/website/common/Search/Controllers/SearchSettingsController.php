<?php

namespace Common\Search\Controllers;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient as Algolia;
use App\User;
use Artisan;
use Common\Billing\BillingPlan;
use Common\Billing\Subscription;
use Common\Core\BaseController;
use Common\Domains\CustomDomain;
use Common\Files\FileEntry;
use Common\Pages\CustomPage;
use Common\Search\Drivers\Mysql\MysqlFullTextIndexer;
use Common\Search\Searchable;
use Common\Tags\Tag;
use Common\Workspaces\Workspace;
use Exception;
use Laravel\Scout\Console\ImportCommand;
use MeiliSearch\Client;
use Str;

class SearchSettingsController extends BaseController
{
    public static function searchableModels(): array
    {
        $appSearchableModels = config('searchable_models');
        $commonSearchableModels = [
            CustomPage::class,
            User::class,
            FileEntry::class,
            Tag::class,
        ];

        if (config('common.site.workspaces_integrated')) {
            $commonSearchableModels[] = Workspace::class;
        }

        if (config('common.site.billing_integrated')) {
            $commonSearchableModels[] = BillingPlan::class;
            $commonSearchableModels[] = Subscription::class;
        }

        if (config('common.site.enable_custom_domains')) {
            $commonSearchableModels[] = CustomDomain::class;
        }

        return array_merge($appSearchableModels ?? [], $commonSearchableModels);
    }

    public function getSearchableModels()
    {
        $models = $this->searchableModels();

        $models = array_map(function (string $model) {
            return [
                'model' => $model,
                'name' => Str::plural(last(explode('\\', $model))),
            ];
        }, $models);

        return $this->success(['models' => $models]);
    }

    public function import()
    {
        $this->middleware('isAdmin');
        @ini_set('memory_limit', '-1');
        @set_time_limit(0);

        if ($selectedDriver = request('driver')) {
            config()->set('scout.driver', $selectedDriver);
        }
        $driver = config('scout.driver');

        $models = request('model')
            ? [request('model')]
            : self::searchableModels();

        if ($driver === 'mysql') {
            foreach ($models as $model) {
                app(MysqlFullTextIndexer::class)->createOrUpdateIndex($model);
            }
        } elseif ($driver === 'meilisearch') {
            $this->configureMeilisearchIndices($models);
        } elseif ($driver === 'algolia') {
            $this->configureAlgoliaIndices($models);
        }

        $this->importUsingDefaultScoutCommand($models);

        return $this->success(['output' => nl2br(Artisan::output())]);
    }

    private function importUsingDefaultScoutCommand(array $models)
    {
        Artisan::registerCommand(app(ImportCommand::class));
        foreach ($models as $model) {
            $model = addslashes($model);
            Artisan::call("scout:import \"$model\"");
        }
    }

    private function configureAlgoliaIndices(array $models)
    {
        $config = SearchConfig::create(
            config('scout.algolia.id'),
            config('scout.algolia.secret'),
        );

        $algolia = Algolia::createWithConfig($config);
        foreach ($models as $model) {
            $filterableFields = $model::filterableFields();

            // keep ID searchable as there are issues with scout otherwise
            if (($key = array_search('id', $filterableFields)) !== false) {
                unset($filterableFields[$key]);
            }

            /**
             * @var Searchable $model
             */
            $model = new $model();
            $indexName = $model->searchableAs();
            $algolia->initIndex($indexName)->setSettings([
                'attributesForFaceting' => array_map(function ($field) {
                    return "filterOnly($field)";
                }, $filterableFields),
            ]);
        }
    }

    private function configureMeilisearchIndices(array $models): void
    {
        foreach ($models as $modelName) {
            /**
             * @var Searchable $model
             */
            $model = new $modelName();
            $indexName = $model->searchableAs();
            $index = app(Client::class)->index($indexName);

            if ($modelConfig = config("search.meilisearch.$modelName")) {
                $index->updateSettings($modelConfig);
            }

            $searchableFields = array_merge(['id'], $model->getSearchableKeys());
            $displayedFields = $searchableFields;
            try {
                app(Client::class)
                    ->index($indexName)
                    ->delete();
            } catch (Exception $e) {
                //
            }
            app(Client::class)
                ->index($indexName)
                ->updateSearchableAttributes($searchableFields);
            app(Client::class)
                ->index($indexName)
                ->updateFilterableAttributes($model::filterableFields());
            app(Client::class)
                ->index($indexName)
                ->updateDisplayedAttributes($displayedFields);
        }
    }
}
