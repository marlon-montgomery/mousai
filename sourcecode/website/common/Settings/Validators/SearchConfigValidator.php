<?php

namespace Common\Settings\Validators;

use App\User;
use Arr;
use Exception;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Matchish\ScoutElasticSearch\ElasticSearchServiceProvider;
use Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine;
use PDOException;
use Throwable;

class SearchConfigValidator
{
    const KEYS = ['scout_driver'];

    public function fails($settings)
    {
        $engineName = Arr::get(
            $settings,
            'scout_driver',
            config('scout.driver'),
        );
        $manager = app(EngineManager::class);

        if (isset($settings['algolia_app_id'])) {
            config()->set('scout.algolia.id', $settings['algolia_app_id']);
        }
        if (isset($settings['algolia_secret'])) {
            config()->set('scout.algolia.secret', $settings['algolia_secret']);
        }

        if (
            $engineName === 'mysql' &&
            Arr::get($settings, 'scout_mysql_mode') !== 'fulltext'
        ) {
            return false;
        }

        // register elastic search provider, if not registered already
        if (
            $engineName === ElasticSearchEngine::class &&
            empty(app()->getProviders(ElasticSearchServiceProvider::class))
        ) {
            app()->register(ElasticSearchServiceProvider::class);
        }

        try {
            $results = $manager->engine($engineName)->search(
                app(Builder::class, [
                    'model' => new User(),
                    'query' => 'test',
                ]),
            );
            if (!$results) {
                return $this->getDefaultErrorMessage();
            }
        } catch (PDOException $e) {
            return ['search_group' => $e->getMessage()];
        } catch (Exception | Throwable $e) {
            return $this->getErrorMessage($e);
        }
    }

    /**
     * @param Exception|Throwable $e
     * @return array
     */
    private function getErrorMessage($e)
    {
        $message = $e->getMessage();
        return [
            'search_group' => "Could not enable this search method: $message",
        ];
    }

    /**
     * @return array
     */
    private function getDefaultErrorMessage()
    {
        return ['search_group' => 'Could not enable this search method.'];
    }
}
