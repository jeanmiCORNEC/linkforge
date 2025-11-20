<?php

return [
    'tiers' => [
        [
            'id'          => 'free',
            'name'        => 'Free',
            'price'       => 0,
            'price_label' => 'Gratuit',
            'description' => 'Découverte : quelques liens/sources et analytics basiques.',
            'cta_action'  => 'register',
            'cta_label'   => 'Commencer gratuitement',
            'featured'    => false,
            'limits'      => [
                '10 liens actifs',
                '2 campagnes',
                '4 sources',
            ],
            'features'    => [
                'Dashboard global',
                'Suivi des clics basique',
                'Exports désactivés',
            ],
        ],
        [
            'id'          => 'pro',
            'name'        => 'Pro',
            'price'       => 9.9,
            'price_label' => '9,90€ / mois',
            'description' => 'Full options : heatmap, tops, deltas, exports CSV/raw.',
            'cta_action'  => 'register',
            'cta_label'   => 'Passer en Pro',
            'featured'    => true,
            'limits'      => [
                'Liens & sources illimités',
                'Heatmap horaire + tops',
                'Exports CSV + raw log',
            ],
            'features'    => [
                'Deltas vs période précédente',
                'Analytics détaillés campagnes/sources/liens',
                'Support prioritaire',
            ],
        ],
    ],
];
