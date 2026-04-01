<?php

return [
    'modules' => [
        'GSO' => [
            'setting_key' => 'storage.google_drive',
            'title' => 'GSO Google Drive Storage Plan',
            'description' => 'Manage the broad Google Drive roots that GSO uses for signed documents, AIR inspection evidence, and inventory item folders.',
            'notes' => [
                'Signed document PDFs should keep the document number as the canonical filename, such as AIR-2026-0001.pdf or RIS-2026-0001.pdf.',
                'AIR inspection evidence should live under AIR-number folders so item images and inspection uploads stay grouped by receiving record.',
                'Inventory item folders should be named by Property Number only, with supporting images and PDFs stored inside that item folder.',
            ],
            'fields' => [
                'signed_documents_root_folder_id' => [
                    'label' => 'Signed Documents Root Folder ID',
                    'help' => 'Root folder for canonical signed PDF printables across AIR, RIS, PAR, ICS, PTR, ITR, and WMR.',
                    'stored_keys' => [
                        'signed_documents_root_folder_id',
                        'air_files_folder_id',
                    ],
                    'fallback_config_keys' => [
                        'gso.storage.air_files_folder_id',
                    ],
                    'examples' => [
                        'AIR/{AIR_NO}/{AIR_NO}.pdf',
                        'RIS/{RIS_NO}/{RIS_NO}.pdf',
                        'PAR/{PAR_NO}/{PAR_NO}.pdf',
                        'ICS/{ICS_NO}/{ICS_NO}.pdf',
                    ],
                ],
                'air_inspections_root_folder_id' => [
                    'label' => 'AIR Inspections Root Folder ID',
                    'help' => 'Root folder for AIR receiving and inspection evidence, including AIR-level images and per-unit inspection uploads.',
                    'stored_keys' => [
                        'air_inspections_root_folder_id',
                        'inspection_photos_folder_id',
                        'air_unit_files_folder_id',
                    ],
                    'fallback_config_keys' => [
                        'gso.storage.inspection_photos_folder_id',
                        'gso.storage.air_unit_files_folder_id',
                    ],
                    'examples' => [
                        '{AIR_NO}/inspection-images/{generated-file-name}.jpg',
                        '{AIR_NO}/unit-files/{AIR_ITEM_LABEL}-{UNIT_LABEL}/{generated-file-name}.jpg',
                    ],
                ],
                'inventory_items_root_folder_id' => [
                    'label' => 'Inventory Items Root Folder ID',
                    'help' => 'Root folder for promoted inventory items. Each property number should get its own folder for images, PDFs, and copied supporting files.',
                    'stored_keys' => [
                        'inventory_items_root_folder_id',
                        'inventory_files_folder_id',
                    ],
                    'fallback_config_keys' => [
                        'gso.storage.inventory_files_folder_id',
                    ],
                    'examples' => [
                        '{PROPERTY_NO}/images/{generated-file-name}.jpg',
                        '{PROPERTY_NO}/files/{generated-file-name}.pdf',
                    ],
                ],
            ],
        ],
    ],
];
