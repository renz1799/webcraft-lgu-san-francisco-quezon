<?php

namespace App\Modules\GSO\Services\PAR;

use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\ParItem;
use App\Modules\GSO\Repositories\Contracts\PAR\ParItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\PAR\ParRepositoryInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParItemServiceInterface;
use App\Modules\GSO\Support\ParStatuses;
use Illuminate\Support\Facades\DB;

class ParItemService implements ParItemServiceInterface
{
    public function __construct(
        private readonly ParRepositoryInterface $pars,
        private readonly ParItemRepositoryInterface $parItems,
    ) {
    }

    public function suggestItems(string $parId, string $q): array
    {
        $par = $this->pars->findOrFail($parId);
        abort_if((string) $par->status !== ParStatuses::DRAFT, 409, 'You can only add items while PAR is draft.');

        return $this->parItems->suggestFromGsoPool($parId, $q, 10);
    }

    public function addItem(string $actorUserId, string $parId, string $inventoryItemId, int $quantity = 1): ParItem
    {
        return DB::transaction(function () use ($parId, $inventoryItemId, $quantity) {
            $par = $this->pars->findOrFail($parId);
            abort_if((string) $par->status !== ParStatuses::DRAFT, 409, 'You can only add items while PAR is draft.');

            return $this->parItems->addInventoryItemToPar($parId, $inventoryItemId, $quantity);
        });
    }
}
