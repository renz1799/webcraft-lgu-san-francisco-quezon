<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Services\Concerns\BuildsInventoryItemReportSupport;
use App\Modules\GSO\Services\Contracts\RegspiReportServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use Carbon\Carbon;

class RegspiReportService implements RegspiReportServiceInterface
{
    use BuildsInventoryItemReportSupport;

    public function getPrintViewData(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $asOf = null,
        array $signatories = []
    ): array {
        $asOfDate = $this->resolveAsOfDate($asOf);

        $activeFundSources = $this->activeFundSources();
        $departments = $this->activeDepartments();
        $accountableOfficers = $this->activeAccountableOfficers();

        $selectedFund = $this->resolveSelectedFundSource($activeFundSources, $fundSourceId, 'RegSPI');
        $selectedDepartment = $this->resolveSelectedDepartment($departments, $departmentId, 'RegSPI');
        $selectedOfficer = $this->resolveSelectedOfficer($accountableOfficers, $accountableOfficerId, 'RegSPI');

        [$rows, $summary] = $this->buildRows(
            asOfDate: $asOfDate,
            selectedFund: $selectedFund,
            selectedDepartment: $selectedDepartment,
            selectedOfficer: $selectedOfficer,
        );

        return [
            'report' => [
                'entity_name' => config('print.entity_name')
                    ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
                'appendix_label' => 'RegSPI',
                'fund_source_id' => $selectedFund?->id ? (string) $selectedFund->id : '',
                'department_id' => $selectedDepartment?->id ? (string) $selectedDepartment->id : '',
                'accountable_officer_id' => $selectedOfficer?->id ? (string) $selectedOfficer->id : '',
                'fund_source' => $selectedFund
                    ? $this->buildFundSourceLabel($selectedFund->code, $selectedFund->name)
                    : 'All Fund Sources',
                'fund_cluster' => $this->resolveFundClusterLabel($selectedFund, $rows),
                'department' => $selectedDepartment
                    ? $this->buildDepartmentScopeLabel($selectedDepartment)
                    : 'All Offices',
                'accountable_officer' => $selectedOfficer?->full_name ?: 'All Accountable Officers',
                'as_of' => $asOfDate->toDateString(),
                'as_of_label' => $asOfDate->format('F j, Y'),
                'summary' => $summary,
                'signatories' => $this->buildSignatories($signatories),
            ],
            'rows' => $rows,
            ...$this->buildFilterOptions($activeFundSources, $departments, $accountableOfficers),
        ];
    }

    /**
     * @return array{0:array<int, array<string, mixed>>, 1:array<string, int|float>}
     */
    private function buildRows(
        Carbon $asOfDate,
        ?FundSource $selectedFund,
        ?Department $selectedDepartment,
        ?AccountableOfficer $selectedOfficer
    ): array {
        $inventoryItems = $this->inventoryItemsBaseQuery($asOfDate, $selectedFund)
            ->where('is_ics', true)
            ->orderBy('property_number')
            ->orderBy('description')
            ->get();

        $rows = [];
        foreach ($inventoryItems as $inventoryItem) {
            /** @var InventoryItemEvent|null $latestEvent */
            $latestEvent = $inventoryItem->events->first();
            /** @var InventoryItemEvent|null $registerEvent */
            $registerEvent = $inventoryItem->events->first(function (InventoryItemEvent $event): bool {
                return in_array((string) $event->event_type, [
                    InventoryEventTypes::ISSUED,
                    InventoryEventTypes::TRANSFERRED_IN,
                ], true);
            });

            if ($this->shouldExcludeItem($inventoryItem, $latestEvent, $registerEvent)) {
                continue;
            }

            $currentDepartmentId = $this->nullableTrim($latestEvent?->department_id)
                ?? $this->nullableTrim($inventoryItem->department_id)
                ?? '';
            $currentOfficerName = $this->resolveAccountableOfficerSnapshotName($inventoryItem, $latestEvent);

            if ($selectedDepartment && $currentDepartmentId !== (string) $selectedDepartment->id) {
                continue;
            }

            if ($selectedOfficer && ! $this->matchesOfficer($inventoryItem, $currentOfficerName, $selectedOfficer)) {
                continue;
            }

            $qty = max(1, (int) ($inventoryItem->quantity ?? 1));
            $totalValue = round((float) ($inventoryItem->acquisition_cost ?? 0), 2);
            $unitValue = $qty > 0 ? round($totalValue / $qty, 2) : $totalValue;

            $rows[] = [
                'date' => $registerEvent?->event_date?->toDateString()
                    ?? $latestEvent?->event_date?->toDateString()
                    ?? $inventoryItem->acquisition_date?->toDateString()
                    ?? '',
                'reference' => $this->buildReferenceLabel($registerEvent ?? $latestEvent),
                'property_no' => $this->nullableTrim($inventoryItem->property_number) ?? '',
                'article' => $this->buildInventoryItemArticleLabel($inventoryItem, 'Semi-Expendable Property'),
                'description' => $this->buildInventoryItemDescription($inventoryItem, 'Semi-Expendable Property'),
                'qty' => $qty,
                'unit_value' => $unitValue,
                'office' => $this->nullableTrim($latestEvent?->office_snapshot)
                    ?? $this->buildDepartmentOfficeLabel($latestEvent?->department)
                    ?? $this->buildDepartmentOfficeLabel($inventoryItem->department)
                    ?? '',
                'accountable_officer' => $currentOfficerName,
                'remarks' => $this->buildRemarks($latestEvent, $inventoryItem),
                'fund_cluster_label' => $this->buildFundClusterLabel(
                    $inventoryItem->fundSource?->fundCluster?->code,
                    $inventoryItem->fundSource?->fundCluster?->name,
                ),
                'total_value' => $totalValue,
            ];
        }

        usort($rows, function (array $left, array $right): int {
            $leftKey = [
                mb_strtolower((string) ($left['office'] ?? '')),
                mb_strtolower((string) ($left['accountable_officer'] ?? '')),
                mb_strtolower((string) ($left['article'] ?? '')),
                mb_strtolower((string) ($left['property_no'] ?? '')),
            ];

            $rightKey = [
                mb_strtolower((string) ($right['office'] ?? '')),
                mb_strtolower((string) ($right['accountable_officer'] ?? '')),
                mb_strtolower((string) ($right['article'] ?? '')),
                mb_strtolower((string) ($right['property_no'] ?? '')),
            ];

            return $leftKey <=> $rightKey;
        });

        $summary = [
            'offices_covered' => count(array_unique(array_values(array_filter(array_map(
                fn (array $row): string => trim((string) ($row['office'] ?? '')),
                $rows
            ))))),
            'accountable_officers_covered' => count(array_unique(array_values(array_filter(array_map(
                fn (array $row): string => trim((string) ($row['accountable_officer'] ?? '')),
                $rows
            ))))),
            'total_items' => count($rows),
            'total_qty' => array_sum(array_map(
                fn (array $row): int => (int) ($row['qty'] ?? 0),
                $rows
            )),
            'total_value' => round(array_sum(array_map(
                fn (array $row): float => (float) ($row['total_value'] ?? 0),
                $rows
            )), 2),
        ];

        return [$rows, $summary];
    }

