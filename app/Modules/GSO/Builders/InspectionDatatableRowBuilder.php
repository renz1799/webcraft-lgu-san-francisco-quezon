<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\InspectionDatatableRowBuilderInterface;
use App\Modules\GSO\Models\Inspection;
use App\Modules\GSO\Support\InspectionStatuses;
use App\Modules\GSO\Support\InventoryConditions;

class InspectionDatatableRowBuilder implements InspectionDatatableRowBuilderInterface
{
    public function build(Inspection $inspection): array
    {
        $item = $inspection->relationLoaded('item') ? $inspection->item : null;
        $department = $inspection->relationLoaded('department') ? $inspection->department : null;
        $inspector = $inspection->relationLoaded('inspector') ? $inspection->inspector : null;
        $reviewer = $inspection->relationLoaded('reviewer') ? $inspection->reviewer : null;
        $isArchived = $inspection->deleted_at !== null;

        return [
            'id' => (string) $inspection->id,
            'inspector_user_id' => $this->nullableString($inspection->inspector_user_id),
            'reviewer_user_id' => $this->nullableString($inspection->reviewer_user_id),
            'inspector_label' => $this->userLabel($inspector?->username, $inspector?->email),
            'reviewer_label' => $this->userLabel($reviewer?->username, $reviewer?->email),
            'item_id' => $this->nullableString($inspection->item_id),
            'item_name' => $this->nullableString($inspection->item_name),
            'item_label' => $this->inspectionItemLabel(
                snapshotItemName: $inspection->item_name,
                relatedItemName: $item?->item_name,
                relatedIdentification: $item?->item_identification,
            ),
            'department_id' => $this->nullableString($inspection->department_id),
            'office_department' => $this->nullableString($inspection->office_department),
            'department_label' => $this->inspectionDepartmentLabel(
                officeDepartment: $inspection->office_department,
                relatedCode: $department?->code,
                relatedName: $department?->name,
            ),
            'accountable_officer' => $this->nullableString($inspection->accountable_officer),
            'dv_number' => $this->nullableString($inspection->dv_number),
            'po_number' => $this->nullableString($inspection->po_number),
            'observed_description' => $this->nullableString($inspection->observed_description),
            'brand' => $this->nullableString($inspection->brand),
            'model' => $this->nullableString($inspection->model),
            'serial_number' => $this->nullableString($inspection->serial_number),
            'acquisition_date' => $inspection->acquisition_date?->toDateString(),
            'acquisition_date_text' => $inspection->acquisition_date?->format('M d, Y') ?? '-',
            'acquisition_cost' => $inspection->acquisition_cost !== null
                ? (string) $inspection->acquisition_cost
                : null,
            'acquisition_cost_text' => $inspection->acquisition_cost !== null
                ? number_format((float) $inspection->acquisition_cost, 2)
                : '-',
            'quantity' => (int) ($inspection->quantity ?? 0),
            'condition' => (string) ($inspection->condition ?? ''),
            'condition_text' => InventoryConditions::labels()[(string) ($inspection->condition ?? '')] ?? 'Unknown',
            'status' => (string) ($inspection->status ?? ''),
            'status_text' => InspectionStatuses::labels()[(string) ($inspection->status ?? '')] ?? 'Unknown',
            'photo_count' => (int) ($inspection->photos_count ?? 0),
            'drive_folder_id' => $this->nullableString($inspection->drive_folder_id),
            'remarks' => $this->nullableString($inspection->remarks),
            'created_at' => $inspection->created_at?->toDateTimeString(),
            'created_at_text' => $inspection->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $inspection->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $inspection->deleted_at?->format('M d, Y h:i A'),
            'status_mode' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->inspectionLabel(
                itemLabel: $this->inspectionItemLabel(
                    snapshotItemName: $inspection->item_name,
                    relatedItemName: $item?->item_name,
                    relatedIdentification: $item?->item_identification,
                ),
                poNumber: $inspection->po_number,
            ),
        ];
    }

    private function inspectionItemLabel(mixed $snapshotItemName, mixed $relatedItemName, mixed $relatedIdentification): string
    {
        $snapshotItemName = trim((string) ($snapshotItemName ?? ''));
        $relatedItemName = trim((string) ($relatedItemName ?? ''));
        $relatedIdentification = trim((string) ($relatedIdentification ?? ''));

        if ($snapshotItemName !== '') {
            return $snapshotItemName;
        }

        if ($relatedItemName !== '' && $relatedIdentification !== '') {
            return "{$relatedItemName} ({$relatedIdentification})";
        }

        return $relatedItemName !== '' ? $relatedItemName : ($relatedIdentification !== '' ? $relatedIdentification : 'Inspection');
    }

    private function inspectionDepartmentLabel(mixed $officeDepartment, mixed $relatedCode, mixed $relatedName): string
    {
        $officeDepartment = trim((string) ($officeDepartment ?? ''));
        $relatedCode = trim((string) ($relatedCode ?? ''));
        $relatedName = trim((string) ($relatedName ?? ''));

        if ($officeDepartment !== '') {
            return $officeDepartment;
        }

        if ($relatedCode !== '' && $relatedName !== '') {
            return "{$relatedCode} - {$relatedName}";
        }

        return $relatedCode !== '' ? $relatedCode : ($relatedName !== '' ? $relatedName : 'None');
    }

    private function userLabel(mixed $username, mixed $email): ?string
    {
        $username = trim((string) ($username ?? ''));
        $email = trim((string) ($email ?? ''));

        if ($username !== '' && $email !== '') {
            return "{$username} ({$email})";
        }

        return $username !== '' ? $username : ($email !== '' ? $email : null);
    }

    private function inspectionLabel(string $itemLabel, mixed $poNumber): string
    {
        $itemLabel = trim($itemLabel);
        $poNumber = trim((string) ($poNumber ?? ''));

        if ($itemLabel !== '' && $poNumber !== '') {
            return "{$itemLabel} ({$poNumber})";
        }

        return $poNumber !== '' ? $poNumber : ($itemLabel !== '' ? $itemLabel : 'Inspection');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
