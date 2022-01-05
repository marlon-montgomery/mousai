@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    {!! $meta->getDescription() !!}
    <br>

    <img src="{{$meta->getData('track.image') ?: $meta->getData('track.album.image')}}">

    @if($meta->getData('track.album'))
        <ul class="tracks">
            @foreach($meta->getData('track.album.tracks') as $track)
                <li>
                    <figure>
                        <img src="{{$track['album']['image']}}">
                        <figcaption>
                            <a href="{{$meta->urls->track($track)}}">{{$track['name']}}</a> by
                            @foreach($track['album']['artists'] as $artist)
                                <a href="{{$meta->urls->artist($artist)}}">{{$artist['name']}}</a>,
                            @endforeach
                        </figcaption>
                    </figure>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
