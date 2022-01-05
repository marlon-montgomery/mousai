<?php

//TODO: for json+ld
//@foreach($data['tracks'] as $track)
//        <meta property="music:song" content="{{ $utils->getTrackUrl($track) }}">
//    @endforeach

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.playlist}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{playlist.name}} by {{playlist.editors.0.display_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{playlist.description}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'music.playlist',
    ],
    [
        'property' => 'og:image',
        'content' => '{{playlist.image}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '300',
    ],
    [
        'property' => 'og:image:height',
        'content' => '300',
    ],
];
