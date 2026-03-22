<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\GetAirInventoryPromotionEligibilityRequest;
use App\Modules\GSO\Http\Requests\Air\PromoteAirInventoryRequest;
use App\Modules\GSO\Services\Contracts\AirInventoryPromotionServiceInterface;
use Illuminate\Http\JsonResponse;

class AirInventoryPromotionController extends Controller
{
    public function __construct(
        private readonly AirInventoryPromotionServiceInterface $promotion,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR|modify Inventory Items')
            ->only(['eligible']);

        $this->middleware('role_or_permission:Administrator|admin|modify AIR|modify Inventory Items')
            ->only(['promote']);
    }

    public function eligible(GetAirInventoryPromotionEligibilityRequest $request, string $air): JsonResponse
    {
        return response()->json([
            'data' => $this->promotion->getEligibility($air),
            'message' => 'AIR inventory promotion eligibility loaded.',
        ]);
    }

    public function promote(PromoteAirInventoryRequest $request, string $air): JsonResponse
    {
        $user = $request->user();
        $actorUserId = (string) $user?->id;
        $actorName = trim((string) ($user?->username ?? '')) !== ''
            ? trim((string) $user?->username)
            : $actorUserId;

        $result = $this->promotion->promote(
            actorUserId: $actorUserId,
            airId: $air,
            airItemUnitIds: $request->validated('air_item_unit_ids', []),
            actorName: $actorName,
        );

        $propertyCreated = (int) ($result['property_created'] ?? 0);
        $propertySkipped = (int) ($result['property_skipped'] ?? 0);
        $consumablePosted = (int) ($result['consumable_posted'] ?? 0);
        $consumableSkipped = (int) ($result['consumable_skipped'] ?? 0);

        if ($propertyCreated === 0 && $consumablePosted === 0 && ($propertySkipped > 0 || $consumableSkipped > 0)) {
            $message = 'Nothing new to promote. This AIR was already promoted earlier.';
        } elseif ($propertyCreated === 0 && $consumablePosted === 0) {
            $message = 'Nothing to promote.';
        } else {
            $message = implode(' | ', [
                "Property created: {$propertyCreated}",
                "Property skipped: {$propertySkipped}",
                "Consumables posted: {$consumablePosted}",
                "Consumables skipped: {$consumableSkipped}",
            ]);
        }

        return response()->json([
            'data' => $result,
            'message' => $message,
        ]);
    }
}
