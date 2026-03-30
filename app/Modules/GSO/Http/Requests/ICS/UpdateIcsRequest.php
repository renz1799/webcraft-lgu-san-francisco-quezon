<?php

namespace App\Modules\GSO\Http\Requests\ICS;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIcsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'ics.update');
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'uuid', 'exists:departments,id'],
            'fund_source_id' => ['required', 'uuid', 'exists:fund_sources,id'],
            'issued_date' => ['required', 'date'],
            'received_from_name' => ['required', 'string', 'max:255'],
            'received_from_position' => ['required', 'string', 'max:255'],
            'received_from_office' => ['required', 'string', 'max:255'],
            'received_from_date' => ['required', 'date'],
            'received_by_name' => ['required', 'string', 'max:255'],
            'received_by_position' => ['required', 'string', 'max:255'],
            'received_by_office' => ['required', 'string', 'max:255'],
            'received_by_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'department_id' => 'Department',
            'fund_source_id' => 'Fund Source',
            'issued_date' => 'Issued Date',
            'received_from_name' => 'Received from',
            'received_from_position' => 'Received from position',
            'received_from_office' => 'Received from office',
            'received_from_date' => 'Received from date',
            'received_by_name' => 'Received by',
            'received_by_position' => 'Received by position',
            'received_by_office' => 'Received by office',
            'received_by_date' => 'Received by date',
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = static function ($value) {
            if (! is_string($value)) {
                return $value;
            }

            $value = trim($value);

            return $value === '' ? null : $value;
        };

        $this->merge([
            'department_id' => $trim($this->input('department_id')),
            'fund_source_id' => $trim($this->input('fund_source_id')),
            'issued_date' => $trim($this->input('issued_date')),
            'received_from_name' => $trim($this->input('received_from_name')),
            'received_from_position' => $trim($this->input('received_from_position')),
            'received_from_office' => $trim($this->input('received_from_office')),
            'received_from_date' => $trim($this->input('received_from_date')),
            'received_by_name' => $trim($this->input('received_by_name')),
            'received_by_position' => $trim($this->input('received_by_position')),
            'received_by_office' => $trim($this->input('received_by_office')),
            'received_by_date' => $trim($this->input('received_by_date')),
            'remarks' => $trim($this->input('remarks')),
        ]);
    }
}
