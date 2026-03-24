<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintAirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('preview')) {
            $this->merge([
                'preview' => $this->boolean('preview'),
            ]);
        }
    }

    public function rules(): array
    {
        $allowedPapers = app(PrintConfigLoaderInterface::class)
            ->allowedPapers('gso_air', 'a4-portrait');

        return [
            'preview' => ['nullable', 'boolean'],
            'paper_profile' => ['nullable', 'string', 'max:100', Rule::in($allowedPapers)],
        ];
    }
}
