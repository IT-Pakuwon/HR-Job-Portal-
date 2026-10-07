<?php

/*
 * Fixed text for the OFFICE (OLA) Surat 1 / Surat 2 letters, keyed by
 * tr_agreement.cpny_id. A company without its own entry uses 'default'.
 *
 *  code           company segment of the letter number   (173/LGL-EPH/PATO/VII/2026)
 *  property_code  property segment of the letter number
 *  building       name used in "Gedung Perkantoran {building}"
 *  return_address lines of the "dikirimkan kepada kami ke alamat" block
 *  footer         small line at the bottom of the page
 *  seq_floor      last running number already used outside the system this
 *                 year; the first system letter is seq_floor + 1
 */
return [
    'office' => [
        'default' => [
            'code' => null,
            'property_code' => null,
            'building' => null,
            'return_address' => [],
            'footer' => null,
            'seq_floor' => 0,
        ],

        'EP' => [
            'code' => 'EPH',
            'property_code' => 'PATO',
            'building' => 'Pakuwon Tower',
            'return_address' => [
                ['text' => 'PT Elite Prima Hutama', 'bold' => true],
                ['text' => 'Legal Department', 'bold' => true],
                ['text' => 'Gandaria 8 Office Tower, 32nd Floor'],
                ['text' => 'Jl. Sultan Iskandar Muda'],
                ['text' => 'Kebayoran Lama'],
                ['text' => 'Jakarta Selatan'],
                ['text' => 'Tel. 021-2900 8000'],
                ['text' => 'U.p.: Tia / Megafiany (Legal Dept)'],
            ],
            'footer' => 'Pakuwon Tower, Lt.7-C Kota Kasablanka Jl. Casablanca Raya Kav. 88, Jakarta Selatan 12870 · Ph. +62 21 837 09 888 · Fax. +62 21 837 06 888',
            'seq_floor' => 0,
        ],
    ],

    // Same keys for MALL (PSM) agreements. 'footer' may be one line or a list of lines.
    'mall' => [
        'default' => [
            'code' => null,
            'property_code' => null,
            'building' => null,
            'return_address' => [],
            'footer' => null,
            'seq_floor' => 0,
        ],

        'AW' => [
            'code' => 'AW',
            'property_code' => 'GC',
            'building' => 'Mal Gandaria City',
            'return_address' => [
                ['text' => 'PT ARTISAN WAHYU', 'bold' => true, 'italic' => true],
                ['text' => 'Legal Department', 'bold' => true, 'italic' => true],
                ['text' => 'Gandaria 8 Office Tower, 32nd Floor', 'bold' => true, 'italic' => true],
                ['text' => 'Jl. Sultan Iskandar Muda', 'bold' => true, 'italic' => true],
                ['text' => 'Kebayoran Lama, Jakarta Selatan', 'bold' => true, 'italic' => true],
                ['text' => 'Tel. 021-2900 8000', 'bold' => true, 'italic' => true],
                ['text' => 'U.p.: Sarah Mega / Fathi', 'bold' => true, 'italic' => true],
            ],
            'footer' => [
                'Gandaria 8 Office Tower Lt-32, Jalan Sultan Iskandar Muda Kebayoran Lama, Jakarta Selatan 12240 - Indonesia | Tel : +62 - 21 2900 8000',
                'Gandaria City Lt-1A, Jalan Sultan Iskandar Muda Kebayoran Lama, Jakarta Selatan 12240 - Indonesia Tel : +62-21 2905 2888 | Fax : +62-21 2905 2988',
            ],
            'seq_floor' => 0,
        ],
    ],
];
