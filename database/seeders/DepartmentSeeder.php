<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $configuredDepartmentId = config('department.id');
        $configuredDepartmentCode = config('department.code');
        $configuredDepartmentName = config('department.name');

        if ($configuredDepartmentId) {
            Department::updateOrCreate(
                ['id' => $configuredDepartmentId],
                [
                    'code' => $configuredDepartmentCode,
                    'name' => $configuredDepartmentName,
                    'is_active' => true,
                ]
            );
        }

        $departments = [
            [
                'id' => '20000000-0000-0000-0000-000000000002',
                'code' => 'MAYOR',
                'name' => "Mayor's Office",
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000003',
                'code' => 'ADMIN',
                'name' => 'Administrator Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000004',
                'code' => 'HRMO',
                'name' => 'Human Resource Management Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000005',
                'code' => 'ACCOUNTING',
                'name' => 'Accounting Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000006',
                'code' => 'BUDGET',
                'name' => 'Budget Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000007',
                'code' => 'TREASURY',
                'name' => 'Treasury Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000008',
                'code' => 'GSO',
                'name' => 'General Services Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000009',
                'code' => 'ENGINEERING',
                'name' => 'Engineering Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000010',
                'code' => 'PLANNING',
                'name' => 'Planning and Development Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000011',
                'code' => 'HEALTH',
                'name' => 'Municipal Health Office',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000012',
                'code' => 'MSWD',
                'name' => 'Social Welfare and Development Office',
            ],
        ];

        foreach ($departments as $department) {
            if ($configuredDepartmentId && $department['id'] === $configuredDepartmentId) {
                continue;
            }

            Department::updateOrCreate(
                ['id' => $department['id']],
                [
                    'code' => $department['code'],
                    'name' => $department['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}