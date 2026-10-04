<?php

return [
    'mode'                  => 'utf-8',
    'format'                => 'A4',
    'author'                => '',
    'subject'               => '',
    'keywords'              => '',
    'creator'               => 'Laravel Pdf',
    'display_mode'          => 'fullpage',
    'tempDir'               => storage_path('app/mpdf'),
    'pdf_a'                 => false,
    'pdf_a_auto'            => false,
    'icc_profile_path'      => '',
    'font_path'             => public_path('assets/fonts/Cairo/'),
    'font_data' => [
        'cairo' => [
            'R'  => 'Cairo-Regular.ttf',
            'B'  => 'Cairo-Bold.ttf',
            'L'  => 'Cairo-Light.ttf',
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ],
    'default_font' => 'cairo',
];
