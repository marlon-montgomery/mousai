@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    {!! $meta->getDescription() !!}
    <br>

    <img src="{{$meta->get('og:image')}}">

    <p>{{__('Release Date')}}: {{$meta->getData('album.release_date')}}</p>

    @foreach($meta->getData('album.tracks') as $track)
        <li><a href="{{ $meta->urls->track($track) }}">{{ $track['name'] }}</a></li>
    @endforeach
@endsection
