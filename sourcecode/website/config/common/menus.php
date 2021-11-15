<?php

use App\Actions\Channel\LoadChannelMenuItems;

return [
    [
        'name' => 'Channel',
        'itemsLoader' => LoadChannelMenuItems::class,
    ]
];
