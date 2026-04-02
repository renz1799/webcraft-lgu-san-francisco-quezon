<?php

namespace App\Modules\GSO\Builders\Air;

use App\Modules\GSO\Builders\Contracts\Air\AirDatatableRowBuilderInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Support\Air\AirStatuses;

class AirDatatableRowBuilder implements AirDatatableRowBuilderInterface
{
    public function build(Air $air): array
    {
        $department = $air->relationLoaded('department') ? $air->department : null;
        $fundSource = $air->relationLoaded('fundSource') ? $air->fundSource : null;
        $creator = $air->relationLoaded('creator') ? $air->creator : null;
        $isArchived = $air->deleted_at !== null;
        $isFollowUp = $air->parent_air_id !== null;
        $propertyPromotableUnits = max(0, (int) ($air->property_promotable_units_count ?? 0));
        $propertyPendingUnits = max(0, (int) ($air->property_pending_units_count ?? 0));
        $consumablePromotableLines = max(0, (int) ($air->consumable_promotable_lines_count ?? 0));
        $consumablePendingLines = max(0, (int) ($air->consumable_pending_lines_count ?? 0));
        $promotionEligibleCount = $propertyPromotableUnits + $consumablePromotableLines;
        $promotionPendingCount = $propertyPendingUnits + $consumablePendingLines;
        [$promotionStatus, $promotionStatusText] = $this->promotionStatusMeta(
            status: (string) ($air->status ?? ''),
            eligibleCount: $promotionEligibleCount,
            pendingCount: $promotionPendingCount,
        );

        return [
            'id' => (string) $air->id,
            'parent_air_id' => $this->nullableString($air->parent_air_id),
            'continuation_no' => (int) ($air->continuation_no ?? 1),
            'continuation_label' => $isFollowUp
                ? 'Follow-up #' . max(1, (int) ($air->continuation_no ?? 1))
                : 'Root AIR',
            'is_follow_up' => $isFollowUp,
            'po_number' => $this->nullableString($air->po_number),
            'po_date' => $air->po_date?->toDateString(),
            'po_date_text' => $air->po_date?->format('M d, Y') ?? '-',
            'air_number' => $this->nullableString($air->air_number),
            'air_date' => $air->air_date?->toDateString(),
            'air_date_text' => $air->air_date?->format('M d, Y') ?? '-',
            'invoice_number' => $this->nullableString($air->invoice_number),
            'invoice_date' => $air->invoice_date?->toDateString(),
            'invoice_date_text' => $air->invoice_date?->format('M d, Y') ?? '-',
            'supplier_name' => $this->nullableString($air->supplier_name),
            'requesting_department_id' => $this->nullableString($air->requesting_department_id),
            'requesting_department_name_snapshot' => $this->nullableString($air->requesting_department_name_snapshot),
            'requesting_department_code_snapshot' => $this->nullableString($air->requesting_department_code_snapshot),
            'department_label' => $this->departmentLabel(
                snapshotCode: $air->requesting_department_code_snapshot,
                snapshotName: $air->requesting_department_name_snapshot,
                relatedCode: $department?->code,
                relatedName: $department?->name,
            ),
            'fund_source_id' => $this->nullableString($air->fund_source_id),
            'fund_source_label' => $this->fundSourceLabel(
                code: $fundSource?->code,
                name: $fundSource?->name,
                fallback: $air->fund,
            ),
            'fund' => $this->nullableString($air->fund),
            'status' => (string) ($air->status ?? ''),
            'status_text' => AirStatuses::label((string) ($air->status ?? '')),
            'date_received' => $air->date_received?->toDateString(),
            'date_received_text' => $air->date_received?->format('M d, Y') ?? '-',
            'received_completeness' => $this->nullableString($air->received_completeness),
            'received_completeness_text' => $this->simpleLabel($air->received_completeness),
            'received_notes' => $this->nullableString($air->received_notes),
            'date_inspected' => $air->date_inspected?->toDateString(),
            'date_inspected_text' => $air->date_inspected?->format('M d, Y') ?? '-',
            'inspection_verified' => $air->inspection_verified,
            'inspection_verified_text' => $air->inspection_verified === null
                ? 'Pending'
                : ($air->inspection_verified ? 'Verified' : 'Needs Review'),
            'inspection_notes' => $this->nullableString($air->inspection_notes),
            'inspected_by_name' => $this->nullableString($air->inspected_by_name),
            'accepted_by_name' => $this->nullableString($air->accepted_by_name),
            'property_promotable_units_count' => $propertyPromotableUnits,
            'property_pending_units_count' => $propertyPendingUnits,
            'consumable_promotable_lines_count' => $consumablePromotableLines,
            'consumable_pending_lines_count' => $consumablePendingLines,
            'promotion_eligible_count' => $promotionEligibleCount,
            'promotion_pending_count' => $promotionPendingCount,
            'promotion_status' => $promotionStatus,
            'promotion_status_text' => $promotionStatusText,
            'created_by_user_id' => $this->nullableString($air->created_by_user_id),
            'created_by_name_snapshot' => $this->nullableString($air->created_by_name_snapshot),
            'created_by_label' => $this->creatorLabel(
                snapshotName: $air->created_by_name_snapshot,
                username: $creator?->username,
                email: $creator?->email,
            ),
            'remarks' => $this->nullableString($air->remarks),
            'created_at' => $air->created_at?->toDateTimeString(),
            'created_at_text' => $air->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $air->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $air->deleted_at?->format('M d, Y h:i A'),
            'is_archived' => $isArchived,
            'record_status' => $isArchived ? 'archived' : 'active',
            'label' => $this->rowLabel($air),
            'can_submit' => ! $isArchived && (string) ($air->status ?? '') === AirStatuses::DRAFT,
        ];
    }

