<?php

namespace App\Http\Requests\AuditLogs;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogPrintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'module_name' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:150'],
            'actor_id' => ['nullable', 'integer'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'paper_profile' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'module_name' => $this->string('module_name')->trim()->value() ?: null,
            'action' => $this->string('action')->trim()->value() ?: null,
            'subject_type' => $this->string('subject_type')->trim()->value() ?: null,
            'search' => $this->string('search')->trim()->value() ?: null,
            'paper_profile' => $this->string('paper_profile')->trim()->value() ?: null,
        ]);
    }
}