<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\NormalizesFiltersForFulltextEngines;
use Elasticsearch\Client;
use Laravel\Scout\Builder;
use Matchish\ScoutElasticSearch\ElasticSearch\Params\Search as SearchParams;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;

class ElasticFilterer extends BaseFilterer
{
    use NormalizesFiltersForFulltextEngines;

    public function apply(): ?Builder
    {
        return $this->query
            ->getModel()
            ->search($this->searchTerm, function (
                Client $client,
                Search $body
            ) {
                foreach ($this->filters->getAll() as $filter) {
                    $value = $this->normalizeFilterValue($filter);
                    if (is_array($value)) {
                        $filter = new TermsQuery($filter['key'], $value);
                    } else {
                        $filter = new TermQuery($filter['key'], $value);
                    }
                    $body->addPostFilter($filter);
                }
                $params = new SearchParams(
                    $this->query->getModel()->searchableAs(),
                    $body->toArray(),
                );
                return $client->search($params->toArray());
            });
    }
}
