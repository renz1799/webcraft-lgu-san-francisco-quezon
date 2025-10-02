<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateUserModulePermissionsRequest extends BaseFormRequest
{
    private const VERB_MAP = [
        'view'   => 'view',
        'modify' => 'modify',
        'delete' => 'delete',
        'create' => 'create',
        'update' => 'update',
        'edit'   => 'edit',
        'export' => 'export',
        'manage' => 'modify', // map “manage” to modify (or keep 'manage' if you really use it)
    ];

    public function authorize(): bool
    {
        $u = $this->user();
        return $u && ($u->hasRole('admin') || $u->can('modify User Lists'));
    }

    /** Accept flat or nested and coerce to nested {page:{resource:[actions]}} */
    protected function prepareForValidation(): void
    {
        $input = $this->input('permissions');

        // Nothing provided (e.g., role-only change) — leave as-is
        if ($input === null) return;

        // Already nested? (permissions => array of arrays)
        $alreadyNested = is_array($input) && !Arr::isList($input) &&
                         collect($input)->every(fn($v) => is_array($v));
        if ($alreadyNested) return;

        // Case: flat associative or mixed — build nested under a dummy page
        $nested = [];

        if (is_array($input)) {
            foreach ($input as $key => $val) {
                // Accept {"View Users": true} or {"Modify Login Logs": ["modify","delete"]}
                if (!is_string($key)) continue;
                if (!$val) continue; // skip falsy

                [$verb, $resource] = explode(' ', $key, 2) + [null, null];
                if (!$verb || !$resource) continue;

                $action = self::VERB_MAP[strtolower($verb)] ?? strtolower($verb);

                $actions = is_array($val) ? $val : [$action];
                foreach ($actions as $a) {
                    $a = self::VERB_MAP[strtolower((string)$a)] ?? strtolower((string)$a);
                    $nested['__flat__'][$resource][] = $a;
                }
            }
        }

        // De-duplicate & sort actions for consistency
        foreach ($nested as $page => $resources) {
            foreach ($resources as $res => $acts) {
                $nested[$page][$res] = array_values(array_unique(array_map('strtolower', $acts)));
            }
        }

        $this->merge(['permissions' => $nested]);
    }

    public function rules(): array
    {
        return [
            'role' => [
                'sometimes','nullable','string',
                Rule::exists('roles','name')->where('guard_name','web'),
            ],
            'permissions'       => ['sometimes','array'],
            'permissions.*'     => ['array'], // page
            'permissions.*.*'   => ['array'], // resource
            'permissions.*.*.*' => ['string','in:view,create,update,edit,modify,delete,export'],
        ];
    }
}
