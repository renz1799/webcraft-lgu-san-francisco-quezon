<?php

namespace App\Modules\GSO\Http\Requests\Reports;

use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintRpcspRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedPapers = app(PrintConfigLoaderInterface::class)
            ->allowedPapers('gso_rpcsp', 'a4-landscape');

        return [
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'accountable_officer_id' => ['nullable', 'uuid', 'exists:accountable_officers,id'],
            'as_of' => ['nullable', 'date'],
            'prefill_count' => ['nullable', 'boolean'],
            'accountable_officer_name' => ['nullable', 'string', 'max:255'],
            'accountable_officer_designation' => ['nullable', 'string', 'max:255'],
            'committee_chair_name' => ['nullable', 'string', 'max:255'],
            'committee_member_1_name' => ['nullable', 'string', 'max:255'],
            'committee_member_2_name' => ['nullable', 'string', 'max:255'],
            'approved_by_name' => ['nullable', 'string', 'max:255'],
            'approved_by_designation' => ['nullable', 'string', 'max:255'],
            'verified_by_name' => ['nullable', 'string', 'max:255'],
            'verified_by_designation' => ['nullable', 'string', 'max:255'],
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
            'as_of' => $this->nullableTrim('as_of'),
            'accountable_officer_name' => $this->nullableTrim('accountable_officer_name'),
            'accountable_officer_designation' => $this->nullableTrim('accountable_officer_designation'),
            'committee_chair_name' => $this->nullableTrim('committee_chair_name'),
            'committee_member_1_name' => $this->nullableTrim('committee_member_1_name'),
            'committee_member_2_name' => $this->nullableTrim('committee_member_2_name'),
            'approved_by_name' => $this->nullableTrim('approved_by_name'),
            'approved_by_designation' => $this->nullableTrim('approved_by_designation'),
            'verified_by_name' => $this->nullableTrim('verified_by_name'),
            'verified_by_designation' => $this->nullableTrim('verified_by_designation'),
            'prefill_count' => $this->boolean('prefill_count'),
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
