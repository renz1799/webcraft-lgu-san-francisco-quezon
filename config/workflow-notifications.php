<?php

return [
    'modules' => [
        'GSO' => [
            'setting_key' => 'workflow.notifications',
            'title' => 'GSO Workflow Notification Rules',
            'description' => 'Choose which GSO roles receive workflow notifications for major document and inspection events.',
            'notes' => [
                'Rules are separated by module so each office workflow can notify its own operational roles without affecting the rest of the platform.',
                'Leaving an event on its seeded role set keeps the default workflow rule. Clearing every role from an event disables notifications for that event.',
                'This page controls role fan-out only. The actual action still follows the workflow-specific permission and assignment rules.',
            ],
            'events' => [
                'air.submitted' => [
                    'label' => 'AIR Submitted',
                    'description' => 'Sent when an AIR draft is submitted and inspection work is ready to be claimed or reviewed.',
                    'default_roles' => ['Inspector'],
                    'message_template' => '{air_label} is submitted and ready for inspection review. Click to open the assigned task and continue the workflow.',
                    'placeholders' => [
                        '{air_label}' => 'Combined AIR label such as PO number and AIR number.',
                        '{air_number}' => 'AIR document number.',
                        '{po_number}' => 'Purchase order number.',
                        '{task_url}' => 'Module task URL for the assigned or claimable inspection task.',
                        '{inspection_url}' => 'AIR inspection workspace URL.',
                        '{actor_name}' => 'Name of the user who triggered the event.',
                    ],
                ],
                'air.inspection_finalized' => [
                    'label' => 'AIR Inspection Finalized',
                    'description' => 'Sent when an AIR inspection is finalized and the record is ready for post-inspection workflow steps.',
                    'default_roles' => ['Administrator', 'Staff'],
                    'message_template' => '{air_label} inspection is finalized. Click to open the AIR inspection record and continue the next workflow step.',
                    'placeholders' => [
                        '{air_label}' => 'Combined AIR label such as PO number and AIR number.',
                        '{air_number}' => 'AIR document number.',
                        '{po_number}' => 'Purchase order number.',
                        '{inspection_url}' => 'AIR inspection workspace URL.',
                        '{actor_name}' => 'Name of the user who triggered the event.',
                    ],
                ],
                'air.follow_up_created' => [
                    'label' => 'AIR Follow-up Created',
                    'description' => 'Sent when a follow-up AIR draft is created from unresolved inspection items.',
                    'default_roles' => ['Administrator', 'Staff'],
                    'message_template' => '{air_label} follow-up AIR is created for unresolved inspection items. Click to open the assigned task and continue the workflow.',
                    'placeholders' => [
                        '{air_label}' => 'Combined follow-up AIR label such as PO number and AIR number when available.',
                        '{air_number}' => 'Follow-up AIR document number.',
                        '{po_number}' => 'Purchase order number.',
                        '{source_air_label}' => 'Source AIR label that produced the follow-up draft.',
                        '{task_url}' => 'Module task URL for the follow-up AIR task.',
                        '{follow_up_url}' => 'Follow-up AIR draft workspace URL.',
                        '{actor_name}' => 'Name of the user who triggered the event.',
                    ],
                ],
            ],
        ],
    ],
];
