<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.genre}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{genre.name}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => 'Popular {{genre.name}} artists.',
    ],
    [
        'property' => 'og:type',
        'content' => 'website',
    ],
];
