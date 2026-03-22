<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Services\Concerns\BuildsInventoryItemReportSupport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractPhysicalCountReportService
{
    use BuildsInventoryItemReportSupport;

    public function getPrintViewData(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $asOf = null,
        bool $prefillCount = false,
        array $signatories = []
    ): array {
        $asOfDate = $this->resolveAsOfDate($asOf);

        $activeFundSources = $this->activeFundSources();
        $departments = $this->activeDepartments();
        $accountableOfficers = $this->activeAccountableOfficers();

        $selectedFund = $this->resolveSelectedFundSource($activeFundSources, $fundSourceId, $this->reportCode());
        $selectedDepartment = $this->resolveSelectedDepartment($departments, $departmentId, $this->reportCode());
        $selectedOfficer = $this->resolveSelectedOfficer($accountableOfficers, $accountableOfficerId, $this->reportCode());

        [$rows, $summary] = $this->buildRows(
            asOfDate: $asOfDate,
            selectedFund: $selectedFund,
            selectedDepartment: $selectedDepartment,
            selectedOfficer: $selectedOfficer,
            prefillCount: $prefillCount,
        );

        return [
            'report' => [
                'entity_name' => config('print.entity_name')
                    ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
                'appendix_label' => $this->reportCode(),
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
                'prefill_count' => $prefillCount,
                'summary' => $summary,
                'signatories' => $this->buildSignatories($signatories, $selectedOfficer),
            ],
            'rows' => $rows,
            ...$this->buildFilterOptions($activeFundSources, $departments, $accountableOfficers),
        ];
    }

    /**
     * @return array{0:array<int, array<string, mixed>>, 1:array<string, int|float|null>}
     */
    private function buildRows(
        Carbon $asOfDate,
        ?FundSource $selectedFund,
        ?Department $selectedDepartment,
        ?AccountableOfficer $selectedOfficer,
        bool $prefillCount
    ): array {
        $inventoryItems = $this->applyClassificationScope(
            $this->inventoryItemsBaseQuery($asOfDate, $selectedFund)
        )
            ->orderBy('property_number')
            ->orderBy('description')
            ->get();

        $rows = [];
        foreach ($inventoryItems as $inventoryItem) {
            if (! $this->matchesInventoryItem($inventoryItem)) {
                continue;
            }

            $snapshot = $this->resolveSnapshot($inventoryItem);
            if ($this->shouldExcludeSnapshot($snapshot)) {
                continue;
            }

            if ($selectedDepartment && (string) ($snapshot['department_id'] ?? '') !== (string) $selectedDepartment->id) {
                continue;
            }

            if ($selectedOfficer && ! $this->snapshotMatchesOfficer($inventoryItem, $snapshot, $selectedOfficer)) {
                continue;
            }

            $bookQty = max(1, (int) ($inventoryItem->quantity ?? 1));
            $bookValue = round((float) ($inventoryItem->acquisition_cost ?? 0), 2);
            $unitValue = $bookQty > 0
                ? round($bookValue / $bookQty, 2)
                : $bookValue;

            $countQty = $prefillCount ? $bookQty : null;
            $varianceQty = $countQty !== null ? $countQty - $bookQty : null;
            $varianceValue = $varianceQty !== null ? round($varianceQty * $unitValue, 2) : null;

            $rows[] = [
                'article' => $this->buildInventoryItemArticleLabel($inventoryItem, $this->itemFallbackLabel()),
                'description' => $this->buildInventoryItemDescription($inventoryItem, $this->itemFallbackLabel()),
                'property_no' => $this->nullableTrim($inventoryItem->property_number) ?? '',
                'unit' => $this->nullableTrim($inventoryItem->unit) ?? '',
                'unit_value' => $unitValue,
                'balance_per_card_qty' => $bookQty,
                'count_qty' => $countQty,
                'shortage_overage_qty' => $varianceQty,
                'shortage_overage_value' => $varianceValue,
                'remarks' => $this->buildSnapshotRemarks(
                    snapshot: $snapshot,
                    showOffice: ! $selectedDepartment,
                    showOfficer: ! $selectedOfficer,
                ),
                'office_label' => (string) ($snapshot['office_label'] ?? ''),
                'accountable_name' => (string) ($snapshot['accountable_name'] ?? ''),
                'fund_cluster_label' => $this->buildFundClusterLabel(
                    $inventoryItem->fundSource?->fundCluster?->code,
                    $inventoryItem->fundSource?->fundCluster?->name,
                ),
                'book_value' => $bookValue,
            ];
        }

        usort($rows, function (array $left, array $right): int {
            $leftKey = [
                mb_strtolower((string) ($left['office_label'] ?? '')),
                mb_strtolower((string) ($left['accountable_name'] ?? '')),
                mb_strtolower((string) ($left['article'] ?? '')),
                mb_strtolower((string) ($left['property_no'] ?? '')),
            ];

            $rightKey = [
                mb_strtolower((string) ($right['office_label'] ?? '')),
                mb_strtolower((string) ($right['accountable_name'] ?? '')),
                mb_strtolower((string) ($right['article'] ?? '')),
                mb_strtolower((string) ($right['property_no'] ?? '')),
            ];

            return $leftKey <=> $rightKey;
        });

        $summary = [
            'offices_covered' => count(array_unique(array_values(array_filter(array_map(
                fn (array $row): string => trim((string) ($row['office_label'] ?? '')),
                $rows
            ))))),
            'accountable_officers_covered' => count(array_unique(array_values(array_filter(array_map(
                fn (array $row): string => trim((string) ($row['accountable_name'] ?? '')),
                $rows
            ))))),
            'total_items' => count($rows),
            'total_balance_qty' => array_sum(array_map(
                fn (array $row): int => (int) ($row['balance_per_card_qty'] ?? 0),
                $rows
            )),
            'total_book_value' => round(array_sum(array_map(
                fn (array $row): float => (float) ($row['book_value'] ?? 0),
                $rows
            )), 2),
        ];

        $summary['total_count_qty'] = $prefillCount
            ? array_sum(array_map(
                fn (array $row): int => (int) ($row['count_qty'] ?? 0),
                $rows
            ))
            : null;

        $summary['total_shortage_overage_qty'] = $prefillCount
            ? array_sum(array_map(
                fn (array $row): int => (int) ($row['shortage_overage_qty'] ?? 0),
                $rows
            ))
            : null;

        $summary['total_shortage_overage_value'] = $prefillCount
            ? round(array_sum(array_map(
                fn (array $row): float => (float) ($row['shortage_overage_value'] ?? 0),
                $rows
            )), 2)
            : null;

        return [$rows, $summary];
    }

    private function buildSignatories(array $signatories, ?AccountableOfficer $selectedOfficer): array
    {
        $defaults = [
            'accountable_officer_name' => (string) ($selectedOfficer?->full_name ?? ''),
            'accountable_officer_designation' => (string) ($selectedOfficer?->designation ?? ''),
            'committee_chair_name' => '',
            'committee_member_1_name' => '',
            'committee_member_2_name' => '',
            'approved_by_name' => '',
            'approved_by_designation' => '',
            'verified_by_name' => '',
            'verified_by_designation' => '',
        ];

        $resolved = [];
        foreach ($defaults as $key => $defaultValue) {
            $resolved[$key] = $this->nullableTrim($signatories[$key] ?? $defaultValue) ?? '';
        }

        return $resolved;
    }

    abstract protected function reportCode(): string;

    abstract protected function itemFallbackLabel(): string;

    abstract protected function applyClassificationScope(Builder $query): Builder;

    abstract protected function matchesInventoryItem(InventoryItem $inventoryItem): bool;
}
