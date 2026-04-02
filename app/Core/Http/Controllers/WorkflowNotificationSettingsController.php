<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Requests\Notifications\UpdateWorkflowNotificationSettingsRequest;
use App\Core\Models\Module;
use App\Core\Services\Contracts\Notifications\WorkflowNotificationSettingsServiceInterface;
use App\Core\Support\AdminContextAuthorizer;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowNotificationSettingsController extends Controller
{
    public function __construct(
        private readonly WorkflowNotificationSettingsServiceInterface $settings,
        private readonly AdminContextAuthorizer $authorizer,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('workflow-notifications.index', [
            'notificationContexts' => $this->settings->contexts(),
            'canUpdateWorkflowNotifications' => (bool) $user
                && $this->authorizer->allowsPermission($user, 'workflow_notifications.update'),
        ]);
    }

    public function update(UpdateWorkflowNotificationSettingsRequest $request): RedirectResponse
    {
        $moduleId = (string) $request->validated('module_id');
        $events = (array) $request->validated('events', []);
        $this->settings->updateModuleSettings($moduleId, $events);

        $module = Module::query()->findOrFail($moduleId);

        return redirect()
            ->route('workflow-notifications.index')
            ->with('status', "Workflow notification rules saved for {$module->name}.");
    }
}
