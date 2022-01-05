@extends('common::prerender.base')

<?php /** @var App\Services\Meta\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    {!! $meta->getDescription() !!}
    <br>

    <img src="{{$meta->get('og:image')}}">

    @foreach($meta->getData('tracks')->items() as $track)
        <li><a href="{{ $meta->urls->track($track) }}">{{ $track['name'] }}</a></li>
    @endforeach
@endsection
