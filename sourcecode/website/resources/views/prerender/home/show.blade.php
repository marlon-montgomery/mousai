@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1>{{ $meta->getTitle() }}</h1>

    <p>{{ $meta->getDescription() }}</p>

    <ul>
        <li>
            <a href="{{ url('new-releases') }}">{{__('New Releases')}}</a>
            <a href="{{ url('popular-genres') }}">{{(__('Popular Genres'))}}</a>
            <a href="{{ url('popular-albums') }}">{{__('Popular Albums')}}</a>
            <a href="{{ url('top-50') }}">{{__('Top 50')}}</a>
        </li>
    </ul>

    @if($meta->getData('genres'))
        <ul class="genres">
            @foreach($meta->getData('genres') as $genre)
                <li>
                    <figure>
                        <img src="{{$genre['image']}}">
                        <figcaption><a href="{{ $meta->urls->genre($genre) }}">{{$genre['name']}}</a></figcaption>
                    </figure>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
