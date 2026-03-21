<?php

namespace App\Core\Http\Requests\Theme;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeColorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'menu' => ['sometimes', 'in:light,dark,color,gradient,transparent'],
            'header' => ['sometimes', 'in:light,dark,color,gradient,transparent'],
            'primaryRgb' => ['sometimes', 'regex:/^\d{1,3},\s*\d{1,3},\s*\d{1,3}$/'],
            'primaryRgb1' => ['sometimes', 'regex:/^\d{1,3}\s+\d{1,3}\s+\d{1,3}$/'],
            'bodyBgRgb' => ['sometimes', 'regex:/^\d{1,3}\s+\d{1,3}\s+\d{1,3}$/'],
            'darkBgRgb' => ['sometimes', 'regex:/^\d{1,3}\s+\d{1,3}\s+\d{1,3}$/'],
            'bgImage' => ['sometimes', 'in:bgimg1,bgimg2,bgimg3,bgimg4,bgimg5'],
        ];
    }
}
