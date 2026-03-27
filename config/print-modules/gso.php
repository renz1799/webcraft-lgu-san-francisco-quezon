<?php

return [
    'printables' => [
        'gso_air' => [
            'module' => 'GSO',
            'default_paper' => 'a4-portrait',
            'allowed_papers' => [
                'a4-portrait',
                'letter-portrait',
                'legal-portrait',
            ],
            'profiles' => [
                'a4-portrait' => [
                    'pages_view' => 'gso::air.print.paper.a4-portrait.pages',
                    'styles_view' => 'gso::air.print.paper.a4-portrait.styles',
                    'pdf_styles_view' => 'gso::air.print.paper.a4-portrait.pdf-styles',
                    'rows_per_page' => 31,
                    'grid_rows' => 31,
                    'last_page_grid_rows' => 24,
                    'description_chars_per_line' => 73,
                    'header_image_web' => 'headers/Picture1.png',
                    'footer_image_web' => 'headers/Picture2.png',
                ],
                'letter-portrait' => [
                    'pages_view' => 'gso::air.print.paper.letter-portrait.pages',
                    'styles_view' => 'gso::air.print.paper.letter-portrait.styles',
                    'pdf_styles_view' => 'gso::air.print.paper.letter-portrait.pdf-styles',
                    'rows_per_page' => 52,
                    'grid_rows' => 51,
                    'last_page_grid_rows' => 34,
                    'description_chars_per_line' => 40,
                ],
                'legal-portrait' => [
                    'pages_view' => 'gso::air.print.paper.legal-portrait.pages',
                    'styles_view' => 'gso::air.print.paper.legal-portrait.styles',
                    'pdf_styles_view' => 'gso::air.print.paper.legal-portrait.pdf-styles',
                    'rows_per_page' => 63,
                    'grid_rows' => 60,
                    'last_page_grid_rows' => 33,
                    'description_chars_per_line' => 72,
                ],
            ],
        ],
        'gso_ris' => [
            'module' => 'GSO',
            'default_paper' => 'a4-portrait',
            'allowed_papers' => [
                'a4-portrait',
                'letter-portrait',
                'legal-portrait',
            ],
            'profiles' => [
                'a4-portrait' => [
                    'pages_view' => 'gso::ris.print.paper.a4-portrait.pages',
                    'styles_view' => 'gso::ris.print.paper.a4-portrait.styles',
                    'pdf_styles_view' => 'gso::ris.print.paper.a4-portrait.pdf-styles',
                    'rows_per_page' => 74,
                    'grid_rows' => 72,
                    'last_page_grid_rows' => 31,
                    'description_chars_per_line' => 40,
                ],
                'letter-portrait' => [
                    'pages_view' => 'gso::ris.print.paper.letter-portrait.pages',
                    'styles_view' => 'gso::ris.print.paper.letter-portrait.styles',
                    'pdf_styles_view' => 'gso::ris.print.paper.letter-portrait.pdf-styles',
                    'rows_per_page' => 68,
                    'grid_rows' => 68,
                    'last_page_grid_rows' => 29,
                    'description_chars_per_line' => 40,
                ],
                'legal-portrait' => [
                    'pages_view' => 'gso::ris.print.paper.legal-portrait.pages',
                    'styles_view' => 'gso::ris.print.paper.legal-portrait.styles',
                    'pdf_styles_view' => 'gso::ris.print.paper.legal-portrait.pdf-styles',
                    'rows_per_page' => 76,
                    'grid_rows' => 76,
                    'last_page_grid_rows' => 37,
                    'description_chars_per_line' => 40,
                ],
            ],
        ],
    ],
];
