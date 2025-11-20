<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitoring des clics
    |--------------------------------------------------------------------------
    |
    | Paramétrage du lookback pour l’alerte “plus de clics récents”.
    |
    */
    'click_monitor_lookback' => (int) env('CLICK_MONITOR_LOOKBACK_MINUTES', 60),
];
