<?php

namespace App\Modules\GSO\Http\Requests\RIS\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class RevertRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($u, 'ris.revert');
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $ris = $this->route('ris');
            if (!$ris) return;

            if ((string)($ris->status ?? '') !== 'issued') {
                $v->errors()->add('status', 'Only issued RIS can be reverted to draft.');
            }
        });
    }
}
