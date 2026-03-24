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
            'header_image_web' => 'headers/a4_header_template_dark_2480x300.png',
            'footer_image_web' => 'headers/a4_footer_template_dark_2480x250.png',
            'header_image_pdf' => 'headers/a4_header_template_dark_2480x300.png',
            'footer_image_pdf' => 'headers/a4_footer_template_dark_2480x250.png',
        ],

        'letter-portrait' => [
            'code' => 'letter-portrait',
            'label' => 'Letter Portrait',
            'width' => '8.5in',
            'height' => '11in',
            'orientation' => 'portrait',
            'preview_width' => '8.5in',

            // platform defaults
            'header_image_web' => 'headers/letter_portrait_header_2550x300.png',
            'footer_image_web' => 'headers/letter_portrait_footer_2550x250.png',
            'header_image_pdf' => 'headers/letter_portrait_header_2550x300.png',
            'footer_image_pdf' => 'headers/letter_portrait_footer_2550x250.png',
        ],

        'legal-portrait' => [
            'code' => 'legal-portrait',
            'label' => 'Legal Portrait (8.5 x 13)',
            'width' => '8.5in',
            'height' => '13in',
            'orientation' => 'portrait',
            'preview_width' => '8.5in',

            // platform defaults
            'header_image_web' => 'headers/longbond_portrait_header_2550x300.png',
            'footer_image_web' => 'headers/longbond_portrait_footer_2550x250.png',
            'header_image_pdf' => 'headers/longbond_portrait_header_2550x300.png',
            'footer_image_pdf' => 'headers/longbond_portrait_footer_2550x250.png',
        ],

    ],

];
