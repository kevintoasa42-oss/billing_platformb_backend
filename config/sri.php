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

    // Tiempo de espera entre recepcion y autorizacion (segundos)
    'auth_wait_seconds' => env('SRI_AUTH_WAIT_SECONDS', 3),

    // URLs de los web services del SRI
    'wsdl' => [
        'reception' => [
            'pruebas' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'produccion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
        ],
        'authorization' => [
            'pruebas' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
            'produccion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
    ],
];
