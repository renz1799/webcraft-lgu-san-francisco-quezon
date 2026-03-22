<?php

return [
    'storage' => [
        'inspection_photos_folder_id' => env(
            'GSO_INSPECTION_PHOTOS_FOLDER_ID',
            env('GOOGLE_DRIVE_FOLDER_ID')
        ),
        'air_unit_files_folder_id' => env(
            'GSO_AIR_UNIT_FILES_FOLDER_ID',
            env('GOOGLE_DRIVE_FOLDER_ID')
        ),
        'air_files_folder_id' => env(
            'GSO_AIR_FILES_FOLDER_ID',
            env('GOOGLE_DRIVE_FOLDER_ID')
        ),
        'inventory_files_folder_id' => env(
            'GSO_INVENTORY_FILES_FOLDER_ID',
            env('GOOGLE_DRIVE_FOLDER_ID')
        ),
    ],

    'pool' => [
        'department_id' => env('GSO_POOL_DEPARTMENT_ID', env('GSO_DEPARTMENT_ID')),
        'department_code' => env('GSO_POOL_DEPARTMENT_CODE', 'GSO'),
        'accountable_officer_name' => env('GSO_POOL_ACCOUNTABLE_OFFICER_NAME', 'GSO Pool'),
    ],

    'inventory' => [
        'ics_unit_cost_threshold' => (float) env('GSO_ICS_UNIT_COST_THRESHOLD', 50000),
    ],

    'legacy' => [
        'connection' => env('GSO_LEGACY_DB_CONNECTION', 'gso_legacy'),
        'reference_path' => env(
            'GSO_LEGACY_REFERENCE_PATH',
            'C:\\Webcraft Projects\\GSO San Francisco Quezon\\GSO San Francisco Quezon'
        ),

        'waves' => [
            1 => [
                'label' => 'Reference Data and Inventory Foundation',
                'tables' => [
                    ['table' => 'asset_types', 'label' => 'Asset Types'],
                    ['table' => 'asset_categories', 'label' => 'Asset Categories'],
                    ['table' => 'departments', 'label' => 'Departments'],
                    ['table' => 'fund_clusters', 'label' => 'Fund Clusters'],
                    ['table' => 'fund_sources', 'label' => 'Fund Sources'],
                    ['table' => 'accountable_officers', 'label' => 'Accountable Officers'],
                    ['table' => 'items', 'label' => 'Items'],
                    ['table' => 'item_unit_conversions', 'label' => 'Item Unit Conversions'],
                    ['table' => 'inventory_items', 'label' => 'Inventory Items'],
                    ['table' => 'inventory_item_files', 'label' => 'Inventory Item Files'],
                    ['table' => 'inventory_item_events', 'label' => 'Inventory Item Events'],
                    ['table' => 'inventory_item_event_files', 'label' => 'Inventory Item Event Files'],
                    ['table' => 'inspections', 'label' => 'Inspections'],
                    ['table' => 'inspection_photos', 'label' => 'Inspection Photos'],
                    ['table' => 'stocks', 'label' => 'Stocks'],
                    ['table' => 'stock_movements', 'label' => 'Stock Movements'],
                ],
            ],
            2 => [
                'label' => 'AIR and AIR-to-Inventory Flows',
                'tables' => [
                    ['table' => 'airs', 'label' => 'AIR Documents'],
                    ['table' => 'air_items', 'label' => 'AIR Items'],
                    ['table' => 'air_item_units', 'label' => 'AIR Item Units'],
                    ['table' => 'air_files', 'label' => 'AIR Files'],
                    ['table' => 'air_item_unit_files', 'label' => 'AIR Unit Files'],
                    ['table' => 'air_item_unit_components', 'label' => 'AIR Unit Components'],
                    ['table' => 'item_component_templates', 'label' => 'Item Component Templates'],
                    ['table' => 'inventory_item_components', 'label' => 'Inventory Item Components'],
                ],
            ],
            3 => [
                'label' => 'Downstream Document Flows',
                'tables' => [
                    ['table' => 'ris', 'label' => 'RIS Documents'],
                    ['table' => 'ris_items', 'label' => 'RIS Items'],
                    ['table' => 'ris_files', 'label' => 'RIS Files'],
                    ['table' => 'pars', 'label' => 'PAR Documents'],
                    ['table' => 'par_items', 'label' => 'PAR Items'],
                    ['table' => 'ics', 'label' => 'ICS Documents'],
                    ['table' => 'ics_items', 'label' => 'ICS Items'],
                    ['table' => 'ptrs', 'label' => 'PTR Documents'],
                    ['table' => 'ptr_items', 'label' => 'PTR Items'],
                    ['table' => 'itrs', 'label' => 'ITR Documents'],
                    ['table' => 'itr_items', 'label' => 'ITR Items'],
                    ['table' => 'wmrs', 'label' => 'WMR Documents'],
                    ['table' => 'wmr_items', 'label' => 'WMR Items'],
                    ['table' => 'document_number_counters', 'label' => 'Document Number Counters'],
                    ['table' => 'item_unit_conversions', 'label' => 'Item Unit Conversions'],
                ],
            ],
        ],

        'shared_tables' => [
            'users' => 'users',
            'user_profiles' => 'user_profiles',
            'login_details' => 'login_details',
            'audit_logs' => 'audit_logs',
            'notifications' => 'notifications',
            'tasks' => 'tasks',
            'task_events' => 'task_events',
            'google_tokens' => 'google_tokens',
            'roles' => 'roles',
            'permissions' => 'permissions',
            'role_has_permissions' => 'role_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'model_has_permissions' => 'model_has_permissions',
            'app_settings' => 'app_settings',
        ],
    ],
];
