<?php

namespace App\Http\Controllers;

use App\Actions\Channel\CrupdateChannel;
use App\Channel;
use App\Http\Requests\CrupdateChannelRequest;
use Arr;
use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ChannelController extends BaseController
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Channel $channel, Request $request)
    {
        $this->channel = $channel;
        $this->request = $request;
    }

    public function index(): Response
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [Channel::class, $userId]);

        $builder = $this->channel->newQuery();

        if ($userId = $this->request->get('userId')) {
            $builder->where('user_id', $userId);
        }

        if ($channelIds = $this->request->get('channelIds')) {
            $builder->whereIn('id', explode(',', $channelIds));
        }

        $paginator = new MysqlDataSource($builder, $this->request->all());

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function show(Channel $channel): Response
    {
        $this->authorize('show', $channel);

        $channel->loadContent($this->request->all());

        if ($this->request->get('returnContentOnly')) {
            return response()->json(['pagination' => $channel->content]);
        } else {
            return $this->success(['channel' => $channel->toArray()]);
        }
    }

    public function store(CrupdateChannelRequest $request): Response
    {
        $this->authorize('store', Channel::class);

        $channel = app(CrupdateChannel::class)->execute($request->all());

        return $this->success(['channel' => $channel]);
    }

    public function update(
        Channel $channel,
        CrupdateChannelRequest $request
    ): Response {
        $this->authorize('store', $channel);

        $channel = app(CrupdateChannel::class)->execute(
            $request->all(),
            $channel,
        );

        return $this->success(['channel' => $channel]);
    }

    public function destroy(Collection $channels): Response
    {
        $channels = $channels->filter(function (Channel $channel) {
            return !Arr::get($channel->config, 'preventDeletion');
        });

        $channelsToDelete = $channels->pluck('id');
        $this->authorize('destroy', [Channel::class, $channelsToDelete]);

        // touch all channels that have channels were' deleting
        // nested so cache for them is cleared properly
        $parentChannelIds = DB::table('channelables')
            ->where('channelable_type', Channel::class)
            ->whereIn('channelable_id', $channelsToDelete)
            ->pluck('channel_id');
        $this->channel
            ->whereIn('id', $parentChannelIds)
            ->update(['updated_at' => Carbon::now()]);

        DB::table('channelables')
            ->whereIn('channel_id', $channelsToDelete)
            ->delete();
        $this->channel->whereIn('id', $channelsToDelete)->delete();

        return $this->success();
    }

    public function detachItem(Channel $channel)
    {
        $this->authorize('update', $channel);

        // TODO: touch / clear cache for parent channel if this channel is nested

        $modelType = $this->request->get('item')['model_type'];

        // track => tracks
        $relation = Str::plural($modelType);

        $channel->$relation()->detach($this->request->get('item')['id']);
        $channel->touch();

        return $this->success();
    }

    public function attachItem(Channel $channel)
    {
        $this->authorize('update', $channel);

        $modelType = $this->request->get('item')['model_type'];
        $modelId = (int) $this->request->get('item')['id'];

        // track => tracks
        $relation = Str::plural($modelType);

        if ($modelType === Channel::MODEL_TYPE && $modelId === $channel->id) {
            return $this->error(__("Channel can't be attached to itself."));
        }

        $relationId = $this->request->get('item')['id'];
        if (!$channel->$relation()->find($relationId)) {
            $channel->$relation()->attach($relationId);
        }

        $channel->touch();

        return $this->success();
    }

    public function changeOrder(Channel $channel)
    {
        $this->authorize('update', $channel);

        $this->validate($this->request, [
            'ids' => 'array|min:1',
            'ids.*' => 'integer',
        ]);

        $queryPart = '';
        foreach ($this->request->get('ids') as $order => $id) {
            $queryPart .= " when id=$id then $order";
        }

        DB::table('channelables')
            ->where('channel_id', $channel->id)
            ->whereIn('id', $this->request->get('ids'))
            ->update(['order' => DB::raw("(case $queryPart end)")]);

        $channel->touch();

        return $this->success();
    }
}
