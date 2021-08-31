<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.artist}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{artist.name}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{artist.profile.description}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'music.musician',
    ],
    [
        'property' => 'og:image',
        'content' => '{{artist.image_small}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '1000',
    ],
    [
        'property' => 'og:image:height',
        'content' => '667',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            "@context" => "http://schema.org",
            "@type" => "MusicGroup",
            "@id" => "{{url.artist}}",
            "name" => "{{artist.name}}",
            "url" => "{{url.artist}}",
            "description" => "{{artist.profile.description}}",
            "image" => "{{artist.image_large}}"
        ],
    ]
];
