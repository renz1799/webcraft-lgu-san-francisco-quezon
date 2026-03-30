<?php

namespace App\Modules\GSO\Http\Requests\PTR;

use Illuminate\Foundation\Http\FormRequest;

class PtrDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'ptr.view',
            'ptr.create',
            'ptr.update',
            'ptr.submit',
            'ptr.finalize',
            'ptr.reopen',
            'ptr.archive',
            'ptr.restore',
            'ptr.manage_items',
            'ptr.print',
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
            'from_department_id' => ['nullable', 'uuid'],
            'to_department_id' => ['nullable', 'uuid'],
            'from_fund_source_id' => ['nullable', 'uuid'],
            'to_fund_source_id' => ['nullable', 'uuid'],
            'record_status' => ['nullable', 'string', 'max:20'],
        ];
    }
}
