<?php

return [
    'registry' => [
        'CORE' => [
            'code' => 'CORE',
            'name' => 'Core Platform',
            'description' => 'Platform-level pages and shared administrative tooling.',
            'type' => 'platform',
            'home_route' => 'access.users.index',
        ],
        'GSO' => [
            'code' => 'GSO',
            'name' => 'General Services Office',
            'description' => 'Legacy GSO workflows being integrated into the LGU platform.',
            'type' => 'business',
            'home_route' => 'gso.dashboard',
        ],
    ],

    'platform_context_route_names' => [
        'access.*',
        'legacy.access.*',
        'audit-logs.*',
        'logs.*',
        'audit.restore',
        'drive.*',
        'password.*',
        'sign-up',
        'register.*',
    ],

    'shared_capability_codes' => [
        'TASKS',
    ],

    'shared_capability_home_routes' => [
        'TASKS' => 'tasks.index',
    ],

    'shared_capability_route_names' => [
        'tasks.*',
    ],

    'department_defaults' => [
        'CORE' => [
            'code' => env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_CORE', env('APP_DEFAULT_DEPARTMENT_CODE', 'ITO')),
        ],
        'TASKS' => [
            'code' => env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_TASKS', env('APP_DEFAULT_DEPARTMENT_CODE', 'ITO')),
        ],
        'DTS' => [
            'code' => env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_DTS', 'RECORDS'),
        ],
        'GSO' => [
            'code' => env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_GSO', 'GSO'),
        ],
        'PROCUREMENT' => [
            'code' => env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_PROCUREMENT', 'BAC'),
        ],
    ],

    'department_scopes' => [
        'CORE' => [
            'codes' => [
                env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_CORE', env('APP_DEFAULT_DEPARTMENT_CODE', 'ITO')),
            ],
        ],
        'TASKS' => [
            'codes' => [
                env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_TASKS', env('APP_DEFAULT_DEPARTMENT_CODE', 'ITO')),
            ],
        ],
        'DTS' => [
            'codes' => [
                env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_DTS', 'RECORDS'),
            ],
        ],
        'GSO' => [
            'codes' => [
                env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_GSO', 'GSO'),
            ],
        ],
        'PROCUREMENT' => [
            'codes' => [
                env('APP_MODULE_DEFAULT_DEPARTMENT_CODE_PROCUREMENT', 'BAC'),
            ],
        ],
    ],
];
