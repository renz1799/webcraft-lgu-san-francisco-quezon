<?php

namespace App\Modules\GSO\Http\Requests\ICS;

use Illuminate\Foundation\Http\FormRequest;

class IcsDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'ics.view',
            'ics.create',
            'ics.update',
            'ics.submit',
            'ics.finalize',
            'ics.reopen',
            'ics.archive',
            'ics.restore',
            'ics.manage_items',
            'ics.print',
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
            'department_id' => ['nullable', 'uuid'],
            'fund_source_id' => ['nullable', 'uuid'],
            'record_status' => ['nullable', 'string', 'max:20'],
        ];
    }
}
