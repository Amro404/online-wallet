<?php

return [
        'foodics' => [
            'webhook_secret' => env('FOODICS_SECRET', '123456')
        ],
        'acme' => [
            'webhook_secret' => env('ACME_SECRET', '123456')
    ]
];
