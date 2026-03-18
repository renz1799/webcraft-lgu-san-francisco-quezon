<?php

return [

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

    ],

        'modules' => [

            'audit_logs' => [
                'default_paper' => 'a4-portrait',

                'allowed_papers' => [
                    'a4-portrait',
                    'letter-portrait',
                ],

                'profiles' => [

                    'a4-portrait' => [
                        'pages_view' => 'audit-logs.print.paper.a4-portrait.pages',
                        'styles_view' => 'audit-logs.print.paper.a4-portrait.styles',
                        'pdf_styles_view' => 'audit-logs.print.paper.a4-portrait.pdf-styles',
                        'rows_per_page' => 15,
                    // optional module overrides
                    // 'header_image_web' => '...',
                    // 'footer_image_web' => '...',
                    // 'header_image_pdf' => '...',
                    // 'footer_image_pdf' => '...',
                    ],

                    'letter-portrait' => [
                        'pages_view' => 'audit-logs.print.paper.letter-portrait.pages',
                        'styles_view' => 'audit-logs.print.paper.letter-portrait.styles',
                        'pdf_styles_view' => 'audit-logs.print.paper.letter-portrait.pdf-styles',
                        'rows_per_page' => 15,
                    ],

                ],
            ],

        ],

];