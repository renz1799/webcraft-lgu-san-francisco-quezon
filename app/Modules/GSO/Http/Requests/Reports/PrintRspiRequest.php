<?php

namespace App\Modules\GSO\Http\Requests\Reports;

use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintRspiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'reports.rspi.view',
            'inventory_items.view',
            'inventory_items.update',
        ]);
    }

    public function rules(): array
    {
        $allowedPapers = app(PrintConfigLoaderInterface::class)
            ->allowedPapers('gso_rspi', 'a4-landscape');

        return [
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'accountable_officer_id' => ['nullable', 'uuid', 'exists:accountable_officers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'prepared_by_name' => ['nullable', 'string', 'max:255'],
            'prepared_by_designation' => ['nullable', 'string', 'max:255'],
            'reviewed_by_name' => ['nullable', 'string', 'max:255'],
            'reviewed_by_designation' => ['nullable', 'string', 'max:255'],
            'approved_by_name' => ['nullable', 'string', 'max:255'],
            'approved_by_designation' => ['nullable', 'string', 'max:255'],
            'paper_profile' => ['nullable', 'string', 'max:100', Rule::in($allowedPapers)],
            'rows_per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'grid_rows' => ['nullable', 'integer', 'min:1', 'max:200'],
            'last_page_grid_rows' => ['nullable', 'integer', 'min:0', 'max:200'],
            'description_chars_per_line' => ['nullable', 'integer', 'min:10', 'max:300'],
            'preview' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizeInteger = function (string $key): void {
            if (! $this->has($key)) {
                return;
            }

            $value = $this->input($key);

            if ($value === '' || $value === null) {
                $this->merge([$key => null]);

                return;
            }

            if (is_numeric($value)) {
                $this->merge([$key => (int) $value]);
            }
        };

        $this->merge([
            'fund_source_id' => $this->nullableTrim('fund_source_id'),
            'department_id' => $this->nullableTrim('department_id'),
            'accountable_officer_id' => $this->nullableTrim('accountable_officer_id'),
            'date_from' => $this->nullableTrim('date_from'),
            'date_to' => $this->nullableTrim('date_to'),
            'prepared_by_name' => $this->nullableTrim('prepared_by_name'),
            'prepared_by_designation' => $this->nullableTrim('prepared_by_designation'),
            'reviewed_by_name' => $this->nullableTrim('reviewed_by_name'),
            'reviewed_by_designation' => $this->nullableTrim('reviewed_by_designation'),
            'approved_by_name' => $this->nullableTrim('approved_by_name'),
            'approved_by_designation' => $this->nullableTrim('approved_by_designation'),
            'paper_profile' => $this->nullableTrim('paper_profile'),
            'preview' => $this->has('preview') ? $this->boolean('preview') : null,
        ]);

        $normalizeInteger('rows_per_page');
        $normalizeInteger('grid_rows');
        $normalizeInteger('last_page_grid_rows');
        $normalizeInteger('description_chars_per_line');
    }

    /**
     * @return array<string, int>
     */
    public function paperOverrides(): array
    {
        return array_filter([
            'rows_per_page' => $this->validated('rows_per_page'),
            'grid_rows' => $this->validated('grid_rows'),
            'last_page_grid_rows' => $this->validated('last_page_grid_rows'),
            'description_chars_per_line' => $this->validated('description_chars_per_line'),
        ], static fn (mixed $value): bool => is_int($value));
    }

    private function nullableTrim(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $clean = trim($value);

        return $clean !== '' ? $clean : null;
    }
}
