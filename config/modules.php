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
        'TASKS' => [
            'code' => 'TASKS',
            'name' => 'Tasks',
            'description' => 'Cross-module work queue and task orchestration.',
            'type' => 'support',
            'home_route' => 'tasks.index',
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
];