    private function shouldExcludeItem(
        InventoryItem $inventoryItem,
        ?InventoryItemEvent $latestEvent,
        ?InventoryItemEvent $registerEvent
    ): bool {
        if ((bool) ($inventoryItem->is_ics ?? false) !== true) {
            return true;
        }

        $latestEventType = $this->normalizeValue((string) ($latestEvent?->event_type ?? ''));
        $latestStatus = $this->normalizeValue((string) ($latestEvent?->status ?? $inventoryItem->status ?? ''));
        $custodyState = $this->normalizeValue((string) ($inventoryItem->custody_state ?? ''));

        if (in_array($latestStatus, ['disposed', 'lost'], true)) {
            return true;
        }

        if (in_array($latestEventType, [
            InventoryEventTypes::DISPOSED,
            InventoryEventTypes::RETURNED,
            InventoryEventTypes::TRANSFERRED_OUT,
        ], true)) {
            return true;
        }

        if ($registerEvent) {
            return false;
        }

        return $custodyState !== $this->normalizeValue(InventoryCustodyStates::ISSUED);
    }

    private function matchesOfficer(
        InventoryItem $inventoryItem,
        string $currentOfficerName,
        AccountableOfficer $selectedOfficer
    ): bool {
        if ((string) ($inventoryItem->accountable_officer_id ?? '') === (string) $selectedOfficer->id) {
            return true;
        }

        return $this->normalizeValue($currentOfficerName) === $this->normalizeValue((string) ($selectedOfficer->full_name ?? ''));
    }

    private function buildRemarks(?InventoryItemEvent $latestEvent, InventoryItem $inventoryItem): string
    {
        $parts = [];

        $status = $this->formatSimpleLabel((string) ($latestEvent?->status ?? $inventoryItem->status ?? ''));
        if ($status !== null && $this->normalizeValue($status) !== 'serviceable') {
            $parts[] = 'Status: ' . $status;
        }

        $condition = $this->formatSimpleLabel((string) ($latestEvent?->condition ?? $inventoryItem->condition ?? ''));
        if ($condition !== null) {
            $parts[] = 'Condition: ' . $condition;
        }

        return implode(' | ', $parts);
    }

    private function buildSignatories(array $signatories): array
    {
        $defaults = [
            'prepared_by_name' => (string) config('gso.gso_designate_name', ''),
            'prepared_by_designation' => (string) config('gso.gso_designate_designation', ''),
            'reviewed_by_name' => '',
            'reviewed_by_designation' => '',
            'approved_by_name' => '',
            'approved_by_designation' => '',
        ];

        $resolved = [];
        foreach ($defaults as $key => $defaultValue) {
            $resolved[$key] = $this->nullableTrim($signatories[$key] ?? $defaultValue) ?? '';
        }

        return $resolved;
    }
}
