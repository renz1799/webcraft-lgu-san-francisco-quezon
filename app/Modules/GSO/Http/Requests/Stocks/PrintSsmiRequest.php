<?php

namespace App\Modules\GSO\Http\Requests\Stocks;

use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintSsmiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'prepared_by_name' => ['nullable', 'string', 'max:255'],
            'prepared_by_designation' => ['nullable', 'string', 'max:255'],
            'prepared_by_date' => ['nullable', 'date'],
            'certified_by_name' => ['nullable', 'string', 'max:255'],
            'certified_by_designation' => ['nullable', 'string', 'max:255'],
            'certified_by_date' => ['nullable', 'date'],
            'paper_profile' => ['nullable', 'string', Rule::in($this->allowedPapers())],
            'rows_per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'grid_rows' => ['nullable', 'integer', 'min:1', 'max:200'],
            'last_page_grid_rows' => ['nullable', 'integer', 'min:0', 'max:200'],
            'description_chars_per_line' => ['nullable', 'integer', 'min:10', 'max:300'],
            'preview' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fund_source_id' => $this->nullableTrim('fund_source_id'),
            'date_from' => $this->nullableTrim('date_from'),
            'date_to' => $this->nullableTrim('date_to'),
            'prepared_by_name' => $this->nullableTrim('prepared_by_name'),
            'prepared_by_designation' => $this->nullableTrim('prepared_by_designation'),
            'prepared_by_date' => $this->nullableTrim('prepared_by_date'),
            'certified_by_name' => $this->nullableTrim('certified_by_name'),
            'certified_by_designation' => $this->nullableTrim('certified_by_designation'),
            'certified_by_date' => $this->nullableTrim('certified_by_date'),
            'paper_profile' => $this->nullableTrim('paper_profile'),
            'rows_per_page' => $this->nullableInt('rows_per_page'),
            'grid_rows' => $this->nullableInt('grid_rows'),
            'last_page_grid_rows' => $this->nullableInt('last_page_grid_rows'),
            'description_chars_per_line' => $this->nullableInt('description_chars_per_line'),
            'preview' => $this->has('preview') ? $this->boolean('preview') : null,
        ]);
    }

    public function paperOverrides(): array
    {
        return array_filter([
            'rows_per_page' => $this->validated('rows_per_page'),
            'grid_rows' => $this->validated('grid_rows'),
            'last_page_grid_rows' => $this->validated('last_page_grid_rows'),
            'description_chars_per_line' => $this->validated('description_chars_per_line'),
        ], static fn ($value): bool => is_int($value));
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

    private function nullableInt(string $key): ?int
    {
        $value = $this->input($key);

        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @return array<int, string>
     */
    private function allowedPapers(): array
    {
        return app(PrintConfigLoaderInterface::class)->allowedPapers('gso_ssmi', 'a4-landscape');
    }
}
