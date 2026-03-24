<?php

namespace App\Modules\GSO\Http\Requests\RIS\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class ReopenRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        return $u->hasRole('Administrator') || $u->can('reopen RIS') || $u->can('revert RIS');
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

            if (!in_array((string) ($ris->status ?? ''), ['submitted', 'rejected'], true)) {
                $v->errors()->add('status', 'Only submitted or rejected RIS can be reopened to draft.');
            }
        });
    }
}