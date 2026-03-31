<?php

return [
    'entity_name' => env('PRINT_ENTITY_NAME'),

    'defaults' => [
        'header' => null,
        'footer' => null,
    ],

    'papers' => [

        'a4-portrait' => [
            'code' => 'a4-portrait',
            'label' => 'A4 Portrait',
            'width' => '210mm',
            'height' => '297mm',
            'orientation' => 'portrait',
            'preview_width' => '210mm',

            // platform defaults
                    'header_image_web' => 'headers/Picture1.png',
                    'footer_image_web' => 'headers/Picture2.png',
                    'header_image_pdf' => 'headers/Picture1.png',
                    'footer_image_pdf' => 'headers/Picture2.png',
        ],

        'a4-landscape' => [
            'code' => 'a4-landscape',
            'label' => 'A4 Landscape',
            'width' => '297mm',
            'height' => '210mm',
            'orientation' => 'landscape',
            'preview_width' => '297mm',

            // platform defaults
            'header_image_web' => 'headers/a4_landscape_header_dark_3508x300.png',
            'footer_image_web' => 'headers/a4_landscape_footer_dark_3508x250.png',
            'header_image_pdf' => 'headers/a4_landscape_header_dark_3508x300.png',
            'footer_image_pdf' => 'headers/a4_landscape_footer_dark_3508x250.png',
        ],

        'letter-landscape' => [
            'code' => 'letter-landscape',
            'label' => 'Letter Landscape',
            'width' => '11in',
            'height' => '8.5in',
            'orientation' => 'landscape',
            'preview_width' => '11in',

            // platform defaults
            'header_image_web' => 'headers/letter_landscape_header_3300x300.png',
            'footer_image_web' => 'headers/letter_landscape_footer_3300x250.png',
            'header_image_pdf' => 'headers/letter_landscape_header_3300x300.png',
            'footer_image_pdf' => 'headers/letter_landscape_footer_3300x250.png',
        ],

        'legal-landscape' => [
            'code' => 'legal-landscape',
            'label' => 'Legal Landscape (8.5 x 13)',
            'width' => '13in',
            'height' => '8.5in',
            'orientation' => 'landscape',
            'preview_width' => '13in',

            // platform defaults
            'header_image_web' => 'headers/longbond_landscape_header_3900x300.png',
            'footer_image_web' => 'headers/longbond_landscape_footer_3900x250.png',
            'header_image_pdf' => 'headers/longbond_landscape_header_3900x300.png',
            'footer_image_pdf' => 'headers/longbond_landscape_footer_3900x250.png',
        ],

        'letter-portrait' => [
            'code' => 'letter-portrait',
            'label' => 'Letter Portrait',
            'width' => '8.5in',
            'height' => '11in',
            'orientation' => 'portrait',
            'preview_width' => '8.5in',

            // platform defaults
                    'header_image_web' => 'headers/Picture1.png',
                    'footer_image_web' => 'headers/Picture2.png',
                    'header_image_pdf' => 'headers/Picture1.png',
                    'footer_image_pdf' => 'headers/Picture2.png',
        ],

        'legal-portrait' => [
            'code' => 'legal-portrait',
            'label' => 'Legal Portrait (8.5 x 13)',
            'width' => '8.5in',
            'height' => '13in',
            'orientation' => 'portrait',
            'preview_width' => '8.5in',

            // platform defaults
                    'header_image_web' => 'headers/Picture1.png',
                    'footer_image_web' => 'headers/Picture2.png',
                    'header_image_pdf' => 'headers/Picture1.png',
                    'footer_image_pdf' => 'headers/Picture2.png',
        ],

    ],

];
