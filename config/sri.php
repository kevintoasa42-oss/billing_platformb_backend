<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SRI Configuration
    |--------------------------------------------------------------------------
    | Configuracion para generacion de comprobantes electronicos del SRI Ecuador.
    */

    // RUC del proveedor del sistema (siempre va en infoAdicional del XML)
    'provider_ruc' => env('SRI_PROVIDER_RUC', '1752331700'),
];
