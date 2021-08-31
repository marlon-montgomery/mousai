<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.user}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{user.display_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{user.profile.description}} | {{site_name}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'profile',
    ],
    [
        'property' => 'og:image',
        'content' => '{{user.avatar}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '200',
    ],
    [
        'property' => 'og:image:height',
        'content' => '200',
    ],
];
