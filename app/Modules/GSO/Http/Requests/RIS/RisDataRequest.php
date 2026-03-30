<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;

class RisDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'ris.view',
            'ris.create',
            'ris.update',
            'ris.submit',
            'ris.approve',
            'ris.reject',
            'ris.reopen',
            'ris.revert',
            'ris.archive',
            'ris.restore',
            'ris.manage_items',
            'ris.generate_from_air',
            'ris.print',
        ]);
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'string', 'max:30'], // workflow status (draft/submitted/...)
            'record_status' => ['nullable', 'string', 'max:30'], // active/archived/all

            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],

            'fund' => ['nullable', 'string', 'max:200'],
        ];
    }
}
