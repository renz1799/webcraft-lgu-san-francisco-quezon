<?php

namespace App\Modules\GSO\Http\Requests\ITR;

use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintItrRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'itr.print',
            'itr.view',
            'itr.update',
        ]);
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

        if ($this->has('preview')) {
            $this->merge([
                'preview' => $this->boolean('preview'),
            ]);
        }

        $normalizeInteger('rows_per_page');
        $normalizeInteger('grid_rows');
        $normalizeInteger('last_page_grid_rows');
        $normalizeInteger('description_chars_per_line');
    }

    public function rules(): array
    {
        $allowedPapers = app(PrintConfigLoaderInterface::class)
            ->allowedPapers('gso_itr', 'a4-portrait');

        return [
            'preview' => ['nullable', 'boolean'],
            'paper_profile' => ['nullable', 'string', 'max:100', Rule::in($allowedPapers)],
            'rows_per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'grid_rows' => ['nullable', 'integer', 'min:1', 'max:200'],
            'last_page_grid_rows' => ['nullable', 'integer', 'min:0', 'max:200'],
            'description_chars_per_line' => ['nullable', 'integer', 'min:10', 'max:300'],
        ];
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
}
