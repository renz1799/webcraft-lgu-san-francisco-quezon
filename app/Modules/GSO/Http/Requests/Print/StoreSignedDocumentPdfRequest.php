<?php

namespace App\Modules\GSO\Http\Requests\Print;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignedDocumentPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'signed_pdf' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
            'paper_profile' => ['nullable', 'string', 'max:100'],
            'rows_per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'grid_rows' => ['nullable', 'integer', 'min:1', 'max:200'],
            'last_page_grid_rows' => ['nullable', 'integer', 'min:0', 'max:200'],
            'description_chars_per_line' => ['nullable', 'integer', 'min:1', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'signed_pdf.required' => 'Select the scanned signed PDF to upload.',
            'signed_pdf.file' => 'The signed document upload must be a file.',
            'signed_pdf.mimes' => 'Only PDF files can be uploaded as signed documents.',
            'signed_pdf.mimetypes' => 'Only PDF files can be uploaded as signed documents.',
            'signed_pdf.max' => 'The signed PDF must be 10 MB or smaller.',
        ];
    }

    public function releaseSessionLock(): void
    {
        if (! $this->expectsJson() || ! $this->hasSession()) {
            return;
        }

        $this->session()->save();

        if (function_exists('session_write_close')) {
            @session_write_close();
        }
    }
}
