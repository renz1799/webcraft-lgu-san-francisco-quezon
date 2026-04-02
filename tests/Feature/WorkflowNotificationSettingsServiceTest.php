<?php

namespace Tests\Feature;

use App\Core\Models\AppSetting;
use App\Core\Models\Module;
use App\Core\Models\Role;
use App\Core\Services\Notifications\WorkflowNotificationSettingsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkflowNotificationSettingsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        $this->seedConfig();
    }

    public function test_contexts_show_default_and_stored_role_sets(): void
    {
        $module = Module::query()->create([
            'code' => 'GSO',
            'name' => 'General Services Office',
            'description' => 'GSO module',
            'url' => 'https://gso.test',
            'is_active' => true,
        ]);

        Role::query()->create([
            'id' => 'role-inspector',
            'module_id' => (string) $module->id,
            'name' => 'Inspector',
            'guard_name' => 'web',
        ]);

        Role::query()->create([
            'id' => 'role-staff',
            'module_id' => (string) $module->id,
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        AppSetting::query()->create([
            'module_id' => (string) $module->id,
            'key' => 'workflow.notifications',
            'value' => [
                'air.submitted' => [
                    'roles' => ['Staff'],
                    'message_template' => '{air_number} is assigned to you. Click to open the task.',
                ],
            ],
        ]);

        $context = $this->makeService()->contexts()->first();

        $this->assertNotNull($context);
        $this->assertSame('GSO Workflow Notification Rules', $context['title']);

        $event = collect($context['events'])->firstWhere('key', 'air.submitted');

        $this->assertSame(['Inspector'], $event['default_roles']);
        $this->assertSame(['Staff'], $event['stored_roles']);
        $this->assertSame(['Staff'], $event['effective_roles']);
        $this->assertSame(
            '{air_number} is submitted and ready for inspection review. Click to open the assigned task and continue the workflow.',
            $event['default_message_template']
        );
        $this->assertSame(
            '{air_number} is assigned to you. Click to open the task.',
            $event['stored_message_template']
        );
        $this->assertSame(
            '{air_number} is assigned to you. Click to open the task.',
            $event['effective_message_template']
        );
        $this->assertSame('App settings', $event['source']);
        $this->assertSame(['Inspector', 'Staff'], $event['available_roles']);

        $followUpEvent = collect($context['events'])->firstWhere('key', 'air.follow_up_created');

        $this->assertNotNull($followUpEvent);
        $this->assertSame(['Administrator', 'Staff'], $followUpEvent['default_roles']);
        $this->assertSame(
            '{air_label} follow-up AIR is created for unresolved inspection items. Click to open the assigned task and continue the workflow.',
            $followUpEvent['effective_message_template']
        );
    }

    public function test_update_uses_default_fallback_and_can_disable_event(): void
    {
        $module = Module::query()->create([
            'code' => 'GSO',
            'name' => 'General Services Office',
            'description' => 'GSO module',
            'url' => 'https://gso.test',
            'is_active' => true,
        ]);

        Role::query()->create([
            'id' => 'role-inspector',
            'module_id' => (string) $module->id,
            'name' => 'Inspector',
            'guard_name' => 'web',
        ]);

        Role::query()->create([
            'id' => 'role-staff',
            'module_id' => (string) $module->id,
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        $service = $this->makeService();

        $storedDefaults = $service->updateModuleSettings((string) $module->id, [
            'air.submitted' => [
                'event_key' => 'air.submitted',
                'roles' => ['Inspector'],
                'message_template' => '{air_number} is submitted and ready for inspection review. Click to open the assigned task and continue the workflow.',
            ],
        ]);

        $this->assertSame([], $storedDefaults);
        $this->assertSame(['Inspector'], $service->rolesForEvent('GSO', 'air.submitted'));

        $storedDisabled = $service->updateModuleSettings((string) $module->id, [
            'air.submitted' => [
                'event_key' => 'air.submitted',
                'roles' => [],
                'message_template' => '{air_number} is submitted and ready for inspection review. Click to open the assigned task and continue the workflow.',
            ],
        ]);

        $this->assertArrayHasKey('air.submitted', $storedDisabled);
        $this->assertSame(['roles' => []], $storedDisabled['air.submitted']);
        $this->assertSame([], $service->rolesForEvent('GSO', 'air.submitted'));

        $storedOverride = $service->updateModuleSettings((string) $module->id, [
            'air.submitted' => [
                'event_key' => 'air.submitted',
                'roles' => ['Inspector', 'Staff', 'Unknown'],
                'message_template' => '{po_number} is submitted and assigned to your team. Click to open the task.',
            ],
        ]);

        $this->assertSame(['Inspector', 'Staff'], $storedOverride['air.submitted']['roles']);
        $this->assertSame(
            '{po_number} is submitted and assigned to your team. Click to open the task.',
            $storedOverride['air.submitted']['message_template']
        );
        $this->assertSame(['Inspector', 'Staff'], $service->rolesForEvent('GSO', 'air.submitted'));
        $this->assertSame(
            '{po_number} is submitted and assigned to your team. Click to open the task.',
            $service->messageTemplateForEvent('GSO', 'air.submitted')
        );
    }

    private function makeService(): WorkflowNotificationSettingsService
    {
        return new WorkflowNotificationSettingsService();
    }

    private function createSchema(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['module_id', 'key'], 'app_settings_module_key_unique');
        });
    }

    private function seedConfig(): void
    {
        config()->set('workflow-notifications.modules', [
            'GSO' => [
                'setting_key' => 'workflow.notifications',
                'title' => 'GSO Workflow Notification Rules',
                'description' => 'Workflow recipients for GSO.',
                'events' => [
                    'air.submitted' => [
                        'label' => 'AIR Submitted',
                        'description' => 'AIR is ready for inspection.',
                        'default_roles' => ['Inspector'],
                        'message_template' => '{air_number} is submitted and ready for inspection review. Click to open the assigned task and continue the workflow.',
                        'placeholders' => [
                            '{air_number}' => 'AIR number',
                            '{po_number}' => 'PO number',
                        ],
                    ],
                    'air.inspection_finalized' => [
                        'label' => 'AIR Inspection Finalized',
                        'description' => 'Inspection has been finalized.',
                        'default_roles' => ['Administrator', 'Staff'],
                        'message_template' => '{air_number} inspection is finalized. Click to open the inspection record.',
                    ],
                    'air.follow_up_created' => [
                        'label' => 'AIR Follow-up Created',
                        'description' => 'Follow-up AIR draft was created.',
                        'default_roles' => ['Administrator', 'Staff'],
                        'message_template' => '{air_label} follow-up AIR is created for unresolved inspection items. Click to open the assigned task and continue the workflow.',
                        'placeholders' => [
                            '{air_label}' => 'Follow-up AIR label',
                            '{source_air_label}' => 'Source AIR label',
                            '{task_url}' => 'Task URL',
                            '{follow_up_url}' => 'Follow-up AIR URL',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
