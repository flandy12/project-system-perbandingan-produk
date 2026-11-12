<?php

$criterion = fn (string $name, string $type, ?string $unit, float $reference) => [
    'name' => $name, 'data_type' => $type, 'unit' => $unit, 'reference' => $reference, 'weight' => 1,
];

return [
    'feature_version' => 'stb-capabilities-v1',
    'feature_label' => 'Indeks kemampuan terdokumentasi',
    'feature_note' => 'Rubrik awal aplikasi dengan bobot setara. Mengukur kemampuan yang tercantum dalam sumber, bukan hasil pengujian mutu perangkat. Hanya dinilai dalam jenis perangkat yang sama.',
    'types' => ['terrestrial' => 'STB antena UHF', 'satellite' => 'Receiver parabola', 'combo' => 'Receiver combo', 'android' => 'Android TV Box'],
    'feature_rubrics' => [
        'terrestrial' => [
            'resolution' => $criterion('Resolusi keluaran maksimum', 'numeric', 'p', 2160),
            'usb' => $criterion('Jumlah port USB', 'numeric', 'port', 2),
            'wifi_support' => $criterion('Dukungan koneksi WiFi', 'boolean', null, 1),
            'cast_support' => $criterion('Dukungan berbagi layar', 'boolean', null, 1),
            'ews' => $criterion('Peringatan dini EWS', 'boolean', null, 1),
        ],
        'satellite' => [
            'fullhd' => $criterion('Dukungan keluaran Full HD', 'boolean', null, 1),
            'usb' => $criterion('Jumlah port USB', 'numeric', 'port', 2),
            'wifi_support' => $criterion('Dukungan koneksi WiFi', 'boolean', null, 1),
            'hevc' => $criterion('Dukungan H.265 HEVC tercantum', 'boolean', null, 1),
            'ota' => $criterion('Dukungan pembaruan OTA', 'boolean', null, 1),
        ],
        'combo' => [
            'fullhd' => $criterion('Dukungan keluaran Full HD', 'boolean', null, 1),
            'usb' => $criterion('Jumlah port USB', 'numeric', 'port', 2),
            'wifi_support' => $criterion('Dukungan koneksi WiFi', 'boolean', null, 1),
            'hevc' => $criterion('Dukungan H.265 HEVC tercantum', 'boolean', null, 1),
            'ews' => $criterion('Peringatan dini EWS', 'boolean', null, 1),
        ],
        'android' => [
            'resolution' => $criterion('Resolusi keluaran maksimum', 'numeric', 'p', 2160),
            'usb' => $criterion('Jumlah port USB', 'numeric', 'port', 2),
            'wifi_support' => $criterion('Dukungan koneksi WiFi', 'boolean', null, 1),
        ],
    ],
];
