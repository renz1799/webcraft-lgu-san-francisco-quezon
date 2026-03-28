<?php

namespace App\Modules\GSO\Services\Concerns;

use App\Core\Models\Department;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Support\InventoryEventTypes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait BuildsInventoryItemReportSupport
{
    protected function resolveAsOfDate(?string $asOf): Carbon
    {
        $asOf = $this->nullableTrim($asOf);

        return $asOf !== null
            ? Carbon::parse($asOf)->startOfDay()
            : Carbon::today();
    }

    protected function activeFundSources(): Collection
    {
        return FundSource::query()
            ->with('fundCluster:id,code,name')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN LOWER(COALESCE(code, '')) = 'gf' THEN 0 ELSE 1 END")
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'fund_cluster_id']);
    }

    protected function activeDepartments(): Collection
    {
        return Department::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'short_name']);
    }

    protected function activeAccountableOfficers(): Collection
    {
        return AccountableOfficer::query()
            ->with([
                'department' => fn ($query) => $query
                    ->select(['id', 'code', 'name', 'short_name']),
            ])
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'designation', 'department_id']);
    }

    protected function resolveSelectedFundSource(Collection $fundSources, ?string $fundSourceId, string $reportCode): ?FundSource
    {
        $fundSourceId = $this->nullableTrim($fundSourceId);
        if ($fundSourceId === null) {
            return null;
        }

        /** @var FundSource|null $selectedFund */
        $selectedFund = $fundSources->firstWhere('id', $fundSourceId);
        abort_if(! $selectedFund, 404, "The selected fund source is not available for {$reportCode} printing.");

        return $selectedFund;
    }

    protected function resolveSelectedDepartment(Collection $departments, ?string $departmentId, string $reportCode): ?Department
    {
        $departmentId = $this->nullableTrim($departmentId);
        if ($departmentId === null) {
            return null;
        }

        /** @var Department|null $selectedDepartment */
        $selectedDepartment = $departments->firstWhere('id', $departmentId);
        abort_if(! $selectedDepartment, 404, "The selected office is not available for {$reportCode} printing.");

        return $selectedDepartment;
    }

    protected function resolveSelectedOfficer(Collection $accountableOfficers, ?string $accountableOfficerId, string $reportCode): ?AccountableOfficer
    {
        $accountableOfficerId = $this->nullableTrim($accountableOfficerId);
        if ($accountableOfficerId === null) {
            return null;
        }

        /** @var AccountableOfficer|null $selectedOfficer */
        $selectedOfficer = $accountableOfficers->firstWhere('id', $accountableOfficerId);
        abort_if(! $selectedOfficer, 404, "The selected accountable officer is not available for {$reportCode} printing.");

        return $selectedOfficer;
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    protected function buildFilterOptions(Collection $fundSources, Collection $departments, Collection $accountableOfficers): array
    {
        return [
            'available_funds' => $fundSources
                ->map(fn (FundSource $fund): array => [
                    'id' => (string) $fund->id,
                    'label' => $this->buildFundSourceLabel($fund->code, $fund->name),
                ])
                ->values()
                ->all(),
            'available_departments' => $departments
                ->map(fn (Department $department): array => [
                    'id' => (string) $department->id,
                    'label' => $this->buildDepartmentScopeLabel($department),
                ])
                ->values()
                ->all(),
            'available_accountable_officers' => $accountableOfficers
                ->map(fn (AccountableOfficer $officer): array => [
                    'id' => (string) $officer->id,
                    'label' => $this->buildAccountableOfficerOptionLabel($officer),
                    'full_name' => (string) ($officer->full_name ?? ''),
                    'designation' => (string) ($officer->designation ?? ''),
                    'department_id' => (string) ($officer->department_id ?? ''),
                ])
                ->values()
                ->all(),
        ];
    }

    protected function inventoryItemsBaseQuery(Carbon $asOfDate, ?FundSource $selectedFund): Builder
    {
        return InventoryItem::query()
            ->with([
                'item:id,item_name,tracking_type',
                'department:id,code,name,short_name',
                'fundSource:id,code,name,fund_cluster_id',
                'fundSource.fundCluster:id,code,name',
                'accountableOfficerRelation:id,full_name,designation,department_id',
                'events' => fn ($query) => $query
                    ->with([
                        'department:id,code,name,short_name',
                        'fundSource:id,code,name,fund_cluster_id',
                        'fundSource.fundCluster:id,code,name',
                    ])
                    ->whereDate('event_date', '<=', $asOfDate->toDateString())
                    ->orderByDesc('event_date')
                    ->orderByDesc('created_at'),
            ])
            ->whereDate('acquisition_date', '<=', $asOfDate->toDateString())
            ->when($selectedFund, fn (Builder $query) => $query->where('fund_source_id', $selectedFund->id));
    }

    /**
     * @return array<string, string>
     */
    protected function resolveSnapshot(InventoryItem $inventoryItem): array
    {
        /** @var InventoryItemEvent|null $latestEvent */
        $latestEvent = $inventoryItem->events->first();

        return [
            'event_type' => $this->nullableTrim($latestEvent?->event_type) ?? '',
            'status' => $this->nullableTrim($latestEvent?->status) ?? $this->nullableTrim($inventoryItem->status) ?? '',
            'condition' => $this->nullableTrim($latestEvent?->condition) ?? $this->nullableTrim($inventoryItem->condition) ?? '',
            'department_id' => $this->nullableTrim($latestEvent?->department_id) ?? $this->nullableTrim($inventoryItem->department_id) ?? '',
            'office_label' => $this->nullableTrim($latestEvent?->office_snapshot)
                ?? $this->buildDepartmentOfficeLabel($latestEvent?->department)
                ?? $this->buildDepartmentOfficeLabel($inventoryItem->department)
                ?? '',
            'accountable_name' => $this->resolveAccountableOfficerSnapshotName($inventoryItem, $latestEvent),
        ];
    }

    protected function shouldExcludeSnapshot(array $snapshot): bool
    {
        $status = $this->normalizeValue((string) ($snapshot['status'] ?? ''));
        $eventType = $this->normalizeValue((string) ($snapshot['event_type'] ?? ''));

        return in_array($status, ['disposed', 'lost'], true)
            || $eventType === InventoryEventTypes::DISPOSED;
    }

    protected function snapshotMatchesOfficer(
        InventoryItem $inventoryItem,
        array $snapshot,
        AccountableOfficer $selectedOfficer
    ): bool {
        if ((string) ($inventoryItem->accountable_officer_id ?? '') === (string) $selectedOfficer->id) {
            return true;
        }

        $selectedName = $this->normalizeValue((string) ($selectedOfficer->full_name ?? ''));
        if ($selectedName === '') {
            return false;
        }

        $candidateNames = [
            (string) ($snapshot['accountable_name'] ?? ''),
            (string) ($inventoryItem->accountable_officer ?? ''),
            (string) ($inventoryItem->accountableOfficerRelation?->full_name ?? ''),
        ];

        foreach ($candidateNames as $candidateName) {
            if ($this->normalizeValue($candidateName) === $selectedName) {
                return true;
            }
        }

        return false;
    }

    protected function buildReferenceLabel(?InventoryItemEvent $event): string
    {
        if (! $event) {
            return '';
        }

        $type = $this->nullableTrim($event->reference_type);
        $number = $this->nullableTrim($event->reference_no);

        if ($type !== null && $number !== null) {
            return sprintf('%s: %s', strtoupper($type), $number);
        }

        return $number ?? ($type !== null ? strtoupper($type) : '');
    }

    protected function buildInventoryItemArticleLabel(InventoryItem $inventoryItem, string $fallback): string
    {
        return $this->nullableTrim($inventoryItem->item?->item_name)
            ?? $this->nullableTrim($inventoryItem->description)
            ?? $fallback;
    }

    protected function buildInventoryItemDescription(InventoryItem $inventoryItem, string $fallback): string
    {
        $description = $this->nullableTrim($inventoryItem->description);
        $itemName = $this->nullableTrim($inventoryItem->item?->item_name);

        if ($description !== null && $itemName !== null && stripos($description, $itemName) === false) {
            $base = sprintf('%s - %s', $itemName, $description);
        } else {
            $base = $description ?? $itemName ?? $fallback;
        }

        $details = array_values(array_filter([
            $this->nullableTrim($inventoryItem->brand),
            $this->nullableTrim($inventoryItem->model),
            $this->nullableTrim($inventoryItem->serial_number) !== null
                ? 'SN: ' . $this->nullableTrim($inventoryItem->serial_number)
                : null,
        ]));

        return $details !== []
            ? $base . ' (' . implode(', ', $details) . ')'
            : $base;
    }

    protected function buildSnapshotRemarks(array $snapshot, bool $showOffice, bool $showOfficer): string
    {
        $parts = [];

        $status = $this->formatSimpleLabel((string) ($snapshot['status'] ?? ''));
        if ($status !== null && $this->normalizeValue($status) !== 'serviceable') {
            $parts[] = 'Status: ' . $status;
        }

        $condition = $this->formatSimpleLabel((string) ($snapshot['condition'] ?? ''));
        if ($condition !== null) {
            $parts[] = 'Condition: ' . $condition;
        }

        $officeLabel = trim((string) ($snapshot['office_label'] ?? ''));
        if ($showOffice && $officeLabel !== '') {
            $parts[] = 'Office: ' . $officeLabel;
        }

        $accountableName = trim((string) ($snapshot['accountable_name'] ?? ''));
        if ($showOfficer && $accountableName !== '') {
            $parts[] = 'Officer: ' . $accountableName;
        }

        return implode(' | ', $parts);
    }

    protected function buildFundSourceLabel(?string $code, ?string $name): string
    {
        $code = $this->nullableTrim($code);
        $name = $this->nullableTrim($name);

        if ($code !== null && $name !== null) {
            return sprintf('%s - %s', $code, $name);
        }

        return $code ?? $name ?? 'Fund Source';
    }

    protected function buildFundClusterLabel(?string $code, ?string $name): string
    {
        $code = $this->nullableTrim($code);
        $name = $this->nullableTrim($name);

        if ($code !== null && $name !== null) {
            return sprintf('%s - %s', $code, $name);
        }

        return $code ?? $name ?? 'Unassigned';
    }

    protected function buildDepartmentScopeLabel(Department $department): string
    {
        $code = $this->nullableTrim($department->code);
        $name = $this->nullableTrim($department->name) ?? 'Office';
        $shortName = $this->nullableTrim($department->short_name);

        if ($code !== null) {
            return sprintf('%s - %s', $code, $name);
        }

        if ($shortName !== null) {
            return sprintf('%s - %s', $shortName, $name);
        }

        return $name;
    }

    protected function buildDepartmentOfficeLabel(?Department $department): ?string
    {
        if (! $department) {
            return null;
        }

        return $this->nullableTrim($department->code)
            ?? $this->nullableTrim($department->short_name)
            ?? $this->nullableTrim($department->name);
    }

    protected function buildAccountableOfficerOptionLabel(AccountableOfficer $officer): string
    {
        $departmentLabel = $officer->department
            ? $this->buildDepartmentScopeLabel($officer->department)
            : null;

        $parts = array_filter([
            $this->nullableTrim($officer->full_name),
            $this->nullableTrim($officer->designation),
            $departmentLabel,
        ]);

        return implode(' | ', $parts);
    }

    protected function resolveFundClusterLabel(?FundSource $selectedFund, array $rows): string
    {
        if ($selectedFund) {
            return $this->buildFundClusterLabel(
                $selectedFund->fundCluster?->code,
                $selectedFund->fundCluster?->name,
            );
        }

        $labels = array_values(array_unique(array_filter(array_map(
            fn (array $row): string => trim((string) ($row['fund_cluster_label'] ?? '')),
            $rows
        ))));

        if (count($labels) === 1) {
            return $labels[0];
        }

        return 'All / Multiple';
    }

    protected function resolveAccountableOfficerSnapshotName(
        InventoryItem $inventoryItem,
        ?InventoryItemEvent $latestEvent = null
    ): string {
        return $this->nullableTrim($latestEvent?->officer_snapshot)
            ?? $this->nullableTrim($latestEvent?->person_accountable)
            ?? $this->nullableTrim($inventoryItem->accountable_officer)
            ?? $this->nullableTrim($inventoryItem->accountableOfficerRelation?->full_name)
            ?? '';
    }

    protected function formatSimpleLabel(?string $value): ?string
    {
        $value = $this->nullableTrim($value);
        if ($value === null) {
            return null;
        }

        return ucwords(str_replace(['_', '-'], ' ', $value));
    }

    protected function nullableTrim(mixed $value): ?string
    {
        $clean = trim((string) ($value ?? ''));

        return $clean !== '' ? $clean : null;
    }

    protected function normalizeValue(?string $value): string
    {
        return mb_strtolower(trim((string) ($value ?? '')));
    }
}
