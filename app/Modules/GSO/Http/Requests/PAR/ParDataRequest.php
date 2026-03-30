<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;

class ParDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'par.view',
            'par.create',
            'par.update',
            'par.submit',
            'par.finalize',
            'par.reopen',
            'par.archive',
            'par.restore',
            'par.manage_items',
            'par.print',
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

            // active | archived | all
            'record_status' => ['nullable', 'string', 'max:20'],
        ];
    }
}
