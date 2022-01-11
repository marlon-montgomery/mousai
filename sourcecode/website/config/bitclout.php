<?php
return [
    'api_url' => env('BITCLOUT_API_URL', 'https://bitclout.com/api/v0/'),

    'account' => [
        'seed' => env('BITCLOUT_ACCOUNT_SEED'),
        'passphrase' => env('BITCLOUT_ACCOUNT_PASSPHRASE')
    ],

    'message' => [
        'anonymous' => env('BITCLOUT_MESSAGE_ANONYMOUS'),
        'on_tip' => env('BITCLOUT_MESSAGE_ON_TIP')
    ]
];
