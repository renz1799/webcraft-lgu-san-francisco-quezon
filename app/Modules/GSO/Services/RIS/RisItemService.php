<?php

namespace App\Modules\GSO\Services\RIS;

use App\Modules\GSO\Data\Contracts\RIS\RisItemDataProviderInterface;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Models\RisItem;
use App\Modules\GSO\Repositories\Contracts\RIS\RisItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\RIS\RisRepositoryInterface;
use App\Modules\GSO\Services\Contracts\RIS\RisItemServiceInterface;
use Illuminate\Support\Facades\DB;

class RisItemService implements RisItemServiceInterface
{
    public function __construct(
        private readonly RisRepositoryInterface $risRepo,
        private readonly RisItemRepositoryInterface $risItemRepo,
        private readonly RisItemDataProviderInterface $risItemDataProvider,
    ) {
    }

    public function suggestConsumables(string $actorUserId, string $risId, string $query = ''): array
    {
        $ris = $this->findEditableDraftRis($risId);

        $risFundSourceId = trim((string) ($ris->fund_source_id ?? ''));
        abort_if($risFundSourceId === '', 422, 'Please select a Fund Source first.');

        $risFundSource = $this->risItemDataProvider->getFundSourceContext($risFundSourceId);
        abort_if(!$risFundSource, 422, 'The selected Fund Source is invalid.');

        $excludeItemIds = $this->risItemRepo->listActiveItemIdsByRisId($risId);

        $rows = $this->risItemDataProvider->getConsumableSuggestionRows(
            $risFundSourceId,
            $query,
            $excludeItemIds,
        );

        $risFundLabel = trim(
            ((string) ($risFundSource['code'] ?? '')) .
            (((string) ($risFundSource['code'] ?? '')) !== '' ? ' - ' : '') .
            ((string) ($risFundSource['name'] ?? ''))
        );

        $risFundLabel = $risFundLabel !== '' ? $risFundLabel : 'Selected Fund Source';

        return array_map(function (array $row) use ($risFundSourceId, $risFundLabel): array {
            $fundLabel = trim(
                ((string) ($row['fund_code'] ?? '')) .
                (((string) ($row['fund_code'] ?? '')) !== '' ? ' - ' : '') .
                ((string) ($row['fund_name'] ?? ''))
            );

            $fundSourceId = (string) ($row['fund_source_id'] ?? '');
            $isAllowed = $fundSourceId !== '' && $fundSourceId === $risFundSourceId;

            return [
                'source' => 'stock',
                'item_id' => (string) ($row['item_id'] ?? ''),
                'item_name' => (string) ($row['item_name'] ?? ''),
                'stock_no' => (string) ($row['item_identification'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'base_unit' => trim((string) ($row['base_unit'] ?? '')),
                'qty_default_base' => 1,
                'multiplier' => 1,
                'on_hand_base' => (int) ($row['on_hand'] ?? 0),
                'fund_source_id' => $fundSourceId,
                'fund_label' => $fundLabel,
                'is_allowed' => $isAllowed,
                'disabled_reason' => $isAllowed
                    ? null
                    : "Not allowed for this RIS. RIS Fund Source is: {$risFundLabel}",
            ];
        }, $rows);
    }

    public function listForEdit(string $risId): array
    {
        $ris = $this->risRepo->findById($risId);
        abort_if(!$ris, 404);

        $fundSourceId = trim((string) ($ris->fund_source_id ?? ''));
        abort_if($fundSourceId === '', 422, 'RIS has no fund source selected.');

        $rows = $this->risItemDataProvider->getEditRows($risId, $fundSourceId);

        return array_map(function (array $row): array {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'item_id' => (string) ($row['item_id'] ?? ''),
                'item_name' => (string) ($row['item_name'] ?? ''),
                'stock_no' => (string) ($row['stock_no_snapshot'] ?? ''),
                'description' => (string) ($row['description_snapshot'] ?? ''),
                'unit' => (string) ($row['unit_snapshot'] ?? ''),
                'base_unit' => (string) ($row['base_unit'] ?? ''),
                'qty_requested' => (int) ($row['qty_requested'] ?? 0),
                'qty_issued' => isset($row['qty_issued']) ? (int) $row['qty_issued'] : 0,
                'on_hand_base' => (int) ($row['on_hand_base'] ?? 0),
                'remarks' => '',
            ];
        }, $rows);
    }

    public function addItem(
        string $actorUserId,
        string $risId,
        string $itemId,
        ?int $qtyRequested = null,
        ?string $remarks = null,
        ?string $fundSourceId = null,
    ): RisItem {
        return DB::transaction(function () use ($risId, $itemId, $qtyRequested, $remarks, $fundSourceId) {
            $ris = $this->findEditableDraftRis($risId);

            $risFundId = trim((string) ($ris->fund_source_id ?? ''));
            abort_if($risFundId === '', 422, 'This RIS has no Fund Source selected.');

            $requestedFundId = trim((string) ($fundSourceId ?? ''));
            if ($requestedFundId !== '' && $requestedFundId !== $risFundId) {
                abort(422, 'Fund source mismatch. Please select items under the same Fund Source as this RIS.');
            }

            $qty = max(1, (int) ($qtyRequested ?? 1));

            $exists = $this->risItemRepo->existsActiveByRisIdAndItemId($risId, $itemId);
            abort_if($exists, 409, 'Item already added.');

            $itemSnapshot = $this->risItemDataProvider->getItemSnapshot($itemId);
            abort_if(!$itemSnapshot, 404, 'Item not found.');

            $stockOnHand = $this->risItemDataProvider->getOnHandForItemAndFundSource($itemId, $risFundId);

            abort_if($stockOnHand <= 0, 409, 'Item has zero stock under this Fund Source.');
            abort_if($qty > $stockOnHand, 409, 'Requested quantity exceeds stock on hand under this Fund Source.');

            $nextLine = $this->risItemRepo->nextLineNumber($risId);

            $created = $this->risItemRepo->create([
                'ris_id' => $risId,
                'line_no' => $nextLine,
                'item_id' => $itemId,
                'stock_no_snapshot' => (($itemSnapshot['item_identification'] ?? '') !== '')
                    ? $itemSnapshot['item_identification']
                    : null,
                'description_snapshot' => (string) ($itemSnapshot['item_name'] ?? ''),
                'unit_snapshot' => (($itemSnapshot['base_unit'] ?? '') !== '')
                    ? $itemSnapshot['base_unit']
                    : null,
                'qty_requested' => $qty,
                'qty_issued' => 0,
                'remarks' => $remarks ? trim((string) $remarks) : null,
            ]);

            return $created->refresh();
        });
    }

    public function updateItem(string $actorUserId, string $risId, string $risItemId, array $data): RisItem
    {
        return DB::transaction(function () use ($risId, $risItemId, $data) {
            $ris = $this->findEditableDraftRis($risId);

            $item = $this->risItemRepo->findById($risItemId);
            abort_if(!$item, 404);
            abort_unless((string) $item->ris_id === (string) $risId, 404);

            $clean = [];

            if (isset($data['qty_requested'])) {
                $qty = max(1, (int) $data['qty_requested']);
                $stockOnHand = $this->resolveStockOnHandForRisItem($item, $ris);

                abort_if($stockOnHand <= 0, 409, 'Item has zero stock.');
                abort_if($qty > $stockOnHand, 409, 'Requested quantity exceeds available stock.');

                $clean['qty_requested'] = $qty;
            }

            if ($clean === []) {
                return $item;
            }

            return $this->risItemRepo->update($item, $clean);
        });
    }

    public function bulkUpdate(string $actorUserId, string $risId, array $items): array
    {
        return DB::transaction(function () use ($risId, $items) {
            $ris = $this->findEditableDraftRis($risId);
            $updated = 0;

            foreach ($items as $row) {
                $id = (string) ($row['id'] ?? '');
                if ($id === '' || !isset($row['qty_requested'])) {
                    continue;
                }

                $item = $this->risItemRepo->findById($id);
                if (!$item || (string) $item->ris_id !== (string) $risId) {
                    continue;
                }

                $qty = max(1, (int) $row['qty_requested']);
                $stockOnHand = $this->resolveStockOnHandForRisItem($item, $ris);

                abort_if($stockOnHand <= 0, 409, 'Item has zero stock.');
                abort_if($qty > $stockOnHand, 409, 'Requested quantity exceeds available stock.');

                $this->risItemRepo->update($item, [
                    'qty_requested' => $qty,
                ]);

                $updated++;
            }

            return ['updated' => $updated];
        });
    }

    public function removeItem(string $actorUserId, string $risId, string $risItemId): void
    {
        DB::transaction(function () use ($risId, $risItemId) {
            $this->findEditableDraftRis($risId);

            $item = $this->risItemRepo->findById($risItemId);
            abort_if(!$item, 404);
            abort_unless((string) $item->ris_id === (string) $risId, 404);

            $this->risItemRepo->forceDelete($item);
        });
    }

    private function findEditableDraftRis(string $risId): Ris
    {
        $ris = $this->risRepo->findById($risId);

        abort_if(!$ris, 404);
        abort_if((string) ($ris->status ?? '') !== 'draft', 409, 'RIS is no longer editable.');

        return $ris;
    }

    private function resolveStockOnHandForRisItem(RisItem $item, Ris $ris): int
    {
        $fundSourceId = trim((string) ($ris->fund_source_id ?? ''));
        abort_if($fundSourceId === '', 422, 'This RIS has no Fund Source selected.');

        return $this->risItemDataProvider->getOnHandForItemAndFundSource(
            (string) $item->item_id,
            $fundSourceId,
        );
    }
}