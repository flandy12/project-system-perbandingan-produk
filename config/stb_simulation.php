<?php

return [
    'version' => 'stb-test-scenario-v1',
    'seed' => 'stb-learning-20261006',
    'scenarios' => ['balanced' => 'Skenario dasar', 'low' => 'Penjualan rendah', 'high' => 'Penjualan tinggi'],
    // Explicit software-test assumptions. These are never asserted as hardware facts.
    'assumed_features' => ['resolution' => 1080, 'usb' => 1, 'wifi_support' => 0, 'cast_support' => 0,
        'ews' => 1, 'fullhd' => 1, 'hevc' => 0, 'ota' => 1],
    'fallback_prices' => ['terrestrial' => 180000, 'satellite' => 300000, 'combo' => 350000, 'android' => 326000],
];
