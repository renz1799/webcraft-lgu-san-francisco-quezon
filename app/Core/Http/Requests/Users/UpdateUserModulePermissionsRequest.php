<?php

namespace App\Core\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use App\Core\Support\PermissionNaming;
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
        return app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user());
    }

    protected function prepareForValidation(): void
    {
        $input = $this->input('permissions');
        if ($input === null) return;

        $alreadyNested = is_array($input) && !Arr::isList($input) &&
            collect($input)->every(fn ($v) => is_array($v));
        if ($alreadyNested) {
            $normalized = [];

            foreach ($input as $page => $resources) {
                if (! is_array($resources)) {
                    continue;
                }

                foreach ($resources as $resource => $actions) {
                    if (! is_array($actions)) {
                        continue;
                    }

                    foreach ($actions as $action) {
                        $normalizedAction = PermissionNaming::normalizeAction((string) $action);

                        if ($normalizedAction === '') {
                            continue;
                        }

                        $normalized[$page][$resource][] = $normalizedAction;
                    }
                }
            }

            foreach ($normalized as $page => $resources) {
                foreach ($resources as $resource => $actions) {
                    $normalized[$page][$resource] = array_values(array_unique($actions));
                }
            }

            $this->merge(['permissions' => $normalized]);
            return;
        }

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
        $moduleId = $this->currentModuleId();

        return [
            'role' => [
                'sometimes', 'nullable', 'string',
                Rule::exists('roles', 'name')->where(function ($query) use ($moduleId) {
                    $query->where('guard_name', 'web')
                        ->whereNull('deleted_at');

                    if ($moduleId) {
                        $query->where('module_id', $moduleId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }),
            ],

            'permissions'       => ['sometimes', 'array', 'max:50'],
            'permissions.*'     => ['array', 'max:50'], // page
            'permissions.*.*'   => ['array', 'max:50'], // resource
            'permissions.*.*.*' => ['string', Rule::in(PermissionNaming::actionKeys())],
        ];
    }

    private function currentModuleId(): ?string
    {
        return app(CurrentContext::class)->moduleId();
    }
}
