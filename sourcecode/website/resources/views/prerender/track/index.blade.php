@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1>{{ $meta->getTitle() }}</h1>

    <p>{{ $meta->getDescription() }}</p>

    <ul class="tracks">
        @foreach($meta->getData('tracks') as $track)
            <li>
                <figure>
                    <img src="{{$track['album']['image']}}">
                    <figcaption>
                        <a href="{{$meta->urls->track($track)}}">{{$track['name']}}</a> by
                        <a href="{{$meta->urls->artist($track['album']['artist'])}}">{{$track['album']['artist']['name']}}</a>
                    </figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
@endsection
