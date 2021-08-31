@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1>{{ $meta->getTitle()  }}</h1>

    <p>{{ $meta->getDescription() }}</p>

    <ul class="albums">
        @foreach($meta->getData('albums') as $album)
            <li>
                <figure>
                    <img src="{{$album['image']}}">
                    <figcaption><a href="{{$meta->urls->album($album)}}">{{$album['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
@endsection
