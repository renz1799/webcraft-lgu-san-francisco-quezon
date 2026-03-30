<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDF Generator Driver
    |--------------------------------------------------------------------------
    |
    | auto    = prefer Chrome when available, otherwise fall back to Dompdf
    | chrome  = always use the Chrome/Chromium engine
    | dompdf  = always use the pure-PHP Dompdf engine
    |
    */
    'driver' => env('PDF_DRIVER', 'auto'),

    'dompdf' => [
        'dpi' => env('PDF_DOMPDF_DPI', 96),
    ],
];
