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
                    'rows_per_page' => 24,
                    'grid_rows' => 24,
                    'header_image_web' => 'headers/Picture1.png',
                    'footer_image_web' => 'headers/Picture2.png',
                ],
                'letter-portrait' => [
                    'pages_view' => 'gso::air.print.paper.letter-portrait.pages',
                    'styles_view' => 'gso::air.print.paper.letter-portrait.styles',
                    'pdf_styles_view' => 'gso::air.print.paper.letter-portrait.pdf-styles',
                    'rows_per_page' => 20,
                    'grid_rows' => 21,
                ],
                'legal-portrait' => [
                    'pages_view' => 'gso::air.print.paper.legal-portrait.pages',
                    'styles_view' => 'gso::air.print.paper.legal-portrait.styles',
                    'pdf_styles_view' => 'gso::air.print.paper.legal-portrait.pdf-styles',
                    'rows_per_page' => 30,
                    'grid_rows' => 30,
                ],
            ],
        ],
    ],
];
