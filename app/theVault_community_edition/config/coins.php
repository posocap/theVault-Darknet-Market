<?php

/*
 * SIMULATION ONLY: this demo storefront moves no real money. The only coin is
 * the in-process StubCoin, which fabricates addresses and balances. Real coin
 * integrations (btc, xmr, ...) will be re-enabled later, with their RPC
 * settings supplied via .env, never hardcoded here.
 */
return [

    /**
     * List of coins supported by this market (short label => implementation)
     */
    'coin_list' => [
        'stb' => \App\Marketplace\Payment\StubCoin::class,
    ],

    /**
     * Refreshing cache for price loading
     */
    'caching_price_interval' => 20,

    /**
     * Market addresses (fake, simulation only)
     */
    'market_addresses' => [
        'stb' => [
            'marketSTB',
            'market2STB'
        ]
    ],
];
