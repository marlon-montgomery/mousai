@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')

    <h1>{{ $meta->getTitle() }}</h1>

    <h2>{{__('Artists')}}</h2>
    <ul class="artists">
        @foreach($meta->getData('artists') as $artist)
            <li>
                <figure>
                    <img src="{{$artist['image_small']}}">
                    <figcaption><a href="{{$meta->urls->artist($artist)}}">{{$artist['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>

    <h2>{{__('Albums')}}</h2>
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

    <h2>{{__('Tracks')}}</h2>
    <ul class="tracks">
        @foreach($meta->getData('tracks') as $track)
            @isset($track['album'])
                <li>
                    <figure>
                        <img src="{{$album['album']['image']}}">
                        <figcaption>
                            <a href="{{$meta->urls->track($track)}}">{{$track['name']}}</a> by
                            <a href="{{$meta->urls->artist($track['album']['artist'])}}">{{$track['album']['artist']['name']}}</a>
                        </figcaption>
                    </figure>
                </li>
            @endisset
        @endforeach
    </ul>

    <h2>{{__('Playlists')}}</h2>
    <ul class="playlists">
        @foreach($meta->getData('playlists') as $playlist)
            <li>
                <figure>
                    <img src="{{$playlist['image']}}">
                    <figcaption><a href="{{$meta->urls->playlist($playlist)}}">{{$playlist['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>

    <h2>{{__('Users')}}</h2>
    <ul class="users">
        @foreach($meta->getData('users') as $user)
            <li>
                <figure>
                    <img src="{{$user['avatar']}}">
                    <figcaption><a href="{{$meta->urls->user($user)}}">{{$user['display_name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
@endsection
