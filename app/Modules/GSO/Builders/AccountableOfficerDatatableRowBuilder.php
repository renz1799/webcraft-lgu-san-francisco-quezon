<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\AccountableOfficerDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AccountableOfficer;

class AccountableOfficerDatatableRowBuilder implements AccountableOfficerDatatableRowBuilderInterface
{
    public function build(AccountableOfficer $accountableOfficer): array
    {
        $isArchived = $accountableOfficer->deleted_at !== null;
        $department = $accountableOfficer->relationLoaded('department') ? $accountableOfficer->department : null;

        return [
            'id' => (string) $accountableOfficer->id,
            'full_name' => (string) $accountableOfficer->full_name,
            'designation' => $this->nullableString($accountableOfficer->designation),
            'office' => $this->nullableString($accountableOfficer->office),
            'department_id' => $accountableOfficer->department_id ? (string) $accountableOfficer->department_id : null,
            'department_label' => $department
                ? $this->departmentLabel((string) $department->code, (string) $department->name)
                : 'None',
            'department' => $department ? [
                'id' => (string) $department->id,
                'code' => (string) $department->code,
                'name' => (string) $department->name,
                'short_name' => $this->nullableString($department->short_name),
            ] : null,
            'is_active' => (bool) $accountableOfficer->is_active,
            'is_active_text' => $accountableOfficer->is_active ? 'Active' : 'Inactive',
            'created_at' => $accountableOfficer->created_at?->toDateTimeString(),
            'created_at_text' => $accountableOfficer->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $accountableOfficer->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $accountableOfficer->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->officerLabel($accountableOfficer),
        ];
    }

    private function officerLabel(AccountableOfficer $accountableOfficer): string
    {
        $fullName = trim((string) $accountableOfficer->full_name);
        $designation = trim((string) ($accountableOfficer->designation ?? ''));

        if ($fullName !== '' && $designation !== '') {
            return "{$fullName} ({$designation})";
        }

        return $fullName !== '' ? $fullName : ($designation !== '' ? $designation : 'Accountable Officer');
    }

    private function departmentLabel(string $code, string $name): string
    {
        $code = trim($code);
        $name = trim($name);

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
