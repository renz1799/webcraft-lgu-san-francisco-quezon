<?php

namespace App\Modules\GSO\Http\Requests\RIS\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class RevertRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        return $u->hasRole('Administrator') || $u->can('revert RIS');
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