<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.album}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{album.name}} - {{album.artists.0.name}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{album.name}} album by {{album.artists.0.name}} on {{site_name}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'music.album',
    ],
    [
        'property' => 'music:release_date',
        'content' => '{{album.release_date}}',
    ],
    [
        'property' => 'og:image',
        'content' => '{{album.image}}',
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
