@extends('common::prerender.base')

<?php /** @var \Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    {!! $meta->getDescription() !!}

    @switch($meta->getData('channel.config.contentModel'))
        @case(\App\Channel::class)
            @foreach($meta->getData('channel.content.data') as $channel)
                <h2>{{$channel['name']}}</h2>
                <ul style="display: flex; flex-wrap: wrap">
                    @foreach($channel['content']['data'] as $subContent)
                        <li>
                            <figure>
                                <img src="{{$subContent['album']['image'] ?? $subContent['image']}}" alt="">
                                <figcaption>
                                    <div>{{$subContent['name']}}</div>
                                    <div>{{$subContent['artists'][0]['name'] ?? null}}</div>
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        @break

        @default
        @foreach($meta->getData('channel.content.data') as $track)
            <figure>
                <img src="{{$track['album']['image'] ?? $track['image'] ?? url('client/assets/images/default/album.png')}}" alt="">
                <figcaption>{{$track['name']}}</figcaption>
            </figure>
        @endforeach
    @endswitch
@endsection
