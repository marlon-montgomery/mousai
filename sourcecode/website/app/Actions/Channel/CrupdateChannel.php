<?php

namespace App\Actions\Channel;

use App\Channel;
use Auth;
use DB;
use Illuminate\Support\Arr;

class CrupdateChannel
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    public function execute($params, $initialChannel = null): Channel
    {
        if ( ! $initialChannel) {
            $channel = $this->channel->newInstance([
                 'user_id' => Auth::id(),
            ]);
        } else {
            $channel = $initialChannel;
        }

        $attributes = [
            'name' => $params['name'],
            'slug' => $params['slug'],
            // merge old config so config that is not in crupdate channel form is not lost
            'config' => array_merge($initialChannel['config'] ?? [], $params['config']),
        ];

        $channel->fill($attributes)->save();

        if ( ! $initialChannel && $channelContent = Arr::get($params, 'content')) {
            $pivots = collect($channelContent)
                ->map(function($item, $i) use($channel) {
                    return [
                        'channel_id' => $channel->id,
                        'channelable_id' => $item['id'],
                        'channelable_type' => modelTypeToNamespace($item['model_type']),
                        'order' => $i
                    ];
                })
                ->filter(function($item) use($channel) {
                    // channels should not be attached to themselves
                    return $item['channelable_type'] !== Channel::class || $item['channel_id'] !== $channel->id;
                });
            DB::table('channelables')->insert($pivots->toArray());
        }

        if (Arr::get($params, 'updateContent')) {
            app(UpdateChannelContent::class)->execute($channel);
            $channel->loadContent();
        }

        return $channel;
    }
}
