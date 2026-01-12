<?php

namespace App\Http\Requests\Permissions;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && ($u->hasRole('Administrator'));
    }

    public function rules(): array
    {
        return [
            // Expect "view Something", "modify Something", "delete Something"
            'name'       => [
                'bail','required','string','max:255',
                'regex:/^(view|modify|delete)\s+.+$/i',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', $this->input('guard_name', 'web')),
            ],
            'page'       => ['bail','required','string','max:255'],
            'guard_name' => ['bail','sometimes','in:web,api'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $guard = $this->input('guard_name', 'web');

        // If page not provided, derive from name (everything after the first space)
        $page = $this->input('page');
        if (!$page && $name) {
            $parts = preg_split('/\s+/', $name, 2);
            $page  = $parts[1] ?? null;
        }

        // Normalize action casing (lower), page title case-ish (optional)
        if ($name) {
            if (preg_match('/^(view|modify|delete)\s+(.+)$/i', $name, $m)) {
                $action = strtolower($m[1]);
                $rest   = $m[2];
                $name   = "{$action} {$rest}";
            }
        }

        $this->merge([
            'name'       => $name,
            'page'       => $page ? trim($page) : null,
            'guard_name' => $guard,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name must start with "view", "modify", or "delete", e.g. "view Login Logs".',
        ];
    }
}
