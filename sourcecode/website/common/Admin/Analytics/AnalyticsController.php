<?php namespace Common\Admin\Analytics;

use Cache;
use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Admin\Analytics\Actions\GetAnalyticsData;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends BaseController
{
    /**
     * @var GetAnalyticsData
     */
    private $getDataAction;

    /**
     * @var GetAnalyticsHeaderDataAction
     */
    private $getHeaderDataAction;

    /**
     * @var Request
     */
    private $request;

    const DEFAULT_CHANNEL = 'default';

    /**
     * @param GetAnalyticsData $getDataAction
     * @param GetAnalyticsHeaderDataAction $getHeaderDataAction
     * @param Request $request
     */
    public function __construct(
        Request $request,
        GetAnalyticsData $getDataAction,
        GetAnalyticsHeaderDataAction $getHeaderDataAction
    )
    {
        $this->getDataAction = $getDataAction;
        $this->getHeaderDataAction = $getHeaderDataAction;
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function stats()
    {
        $this->authorize('index', 'ReportPolicy');

        $channel = $this->request->get('channel') ?: self::DEFAULT_CHANNEL;

        if ($channel === 'clicks') {
            // TODO: temp override of BeLink, refactor get data action and move caching into there so each app can control caching
            $mainData = $this->getDataAction->execute($channel);
        } else {
            $mainData = $data = Cache::remember("analytics.data.main.$channel", Carbon::now()->addDay(), function() use($channel) {
                return $this->getDataAction->execute($channel);
            }) ?: [];
        }

        $headerData = $data = Cache::remember("analytics.data.header.$channel", Carbon::now()->addDay(), function() use($channel) {
            return $this->getHeaderDataAction->execute($channel);
        });

        return $this->success([
            'mainData' => $mainData,
            'headerData' => $headerData,
        ]);
    }
}
