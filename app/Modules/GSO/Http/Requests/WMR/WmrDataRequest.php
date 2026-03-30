<?php

namespace App\Modules\GSO\Http\Requests\WMR;

use Illuminate\Foundation\Http\FormRequest;

class WmrDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'wmr.view',
            'wmr.create',
            'wmr.update',
            'wmr.submit',
            'wmr.approve',
            'wmr.finalize',
            'wmr.reopen',
            'wmr.archive',
            'wmr.restore',
            'wmr.manage_items',
            'wmr.print',
        ]);
    }
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'fund_cluster_id' => ['nullable', 'uuid'],
            'record_status' => ['nullable', 'string', 'max:20'],
        ];
    }
}

