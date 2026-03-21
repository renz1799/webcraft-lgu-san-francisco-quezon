<?php

return [
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
