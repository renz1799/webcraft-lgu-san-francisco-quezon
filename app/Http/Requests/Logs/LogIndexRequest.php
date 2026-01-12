<?php
// app/Http/Requests/Logs/LogIndexRequest.php
namespace App\Http\Requests\Logs;

use Illuminate\Foundation\Http\FormRequest;

class LogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && ($u->hasRole('Administrator') || $u->can('view Audit Logs'));
    }

    public function rules(): array
    {
        return [
            'action'    => ['nullable','string','max:255'],
            'actor_id'  => ['nullable','uuid'],
            'date_from' => ['nullable','date'],
            'date_to'   => ['nullable','date','after_or_equal:date_from'],
            'per_page'  => ['nullable','integer','min:1','max:200'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Trim all string inputs and convert empty strings to null
        $clean = [];
        foreach ($this->all() as $k => $v) {
            if (is_string($v)) {
                $v = trim($v);
                if ($v === '') $v = null;
            }
            $clean[$k] = $v;
        }
        $this->replace($clean);
    }

    /** Convenience: returns sanitized filters incl. per_page default */
    public function filters(): array
    {
        $v = $this->validated();
        $v['per_page'] = (int)($v['per_page'] ?? 50);
        return $v;
    }
}
