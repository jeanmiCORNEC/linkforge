<?php

namespace App\Support\Affiliations;

use App\Models\AffiliateIntegration;
use Illuminate\Support\Carbon;

interface AffiliateConnector
{
    /**
     * Synchronise les conversions pour une intégration sur la fenêtre donnée.
     *
     * @return int Nombre de conversions créées/mises à jour
     */
    public function sync(AffiliateIntegration $integration, Carbon $since, Carbon $until): int;
}
