<?php

namespace App\Modules\GSO\Builders;

use App\Core\Models\Department;
use App\Modules\GSO\Builders\Contracts\DepartmentDatatableRowBuilderInterface;

class DepartmentDatatableRowBuilder implements DepartmentDatatableRowBuilderInterface
{
    public function build(Department $department): array
    {
        $isArchived = $department->deleted_at !== null;

        return [
            'id' => (string) $department->id,
            'code' => (string) $department->code,
            'name' => (string) $department->name,
            'short_name' => $this->nullableString($department->short_name),
            'type' => $this->nullableString($department->type),
            'is_active' => (bool) $department->is_active,
            'is_active_text' => $department->is_active ? 'Active' : 'Inactive',
            'created_at' => $department->created_at?->toDateTimeString(),
            'created_at_text' => $department->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $department->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $department->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->departmentLabel($department),
        ];
    }

    private function departmentLabel(Department $department): string
    {
        $code = trim((string) $department->code);
        $name = trim((string) $department->name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Department');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
