<?php

namespace App\Modules\GSO\Services\RIS;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Repositories\Contracts\RIS\RisItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\RIS\RisRepositoryInterface;
use App\Modules\GSO\Services\Contracts\RIS\RisServiceInterface;
use App\Modules\GSO\Services\Inventory\ItemUnitConversionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RisService implements RisServiceInterface
{
    public function __construct(
        private readonly RisRepositoryInterface $risRepo,
        private readonly RisItemRepositoryInterface $risItemsRepo,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly ItemUnitConversionService $unitConversions,
    ) {
    }

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        return $this->risRepo->datatable($filters, $page, $size);
    }

    public function generateFromAir(string $actorUserId, string $airId, array $overrides = []): Ris
    {
        return DB::transaction(function () use ($airId, $overrides) {
            $air = Air::query()->findOrFail($airId);

            abort_if((string) $air->status !== 'inspected', 409, 'AIR must be inspected before generating RIS.');

            $existing = $this->risRepo->findByAirId($airId);
            if ($existing) {
                return $existing;
            }

            $hasConsumableItems = DB::table('air_items as ai')
                ->where('ai.air_id', $airId)
                ->where('ai.qty_accepted', '>', 0)
                ->whereRaw("LOWER(TRIM(COALESCE(ai.tracking_type_snapshot, ''))) IN ('consumable', 'consumables')")
                ->exists();

            if (!$hasConsumableItems) {
                throw ValidationException::withMessages([
                    'air' => ['This AIR has no consumable items for RIS generation.'],
                ]);
            }

            $department = $this->resolveDepartment((string) ($air->requesting_department_id ?? ''));
            $fundSource = $this->resolveAirFundSource($air);
            $fundLabel = $this->formatFundSourceLabel($fundSource) ?? $this->nullableTrim((string) ($air->fund ?? ''));
            $resolvedFundSourceId = $this->nullableTrim((string) ($fundSource?->id ?? ''));

            $ris = $this->risRepo->create([
                'air_id' => (string) $air->id,
                'ris_number' => $overrides['ris_number'] ?? null,
                'ris_date' => $overrides['ris_date'] ?? ($air->date_received ?? $air->air_date ?? now()->toDateString()),
                'requesting_department_id' => $department?->id,
                'requesting_department_name_snapshot' => $overrides['requesting_department_name_snapshot']
                    ?? ($department?->name ?? $air->requesting_department_name_snapshot),
                'requesting_department_code_snapshot' => $overrides['requesting_department_code_snapshot']
                    ?? ($department?->code ?? $air->requesting_department_code_snapshot),
                'fund_source_id' => $overrides['fund_source_id'] ?? $resolvedFundSourceId,
                'fund' => $overrides['fund'] ?? $fundLabel,
                'fpp_code' => $overrides['fpp_code'] ?? null,
                'division' => $overrides['division'] ?? null,
                'responsibility_center_code' => $overrides['responsibility_center_code']
                    ?? ($department?->code ?? $air->requesting_department_code_snapshot),
                'status' => $overrides['status'] ?? 'draft',
                'purpose' => $overrides['purpose'] ?? null,
                'remarks' => $overrides['remarks'] ?? null,
                'requested_by_name' => $overrides['requested_by_name'] ?? null,
                'requested_by_designation' => $overrides['requested_by_designation'] ?? null,
                'requested_by_date' => $overrides['requested_by_date'] ?? null,
                'approved_by_name' => $overrides['approved_by_name'] ?? null,
                'approved_by_designation' => $overrides['approved_by_designation'] ?? null,
                'approved_by_date' => $overrides['approved_by_date'] ?? null,
                'issued_by_name' => $overrides['issued_by_name'] ?? null,
                'issued_by_designation' => $overrides['issued_by_designation'] ?? null,
                'issued_by_date' => $overrides['issued_by_date'] ?? null,
                'received_by_name' => $overrides['received_by_name'] ?? null,
                'received_by_designation' => $overrides['received_by_designation'] ?? null,
                'received_by_date' => $overrides['received_by_date'] ?? null,
            ]);

            $stockQuery = DB::table('stocks')
                ->select('item_id', DB::raw('SUM(on_hand) as on_hand'))
                ->whereNull('deleted_at')
                ->when($resolvedFundSourceId, function ($query, $fundSourceId) {
                    $query->where('fund_source_id', $fundSourceId);
                })
                ->groupBy('item_id');

            $rows = DB::table('air_items as ai')
                ->join('items as it', 'it.id', '=', 'ai.item_id')
                ->leftJoinSub($stockQuery, 'stock_totals', function ($join) {
                    $join->on('stock_totals.item_id', '=', 'it.id');
                })
                ->where('ai.air_id', $airId)
                ->where('ai.qty_accepted', '>', 0)
                ->whereRaw("LOWER(TRIM(COALESCE(ai.tracking_type_snapshot, ''))) IN ('consumable', 'consumables')")
                ->whereNull('it.deleted_at')
                ->select([
                    'ai.item_id',
                    'ai.unit_snapshot as air_unit_snapshot',
                    'ai.qty_accepted',
                    'it.item_name',
                    'it.item_identification',
                    'it.base_unit',
                    DB::raw('COALESCE(stock_totals.on_hand, 0) as on_hand'),
                ])
                ->orderBy('it.item_name')
                ->get();

            $itemsPayload = [];
            $lineNumber = 1;

            foreach ($rows as $row) {
                $qtyAccepted = (int) ($row->qty_accepted ?? 0);
                $onHand = (int) ($row->on_hand ?? 0);

                if ($qtyAccepted <= 0 || $onHand <= 0) {
                    continue;
                }

                $itemId = (string) $row->item_id;
                $fromUnit = $this->nullableTrim((string) ($row->air_unit_snapshot ?? ''));
                $conversion = $this->unitConversions->toBaseQty($itemId, $qtyAccepted, $fromUnit);
                $baseQty = (int) ($conversion['baseQty'] ?? $qtyAccepted);

                if ($baseQty <= 0) {
                    continue;
                }

                $finalQty = min($baseQty, $onHand);
                if ($finalQty <= 0) {
                    continue;
                }

                $itemsPayload[] = [
                    'line_no' => $lineNumber++,
                    'item_id' => $itemId,
                    'stock_no_snapshot' => $this->nullableTrim((string) ($row->item_identification ?? '')),
                    'description_snapshot' => $this->nullableTrim((string) ($row->item_name ?? '')),
                    'unit_snapshot' => $this->nullableTrim((string) ($row->base_unit ?? '')),
                    'qty_requested' => $finalQty,
                    'qty_issued' => 0,
                    'remarks' => null,
                ];
            }

            if ($itemsPayload !== []) {
                $this->risItemsRepo->createMany((string) $ris->id, $itemsPayload);
            }

            $this->auditLogs->record(
                action: 'ris.created_from_air',
                subject: $ris,
                changesOld: [],
                changesNew: $ris->toArray(),
                meta: [
                    'air_id' => (string) $air->id,
                    'items_count' => count($itemsPayload),
                    'requesting_department_id' => (string) ($department?->id ?? ''),
                    'fund_source_id' => $resolvedFundSourceId,
                ],
                message: 'RIS draft generated from AIR' . ($air->po_number ? ' for PO ' . $air->po_number : ''),
                display: $this->buildRisCreatedFromAirDisplay($air, $ris->toArray(), $itemsPayload),
            );

            return $ris->refresh();
        });
    }

    public function getEditData(string $risId): array
    {
        $ris = Ris::query()
            ->with(['air', 'fundSource', 'items.item'])
            ->findOrFail($risId);

        return [
            'ris' => $ris,
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'fundSources' => FundSource::query()
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name']),
            'items' => $ris->items->map(fn ($item) => [
                'id' => (string) $item->id,
                'item_id' => (string) $item->item_id,
                'item_name' => (string) ($item->item?->item_name ?? $item->description_snapshot ?? ''),
                'stock_no' => (string) ($item->stock_no_snapshot ?? ''),
                'description' => (string) ($item->description_snapshot ?? ''),
                'unit' => (string) ($item->unit_snapshot ?? ''),
                'qty_requested' => (int) ($item->qty_requested ?? 0),
                'qty_issued' => (int) ($item->qty_issued ?? 0),
                'remarks' => (string) ($item->remarks ?? ''),
            ]),
        ];
    }

    public function updateRis(string $actorUserId, string $risId, array $data): Ris
    {
        return DB::transaction(function () use ($risId, $data) {
            $ris = $this->risRepo->findById($risId);
            abort_if(!$ris, 404, 'RIS not found.');

            $allowed = [
                'ris_number',
                'ris_date',
                'requesting_department_id',
                'fund_source_id',
                'fpp_code',
                'division',
                'responsibility_center_code',
                'purpose',
                'remarks',
                'requested_by_name',
                'requested_by_designation',
                'requested_by_date',
                'approved_by_name',
                'approved_by_designation',
                'approved_by_date',
                'issued_by_name',
                'issued_by_designation',
                'issued_by_date',
                'received_by_name',
                'received_by_designation',
                'received_by_date',
            ];

            $payload = array_intersect_key($data, array_flip($allowed));
            $payload = $this->normalizeRisHeaderPayload($payload);

            $this->validateRequiredForEdit($payload);

            $fundSourceId = $this->nullableTrim((string) ($payload['fund_source_id'] ?? ''));
            $fundSource = FundSource::query()
                ->where('id', $fundSourceId)
                ->whereNull('deleted_at')
                ->first(['id', 'code', 'name', 'is_active']);

            if (!$fundSource || !(bool) $fundSource->is_active) {
                throw ValidationException::withMessages([
                    'fund_source_id' => ['The selected fund source is invalid or inactive.'],
                ]);
            }

            $departmentId = $this->nullableTrim((string) ($payload['requesting_department_id'] ?? ''));
            $department = Department::query()
                ->where('id', $departmentId)
                ->whereNull('deleted_at')
                ->first(['id', 'name', 'code']);

            if (!$department) {
                throw ValidationException::withMessages([
                    'requesting_department_id' => ['The selected requesting department is invalid.'],
                ]);
            }

            $payload['fund_source_id'] = (string) $fundSource->id;
            $payload['fund'] = $this->formatFundSourceLabel($fundSource);
            $payload['requesting_department_id'] = (string) $department->id;
            $payload['requesting_department_name_snapshot'] = $department->name;
            $payload['requesting_department_code_snapshot'] = $department->code;

            if ($this->nullableTrim((string) ($payload['responsibility_center_code'] ?? '')) === null) {
                $payload['responsibility_center_code'] = $department->code;
            }

            return $this->risRepo->update($ris, $payload)->refresh();
        });
    }

    public function deleteRis(string $actorUserId, string $risId): void
    {
        DB::transaction(function () use ($risId) {
            $ris = Ris::query()->findOrFail($risId);
            $old = $ris->toArray();

            $this->risRepo->delete($ris);

            $this->auditLogs->record(
                action: 'ris.deleted',
                subject: $ris,
                changesOld: $old,
                changesNew: ['deleted_at' => $ris->deleted_at?->toDateTimeString()],
                meta: [],
                message: 'RIS archived: ' . $this->risLabel($old),
                display: $this->buildRisDeletedDisplay($old),
            );
        });
    }

    public function restoreRis(string $actorUserId, string $risId): void
    {
        DB::transaction(function () use ($risId) {
            $ris = Ris::withTrashed()->findOrFail($risId);
            abort_if(!$ris->trashed(), 409, 'RIS is not archived.');

            $deletedAt = $ris->deleted_at?->toDateTimeString();

            $this->risRepo->restore($ris);

            $this->auditLogs->record(
                action: 'ris.restored',
                subject: $ris,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: [],
                message: 'RIS restored: ' . $this->risLabel($ris->refresh()->toArray()),
                display: $this->buildRisRestoredDisplay($ris->toArray()),
            );
        });
    }

    public function createDraft(string $actorUserId): Ris
    {
        return DB::transaction(function () {
            $fundSource = $this->resolveDefaultFundSource();

            return $this->risRepo->create([
                'ris_number' => null,
                'ris_date' => now()->toDateString(),
                'status' => 'draft',
                'fund_source_id' => $fundSource?->id,
                'fund' => $this->formatFundSourceLabel($fundSource),
                'purpose' => null,
            ]);
        });
    }

    private function resolveDepartment(string $departmentId): ?Department
    {
        $departmentId = $this->nullableTrim($departmentId);

        if ($departmentId === null) {
            return null;
        }

        return Department::query()
            ->where('id', $departmentId)
            ->whereNull('deleted_at')
            ->first(['id', 'name', 'code']);
    }

    private function resolveAirFundSource(Air $air): ?FundSource
    {
        $fundSourceId = $this->nullableTrim((string) ($air->fund_source_id ?? ''));

        if ($fundSourceId !== null) {
            $fundSource = FundSource::query()
                ->where('id', $fundSourceId)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->first(['id', 'code', 'name', 'is_active']);

            if ($fundSource) {
                return $fundSource;
            }
        }

        return $this->resolveDefaultFundSource();
    }

    private function resolveDefaultFundSource(): ?FundSource
    {
        return FundSource::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN LOWER(COALESCE(name, '')) LIKE '%general%' THEN 0 ELSE 1 END")
            ->orderBy('code')
            ->first(['id', 'code', 'name', 'is_active']);
    }

    private function formatFundSourceLabel(?FundSource $fundSource): ?string
    {
        if (!$fundSource) {
            return null;
        }

        $label = trim(
            ((string) ($fundSource->code ?? '')) .
            (((string) ($fundSource->code ?? '')) !== '' ? ' - ' : '') .
            ((string) ($fundSource->name ?? ''))
        );

        return $label !== '' ? $label : null;
    }

    private function normalizeRisHeaderPayload(array $payload): array
    {
        $trimFields = [
            'ris_number',
            'ris_date',
            'requesting_department_id',
            'fund_source_id',
            'fpp_code',
            'division',
            'responsibility_center_code',
            'purpose',
            'remarks',
            'requested_by_name',
            'requested_by_designation',
            'requested_by_date',
            'approved_by_name',
            'approved_by_designation',
            'approved_by_date',
            'issued_by_name',
            'issued_by_designation',
            'issued_by_date',
            'received_by_name',
            'received_by_designation',
            'received_by_date',
        ];

        foreach ($trimFields as $field) {
            if (!array_key_exists($field, $payload) || !is_string($payload[$field])) {
                continue;
            }

            $payload[$field] = $this->nullableTrim($payload[$field]);
        }

        return $payload;
    }

    private function validateRequiredForEdit(array $payload): void
    {
        $required = [
            'ris_date' => 'RIS Date',
            'fund_source_id' => 'Fund Source',
            'requesting_department_id' => 'Requesting Department',
            'purpose' => 'Purpose',
            'requested_by_name' => 'Requested By Name',
            'requested_by_designation' => 'Requested By Designation',
            'requested_by_date' => 'Requested By Date',
            'approved_by_name' => 'Approved By Name',
            'approved_by_designation' => 'Approved By Designation',
            'approved_by_date' => 'Approved By Date',
            'issued_by_name' => 'Issued By Name',
            'issued_by_designation' => 'Issued By Designation',
            'issued_by_date' => 'Issued By Date',
            'received_by_name' => 'Received By Name',
            'received_by_designation' => 'Received By Designation',
            'received_by_date' => 'Received By Date',
        ];

        $errors = [];

        foreach ($required as $field => $label) {
            $value = $payload[$field] ?? null;

            if ($value === null || (is_string($value) && trim($value) === '')) {
                $errors[$field] = ["{$label} is required."];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function buildRisCreatedFromAirDisplay(Air $air, array $after, array $itemsPayload): array
    {
        $poNumber = $this->displayValue($air->po_number ?? null);
        $sections = [
            [
                'title' => 'Source AIR',
                'items' => [
                    ['label' => 'PO Number', 'value' => $poNumber],
                    ['label' => 'AIR Number', 'value' => $this->displayValue($air->air_number ?? null)],
                    ['label' => 'Supplier', 'value' => $this->displayValue($air->supplier_name ?? null)],
                    ['label' => 'Requesting Department', 'value' => $this->displayValue($air->requesting_department_name_snapshot ?? null)],
                    ['label' => 'Items Included', 'value' => (string) count($itemsPayload)],
                ],
            ],
            $this->buildRisContextSection($after),
        ];

        if ($itemsPayload !== []) {
            $sections[] = $this->buildGeneratedItemsSection($itemsPayload);
        }

        return [
            'summary' => 'RIS draft generated from AIR for PO ' . $poNumber,
            'subject_label' => $this->risLabel($after),
            'sections' => $sections,
        ];
    }

    private function buildRisDeletedDisplay(array $values): array
    {
        return [
            'summary' => 'RIS archived: ' . $this->risLabel($values),
            'subject_label' => $this->risLabel($values),
            'sections' => [[
                'title' => 'RIS Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => 'Active Record', 'after' => 'Archived'],
                    ['label' => 'Status', 'value' => $this->statusLabel($values['status'] ?? null)],
                    ['label' => 'Requesting Department', 'value' => $this->resolveDepartmentLabel($values)],
                    ['label' => 'Fund Source', 'value' => $this->resolveFundSourceLabel($values)],
                ],
            ]],
        ];
    }

    private function buildRisRestoredDisplay(array $values): array
    {
        return [
            'summary' => 'RIS restored: ' . $this->risLabel($values),
            'subject_label' => $this->risLabel($values),
            'sections' => [[
                'title' => 'RIS Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => 'Archived', 'after' => 'Active Record'],
                    ['label' => 'Status', 'value' => $this->statusLabel($values['status'] ?? null)],
                    ['label' => 'Requesting Department', 'value' => $this->resolveDepartmentLabel($values)],
                    ['label' => 'Fund Source', 'value' => $this->resolveFundSourceLabel($values)],
                ],
            ]],
        ];
    }

    private function buildRisContextSection(array $values): array
    {
        return [
            'title' => 'RIS Details',
            'items' => [
                ['label' => 'RIS Number', 'value' => $this->displayValue($values['ris_number'] ?? null)],
                ['label' => 'Status', 'value' => $this->statusLabel($values['status'] ?? null)],
                ['label' => 'RIS Date', 'value' => $this->displayValue($values['ris_date'] ?? null)],
                ['label' => 'Requesting Department', 'value' => $this->resolveDepartmentLabel($values)],
                ['label' => 'Fund Source', 'value' => $this->resolveFundSourceLabel($values)],
                ['label' => 'Purpose', 'value' => $this->displayValue($values['purpose'] ?? null)],
            ],
        ];
    }

    private function buildGeneratedItemsSection(array $itemsPayload): array
    {
        return [
            'title' => 'Included Items',
            'items' => collect($itemsPayload)
                ->take(10)
                ->map(function (array $row): array {
                    $parts = ['Qty: ' . max(1, (int) ($row['qty_requested'] ?? 1))];

                    $unit = $this->nullableTrim((string) ($row['unit_snapshot'] ?? ''));
                    if ($unit !== null) {
                        $parts[] = 'Unit: ' . $unit;
                    }

                    return [
                        'label' => $this->displayValue($row['stock_no_snapshot'] ?? null)
                            . ' - '
                            . $this->displayValue($row['description_snapshot'] ?? null),
                        'value' => implode(' | ', $parts),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function risLabel(array $values): string
    {
        $number = $this->nullableTrim((string) ($values['ris_number'] ?? ''));
        if ($number !== null) {
            return $number;
        }

        $status = $this->statusLabel($values['status'] ?? null);
        if ($status !== 'None') {
            return sprintf('RIS (%s)', $status);
        }

        return 'RIS';
    }

    private function resolveDepartmentLabel(array $values): string
    {
        $snapshotCode = $this->nullableTrim((string) ($values['requesting_department_code_snapshot'] ?? ''));
        $snapshotName = $this->nullableTrim((string) ($values['requesting_department_name_snapshot'] ?? ''));

        if ($snapshotCode !== null && $snapshotName !== null) {
            return sprintf('%s (%s)', $snapshotCode, $snapshotName);
        }

        if ($snapshotCode !== null || $snapshotName !== null) {
            return $snapshotCode ?? $snapshotName ?? 'None';
        }

        $departmentId = $this->nullableTrim((string) ($values['requesting_department_id'] ?? ''));
        if ($departmentId === null) {
            return 'None';
        }

        $department = Department::query()->find($departmentId);
        if (!$department) {
            return 'Unknown Department';
        }

        $code = $this->nullableTrim((string) ($department->code ?? ''));
        $name = $this->nullableTrim((string) ($department->name ?? ''));

        if ($code !== null && $name !== null) {
            return sprintf('%s (%s)', $code, $name);
        }

        return $code ?? $name ?? 'Department';
    }

    private function resolveFundSourceLabel(array $values): string
    {
        $fund = $this->nullableTrim((string) ($values['fund'] ?? ''));
        if ($fund !== null) {
            return $fund;
        }

        $fundSourceId = $this->nullableTrim((string) ($values['fund_source_id'] ?? ''));
        if ($fundSourceId === null) {
            return 'None';
        }

        $fundSource = FundSource::query()->find($fundSourceId);
        if (!$fundSource) {
            return 'Unknown Fund Source';
        }

        return $this->formatFundSourceLabel($fundSource) ?? 'Fund Source';
    }

    private function statusLabel(?string $value): string
    {
        $value = $this->nullableTrim((string) ($value ?? ''));

        return $value !== null ? ucfirst(str_replace('_', ' ', $value)) : 'None';
    }

    private function displayValue(mixed $value): string
    {
        $value = $this->nullableTrim((string) ($value ?? ''));

        return $value ?? 'None';
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
