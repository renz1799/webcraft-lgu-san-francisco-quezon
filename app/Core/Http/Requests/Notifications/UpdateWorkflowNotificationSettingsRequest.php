<?php

namespace App\Core\Http\Requests\Notifications;

use App\Core\Models\Module;
use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkflowNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u
            && app(AdminContextAuthorizer::class)->allowsPermission($u, 'workflow_notifications.update');
    }

    public function rules(): array
    {
        return [
            'module_id' => [
                'required',
                'uuid',
                Rule::exists(Module::class, 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'events' => ['required', 'array'],
            'events.*' => ['required', 'array'],
            'events.*.event_key' => ['nullable', 'string', 'max:120'],
            'events.*.roles' => ['nullable', 'array'],
            'events.*.roles.*' => ['nullable', 'string', 'max:120'],
            'events.*.message_template' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