    private function rowLabel(Air $air): string
    {
        $poNumber = trim((string) ($air->po_number ?? ''));
        $airNumber = trim((string) ($air->air_number ?? ''));

        if ($poNumber !== '' && $airNumber !== '') {
            return sprintf('%s / %s', $poNumber, $airNumber);
        }

        return $poNumber !== '' ? $poNumber : ($airNumber !== '' ? $airNumber : 'AIR Record');
    }

    private function departmentLabel(
        mixed $snapshotCode,
        mixed $snapshotName,
        mixed $relatedCode,
        mixed $relatedName,
    ): string {
        $snapshotCode = trim((string) ($snapshotCode ?? ''));
        $snapshotName = trim((string) ($snapshotName ?? ''));
        $relatedCode = trim((string) ($relatedCode ?? ''));
        $relatedName = trim((string) ($relatedName ?? ''));

        if ($snapshotCode !== '' && $snapshotName !== '') {
            return "{$snapshotCode} - {$snapshotName}";
        }

        if ($snapshotName !== '') {
            return $snapshotName;
        }

        if ($relatedCode !== '' && $relatedName !== '') {
            return "{$relatedCode} - {$relatedName}";
        }

        return $relatedCode !== '' ? $relatedCode : ($relatedName !== '' ? $relatedName : 'None');
    }

    private function fundSourceLabel(mixed $code, mixed $name, mixed $fallback): string
    {
        $code = trim((string) ($code ?? ''));
        $name = trim((string) ($name ?? ''));
        $fallback = trim((string) ($fallback ?? ''));

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        if ($name !== '') {
            return $name;
        }

        if ($code !== '') {
            return $code;
        }

        return $fallback !== '' ? $fallback : 'None';
    }

    private function creatorLabel(mixed $snapshotName, mixed $username, mixed $email): string
    {
        $snapshotName = trim((string) ($snapshotName ?? ''));
        $username = trim((string) ($username ?? ''));
        $email = trim((string) ($email ?? ''));

        if ($snapshotName !== '') {
            return $snapshotName;
        }

        if ($username !== '' && $email !== '') {
            return "{$username} ({$email})";
        }

        return $username !== '' ? $username : ($email !== '' ? $email : 'System User');
    }

    private function simpleLabel(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? ucwords(str_replace('_', ' ', $value)) : 'None';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function promotionStatusMeta(string $status, int $eligibleCount, int $pendingCount): array
    {
        if ($status !== AirStatuses::INSPECTED || $eligibleCount <= 0) {
            return ['not_eligible', 'Not Eligible'];
        }

        if ($pendingCount > 0) {
            return ['pending', $pendingCount . ' Pending'];
        }

        return ['fully_promoted', 'Fully Promoted'];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
