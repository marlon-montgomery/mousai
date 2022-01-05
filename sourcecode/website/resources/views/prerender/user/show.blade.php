@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    <p>{{$meta->getDescription()}}</p>
    <br>

    <img src="{{$meta->get('og:image')}}">

    <h2>{{__('Followers')}}</h2>
    <ul class="followers">
        @foreach($meta->getData('user.followers') as $user)
            <li><a href="{{ $meta->urls->user($user) }}">{{ $user['display_name'] }}</a></li>
        @endforeach
    </ul>

    <h2>{{__('Followed Users')}}</h2>
    <ul class="followed_users">
        @foreach($meta->getData('user.followedUsers') as $user)
            <li><a href="{{ $meta->urls->user($user) }}">{{ $user['display_name'] }}</a></li>
        @endforeach
    </ul>

    <h2>{{__('Playlists')}}</h2>
    <ul class="playlists">
        @foreach($meta->getData('user.playlists') as $playlist)
            <li>
                <figure>
                    <img src="{{ $playlist['image'] }}">
                    <figcaption>
                        <a href="{{ $meta->urls->playlist($playlist) }}">{{ $playlist['name'] }}</a>
                    </figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
@endsection
