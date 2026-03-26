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
                    'rows_per_page' => 19,
                    'grid_rows' => 19,
                    'last_page_grid_rows' => 21,
                    'header_image_web' => 'headers/Picture1.png',
                    'footer_image_web' => 'headers/Picture2.png',
                ],
                'letter-portrait' => [
                    'pages_view' => 'gso::air.print.paper.letter-portrait.pages',
                    'styles_view' => 'gso::air.print.paper.letter-portrait.styles',
                    'pdf_styles_view' => 'gso::air.print.paper.letter-portrait.pdf-styles',
                    'rows_per_page' => 20,
                    'grid_rows' => 21,
                    'last_page_grid_rows' => 13,
                ],
                'legal-portrait' => [
                    'pages_view' => 'gso::air.print.paper.legal-portrait.pages',
                    'styles_view' => 'gso::air.print.paper.legal-portrait.styles',
                    'pdf_styles_view' => 'gso::air.print.paper.legal-portrait.pdf-styles',
                    'rows_per_page' => 30,
                    'grid_rows' => 30,
                    'last_page_grid_rows' => 18,
                ],
            ],
        ],
        'gso_ris' => [
            'module' => 'GSO',
            'default_paper' => 'a4-portrait',
            'allowed_papers' => [
                'a4-portrait',
                'legal-portrait',
            ],
            'profiles' => [
                'a4-portrait' => [
                    'pages_view' => 'gso::ris.print.paper.a4-portrait.pages',
                    'styles_view' => 'gso::ris.print.paper.a4-portrait.styles',
                    'pdf_styles_view' => 'gso::ris.print.paper.a4-portrait.pdf-styles',
                    'rows_per_page' => 24,
                    'grid_rows' => 24,
                ],
                'legal-portrait' => [
                    'pages_view' => 'gso::ris.print.paper.legal-portrait.pages',
                    'styles_view' => 'gso::ris.print.paper.legal-portrait.styles',
                    'pdf_styles_view' => 'gso::ris.print.paper.legal-portrait.pdf-styles',
                    'rows_per_page' => 30,
                    'grid_rows' => 30,
                ],
            ],
        ],
    ],
];
