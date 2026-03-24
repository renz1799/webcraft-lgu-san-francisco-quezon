<?php

return [
    'printables' => [
        'audit_logs' => [
            'module' => 'CORE',
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
