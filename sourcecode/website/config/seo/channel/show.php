<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.channel}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{channel.config.seoTitle}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{channel.config.seoDescription}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'website',
    ],
];
