<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintRisRequest extends FormRequest
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
            ->allowedPapers('gso_ris', 'a4-portrait');

        return [
            'preview' => ['nullable', 'boolean'],
            'paper_profile' => ['nullable', 'string', 'max:100', Rule::in($allowedPapers)],
        ];
    }
}
