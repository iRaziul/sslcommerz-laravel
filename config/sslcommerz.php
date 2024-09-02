<?php

// config for Raziul/Sslcommerz

return [
    /**
     * Enable/Disable Sandbox mode
     */
    'sandbox' => env('SSLC_SANDBOX', true),

    /**
     * The API credentials given from SSLCommerz
     */
    'store' => [
        'id' => env('SSLC_STORE_ID'),
        'password' => env('SSLC_STORE_PASSWORD'),
        'currency' => env('SSLC_STORE_CURRENCY', 'BDT'),
    ],

    /**
     * Route names for success/failure/cancel
     */
    'route' => [
        'success' => env('SSLC_ROUTE_SUCCESS', 'sslc.success'),
        'failure' => env('SSLC_ROUTE_FAILURE', 'sslc.failure'),
        'cancel' => env('SSLC_ROUTE_CANCEL', 'sslc.cancel'),
        'ipn' => env('SSLC_ROUTE_IPN', 'sslc.ipn'),
    ],

    /**
     * Product profile required from SSLC
     * By default it is "general"
     *
     * AVAILABLE PROFILES
     *  general
     *  physical-goods
     *  non-physical-goods
     *  airline-tickets
     *  travel-vertical
     *  telecom-vertical
     */
    'product_profile' => 'general',
];
