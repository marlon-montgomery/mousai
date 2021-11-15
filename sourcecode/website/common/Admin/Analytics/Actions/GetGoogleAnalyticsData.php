<?php

namespace Common\Admin\Analytics\Actions;

use Carbon\Carbon;
use Google_Service_Analytics_GaData;
use Illuminate\Support\Collection;
use Spatie\Analytics\Analytics;
use Spatie\Analytics\Period;

class GetGoogleAnalyticsData implements GetAnalyticsData
{
    /**
     * @var Analytics
     */
    private $analytics;

    /**
     * @param Analytics $analytics
     */
    public function __construct(Analytics $analytics)
    {
        $this->analytics = $analytics;
    }

    public function execute($channel)
    {
        return [
            'browsers' =>  $this->analytics->fetchTopBrowsers(Period::days(7)),
            'countries' => $this->getCountries(),
            'weeklyPageViews' => $this->weeklyPageViews(),
            'monthlyPageViews' => $this->monthlyPageViews(),
        ];
    }

    private function weeklyPageViews()
    {
        return [
            'current' => $this->getPageViews(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
            'previous' => $this->getPageViews(Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek())
        ];
    }

    private function monthlyPageViews()
    {
        return [
            'current' => $this->getPageViews(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()),
            'previous' => $this->getPageViews(Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth())
        ];
    }

    private function getPageViews(Carbon $start, Carbon $end)
    {
        return $this->analytics->fetchVisitorsAndPageViews(
            Period::create($start, $end)
        )->groupBy(function($item) {
            return $item['date']->format('d'); // grouping by years
        })->map(function(Collection $dateGroup) {
            return $dateGroup->reduce(function ($result, $item) {
                $result['pageViews'] += $item['pageViews'];
                return $result;
            }, ['date' => $dateGroup[0]['date']->getTimestamp(), 'pageViews' => 0]);
        })->values();
    }

    private function getCountries($maxResults = 6)
    {
        /** @var Google_Service_Analytics_GaData $answer */
        $answer = $this->analytics->performQuery(
            Period::create(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
            'ga:sessions',
            ['dimensions' => 'ga:country', 'sort' => '-ga:sessions']
        );

        if (is_null($answer->rows)) {
            return new Collection([]);
        }

        $pagesData = [];
        foreach ($answer->rows as $pageRow) {
            $pagesData[] = ['country' => $pageRow[0], 'sessions' => $pageRow[1]];
        }

        $countries = new Collection(array_slice($pagesData, 0, $maxResults - 1));

        if (count($pagesData) > $maxResults) {
            $otherCountries = collect(array_slice($pagesData, $maxResults - 1));
            $otherCountriesCount = array_sum(Collection::make($otherCountries->pluck('sessions'))->toArray());

            $countries->put(null, ['country' => 'other', 'sessions' => $otherCountriesCount]);
        }

        return $countries;
    }
}
