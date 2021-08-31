@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1>{{ $meta->getTitle() }}</h1>

    <p>{{ $meta->getDescription() }}</p>

    <ul class="genres">
        @foreach($meta->getData('genres') as $genre)
            <li>
                <figure>
                    <img src="{{$genre->image}}">
                    <figcaption><a href="{{ $meta->urls->genre($genre) }}">{{$genre['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
@endsection
