@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    {!! $meta->getDescription() !!}

    <img src="{{$meta->get('og:image')}}">

    <ul>
        @foreach($meta->getData('artist.genres') as $genre)
            <li><a href="{{$meta->urls->genre($genre)}}">{{$genre['name']}}</a></li>
        @endforeach
    </ul>

    @foreach($meta->getData('albums') as $album)
        <h3><a href="{{ $meta->urls->album($album) }}">{{ $album['name'] }}</a> - {{ $album['release_date'] }}</h3>

        <ul>
            @foreach($album['tracks'] as $track)
                <li><a href="{{ $meta->urls->track($track)  }}">{{ $track['name'] }} - {{ $album['name'] }} - {{ $meta->getData('artist.name') }}</a></li>
            @endforeach
        </ul>
    @endforeach

    @if($meta->getData('artist.similar'))
        <h2>Similar Artists</h2>

        @foreach($meta->getData('artist.similar') as $similarArtist)
            <h3><a href="{{ $meta->urls->artist($similarArtist) }}">{{ $similarArtist['name'] }}</a></h3>
        @endforeach
    @endif
@endsection
