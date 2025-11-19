<?php

return [
    'platforms' => [
        [
            'id'    => 'impact',
            'name'  => 'Impact',
            'fields'=> [
                ['key' => 'api_key', 'label' => 'API Key'],
                ['key' => 'account_sid', 'label' => 'Account SID'],
            ],
        ],
        [
            'id'    => 'awin',
            'name'  => 'Awin',
            'fields'=> [
                ['key' => 'api_key', 'label' => 'API Key'],
                ['key' => 'publisher_id', 'label' => 'ID Publisher'],
            ],
        ],
        [
            'id'    => 'hotmart',
            'name'  => 'Hotmart',
            'fields'=> [
                ['key' => 'client_id', 'label' => 'Client ID'],
                ['key' => 'client_secret', 'label' => 'Client Secret'],
            ],
        ],
    ],
    'connectors' => [
        'impact' => \App\Support\Affiliations\ImpactConnector::class,
    ],
    'sync_window_days' => 7,
];
