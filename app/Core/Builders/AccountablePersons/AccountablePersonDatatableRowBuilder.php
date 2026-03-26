<?php

namespace App\Core\Builders\AccountablePersons;

use App\Core\Builders\Contracts\AccountablePersons\AccountablePersonDatatableRowBuilderInterface;
use App\Core\Models\AccountablePerson;

class AccountablePersonDatatableRowBuilder implements AccountablePersonDatatableRowBuilderInterface
{
    public function build(AccountablePerson $accountablePerson): array
    {
        $isArchived = $accountablePerson->deleted_at !== null;
        $department = $accountablePerson->relationLoaded('department') ? $accountablePerson->department : null;

        return [
            'id' => (string) $accountablePerson->id,
            'full_name' => (string) $accountablePerson->full_name,
            'designation' => $this->nullableString($accountablePerson->designation),
            'office' => $this->nullableString($accountablePerson->office),
            'department_id' => $accountablePerson->department_id ? (string) $accountablePerson->department_id : null,
            'department_label' => $department
                ? $this->departmentLabel((string) $department->code, (string) $department->name)
                : 'None',
            'department' => $department ? [
                'id' => (string) $department->id,
                'code' => (string) $department->code,
                'name' => (string) $department->name,
                'short_name' => $this->nullableString($department->short_name),
            ] : null,
            'is_active' => (bool) $accountablePerson->is_active,
            'is_active_text' => $accountablePerson->is_active ? 'Active' : 'Inactive',
            'created_at' => $accountablePerson->created_at?->toDateTimeString(),
            'created_at_text' => $accountablePerson->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $accountablePerson->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $accountablePerson->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->accountablePersonLabel($accountablePerson),
        ];
    }

    private function accountablePersonLabel(AccountablePerson $accountablePerson): string
    {
        $fullName = trim((string) $accountablePerson->full_name);
        $designation = trim((string) ($accountablePerson->designation ?? ''));

        if ($fullName !== '' && $designation !== '') {
            return "{$fullName} ({$designation})";
        }

        return $fullName !== '' ? $fullName : ($designation !== '' ? $designation : 'Accountable Person');
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
