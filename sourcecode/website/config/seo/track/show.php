<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.track}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{track.artists.0.name}} - {{track.name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{track.name}}, a song by {{track.artists.0.name}} on {{site_name}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'music.song',
    ],
    [
        'property' => 'music.duration',
        'content' => '{{track.duration}}',
    ],
    [
        'property' => 'music:album:track',
        'content' => '{{track.number}}',
    ],
    [
        'property' => 'music:release_date',
        'content' => '{{track.album.release_date}}',
    ],
    [
        'property' => 'og:image',
        'content' => '{{track.image?:track.album.image}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '300',
    ],
    [
        'property' => 'og:image:height',
        'content' => '300',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            "@context" => "http://schema.org",
            "@type" => "MusicRecording",
            "@id" => "{{url.track}}",
            "url" => "{{url.track}}",
            "name" => "{{track.name}}",
            "description" => "{{track.name}}, a song by {{track.artists.0.name}} on {{site_name}}",
            "datePublished" => "{{track.album.release_date}}"
        ]
    ]
];
