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
        // 'manage' => 'modify', // keep only if UI uses "manage"
    ];

    public function authorize(): bool
    {
        $u = $this->user();
        return (bool) $this->user()?->hasRole('Administrator');
    }

    protected function prepareForValidation(): void
    {
        $input = $this->input('permissions');
        if ($input === null) return;

        $alreadyNested = is_array($input) && !Arr::isList($input) &&
            collect($input)->every(fn ($v) => is_array($v));
        if ($alreadyNested) return;

        $nested = [];

        if (is_array($input)) {
            foreach ($input as $key => $val) {
                if (!is_string($key)) continue;
                if (!$val) continue;

                [$verb, $resource] = explode(' ', $key, 2) + [null, null];
                if (!$verb || !$resource) continue;

                $resource = trim($resource);
                if ($resource === '' || mb_strlen($resource) > 100) continue;

                $action = self::VERB_MAP[strtolower($verb)] ?? strtolower($verb);

                $actions = is_array($val) ? $val : [$action];
                foreach ($actions as $a) {
                    $a = self::VERB_MAP[strtolower((string) $a)] ?? strtolower((string) $a);
                    $nested['__flat__'][$resource][] = $a;
                }
            }
        }

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
                'sometimes', 'nullable', 'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],

            'permissions'       => ['sometimes', 'array', 'max:50'],
            'permissions.*'     => ['array', 'max:50'], // page
            'permissions.*.*'   => ['array', 'max:50'], // resource
            'permissions.*.*.*' => ['string', 'in:view,create,update,edit,modify,delete,export'],
        ];
    }
}
