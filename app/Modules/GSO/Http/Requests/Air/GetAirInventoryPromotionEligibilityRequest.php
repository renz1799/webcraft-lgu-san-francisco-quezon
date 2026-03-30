<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class GetAirInventoryPromotionEligibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'air.promote_inventory',
            'inventory_items.import_from_inspection',
        ]);
    }

    public function rules(): array
    {
        return [];
    }
}
