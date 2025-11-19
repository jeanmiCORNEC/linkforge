<?php

return [
    'tiers' => [
        [
            'id'          => 'free',
            'name'        => 'Free',
            'price'       => 0,
            'price_label' => 'Gratuit',
            'description' => '10 liens trackés, 2 campagnes, 4 sources, stats basiques.',
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
            'description' => 'Analytics avancés, exports, heatmap, conversions (bientôt).',
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
        [
            'id'          => 'scale',
            'name'        => 'Scale',
            'price'       => 29,
            'price_label' => '29€ / mois',
            'description' => 'Pour les équipes & studios : workspaces, API & reporting custom.',
            'cta_action'  => 'contact',
            'cta_label'   => 'Parler à l’équipe',
            'featured'    => false,
            'limits'      => [
                'Workspaces multiples',
                'API + Webhooks',
                'Rapports automatisés',
            ],
            'features'    => [
                'Accès équipe & permissions',
                'Exports temps-réel',
                'Customer Success dédié',
            ],
        ],
    ],
];
