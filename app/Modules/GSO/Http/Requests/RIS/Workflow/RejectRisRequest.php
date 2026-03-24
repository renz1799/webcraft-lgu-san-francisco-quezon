<?php

namespace App\Modules\GSO\Http\Requests\RIS\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class RejectRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        return $u->hasRole('Administrator') || $u->can('reject RIS');
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $ris = $this->route('ris');
            if (!$ris) return;

            if ((string)($ris->status ?? '') !== 'submitted') {
                $v->errors()->add('status', 'Only submitted RIS can be rejected.');
            }
        });
    }
}