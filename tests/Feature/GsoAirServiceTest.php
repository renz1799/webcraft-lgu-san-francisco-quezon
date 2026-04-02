<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\AccountablePersons\AccountablePersonServiceInterface;
use App\Core\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Core\Services\Contracts\Notifications\WorkflowNotificationSettingsServiceInterface;
use Illuminate\Support\Facades\Route;
use App\Core\Models\Tasks\Task;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Modules\GSO\Builders\Air\AirDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\Air\AirService;
use App\Modules\GSO\Support\Air\AirStatuses;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Carbon::setTestNow(Carbon::parse('2026-03-21 09:00:00'));

        if (! Route::has('gso.tasks.show')) {
            Route::get('/gso/tasks/{id}', fn () => 'gso-task')->name('gso.tasks.show');
        }

        if (! Route::has('gso.air.inspect')) {
            Route::get('/gso/air/{air}/inspect', fn () => 'gso-air-inspect')->name('gso.air.inspect');
        }

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function test_air_service_handles_draft_lifecycle_and_filters(): void
    {
        DB::table('users')->insert([
            'id' => 'user-1',
            'primary_department_id' => 'dept-1',
            'username' => 'gso.admin',
            'email' => 'gso.admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            [
                'id' => 'dept-1',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-2',
                'code' => 'BUDG',
                'name' => 'Budget Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('fund_sources')->insert([
            [
                'id' => 'fund-gf',
                'code' => 'GF',
                'name' => 'General Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'fund-sef',
                'code' => 'SEF',
                'name' => 'Special Education Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(5);
        $accountableOfficers = Mockery::mock(AccountablePersonServiceInterface::class);
        $accountableOfficers->shouldReceive('createOrResolve')->times(4)->andReturn([
            'officer' => ['id' => 'officer-1', 'full_name' => 'Juan Dela Cruz'],
            'created' => false,
            'restored' => false,
            'reused' => true,
        ]);
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldReceive('findLatestBySubject')->once()->andReturn(null);
        $createdTask = new \App\Core\Models\Tasks\Task([
            'status' => 'pending',
        ]);
        $createdTask->id = 'task-1';
        $tasks->shouldReceive('createUnassigned')->once()->andReturn($createdTask);
        $tasks->shouldReceive('recordEvent')->once();
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $notifications->shouldReceive('notifyUsersByRoles')
            ->once()
            ->withArgs(function (
                array $roles,
                string $actorUserId,
                string $type,
                string $title,
                string $message,
                string $entityType,
                string $entityId,
                array $data
            ): bool {
                return in_array('Inspector', $roles, true)
                    && $actorUserId === 'user-1'
                    && $type === 'gso.air.submitted'
                    && str_contains($title, 'AIR ready for inspection:')
                    && str_contains($message, 'is submitted and ready for inspection review.')
                    && $entityType === 'air'
                    && $entityId !== ''
                    && ($data['subject_url'] ?? '') !== ''
                    && ($data['url'] ?? '') === route('gso.tasks.show', ['id' => 'task-1']);
            });
        $workflowNotifications = Mockery::mock(WorkflowNotificationSettingsServiceInterface::class);
        $workflowNotifications->shouldReceive('rolesForEvent')
            ->once()
            ->with('GSO', 'air.submitted')
            ->andReturn(['Inspector']);
        $workflowNotifications->shouldReceive('messageTemplateForEvent')
            ->once()
            ->with('GSO', 'air.submitted')
            ->andReturn('{air_label} is submitted and ready for inspection review. Click to open the assigned task and continue the workflow.');

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
            $accountableOfficers,
            $tasks,
            $notifications,
            $workflowNotifications,
        );

        $created = $service->createBlankDraft('user-1');

        $this->assertDatabaseHas('airs', [
            'id' => $created->id,
            'status' => AirStatuses::DRAFT,
            'requesting_department_id' => 'dept-1',
            'fund_source_id' => 'fund-gf',
            'supplier_name' => 'TBD',
        ]);

        $updated = $service->updateDraft('user-1', (string) $created->id, [
            'po_number' => 'PO-2026-001',
            'po_date' => '2026-03-21',
            'air_number' => '',
            'air_date' => '2026-03-21',
            'invoice_number' => 'INV-100',
            'invoice_date' => '2026-03-20',
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-2',
            'fund_source_id' => 'fund-sef',
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
            'remarks' => 'Ready for submission',
        ]);

        $this->assertSame('PO-2026-001', $updated->po_number);
        $this->assertSame('dept-2', $updated->requesting_department_id);
        $this->assertSame('Budget Office', $updated->requesting_department_name_snapshot);
        $this->assertSame('fund-sef', $updated->fund_source_id);

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop',
            'description' => 'Portable computer',
            'base_unit' => 'unit',
            'item_identification' => 'ITM-001',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('item_unit_conversions')->insert([
            'id' => 'conv-1',
            'item_id' => 'item-1',
            'from_unit' => 'box',
            'multiplier' => 5,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-1',
            'air_id' => $created->id,
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'ITM-001',
            'item_name_snapshot' => 'Laptop',
            'description_snapshot' => 'Portable computer',
            'unit_snapshot' => 'unit',
            'acquisition_cost' => 55000,
            'qty_ordered' => 2,
            'qty_delivered' => 0,
            'qty_accepted' => 0,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filtered = $service->datatable([
            'search' => 'Acme',
            'department_id' => 'dept-2',
            'status' => AirStatuses::DRAFT,
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filtered['total']);
        $this->assertSame('Acme Trading', $filtered['data'][0]['supplier_name']);
        $this->assertSame('BUDG - Budget Office', $filtered['data'][0]['department_label']);

        DB::table('airs')
            ->where('id', $created->id)
            ->update([
                'received_completeness' => 'complete',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $legacyStyleFiltered = $service->datatable([
            'department' => 'Budget',
            'received_completeness' => 'complete',
            'status' => AirStatuses::DRAFT,
            'archived' => 'active',
        ]);

        $this->assertSame(1, $legacyStyleFiltered['total']);
        $this->assertSame('complete', $legacyStyleFiltered['data'][0]['received_completeness']);
        $this->assertSame('BUDG - Budget Office', $legacyStyleFiltered['data'][0]['department_label']);

        $submitted = $service->submitDraft('user-1', (string) $created->id);

        $this->assertSame(AirStatuses::SUBMITTED, $submitted->status);
        $this->assertStringStartsWith('AIR-2026-', (string) $submitted->air_number);

        $service->delete('user-1', (string) $created->id);

        $archived = $service->datatable([
            'search' => 'PO-2026-001',
            'archived' => 'archived',
        ]);

        $this->assertSame(1, $archived['total']);
        $this->assertTrue($archived['data'][0]['is_archived']);

        $service->restore('user-1', (string) $created->id);

        $restored = $service->datatable([
            'search' => 'PO-2026-001',
            'archived' => 'active',
            'status' => AirStatuses::SUBMITTED,
        ]);

        $this->assertSame(1, $restored['total']);
        $this->assertFalse($restored['data'][0]['is_archived']);
        $this->assertSame('Submitted', $restored['data'][0]['status_text']);
    }

    public function test_air_service_requires_real_po_number_before_submit(): void
    {
        DB::table('users')->insert([
            'id' => 'user-1',
            'primary_department_id' => null,
            'username' => 'gso.admin',
            'email' => 'gso.admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'code' => 'GSO',
            'name' => 'General Services Office',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            'id' => 'fund-gf',
            'code' => 'GF',
            'name' => 'General Fund',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();
        $accountableOfficers = Mockery::mock(AccountablePersonServiceInterface::class);
        $accountableOfficers->shouldNotReceive('createOrResolve');
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldNotReceive('findLatestBySubject');
        $tasks->shouldNotReceive('createUnassigned');
        $tasks->shouldNotReceive('recordEvent');
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $notifications->shouldNotReceive('notifyUsersByRoles');
        $workflowNotifications = Mockery::mock(WorkflowNotificationSettingsServiceInterface::class);
        $workflowNotifications->shouldNotReceive('rolesForEvent');
        $workflowNotifications->shouldNotReceive('messageTemplateForEvent');

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
            $accountableOfficers,
            $tasks,
            $notifications,
            $workflowNotifications,
        );

        $created = $service->createBlankDraft('user-1');

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Office Chair',
            'description' => 'Ergonomic chair',
            'base_unit' => 'piece',
            'item_identification' => 'FUR-001',
            'tracking_type' => 'property',
            'requires_serial' => false,
            'is_semi_expendable' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-1',
            'air_id' => $created->id,
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'FUR-001',
            'item_name_snapshot' => 'Office Chair',
            'description_snapshot' => 'Ergonomic chair',
            'unit_snapshot' => 'piece',
            'acquisition_cost' => 4200,
            'qty_ordered' => 1,
            'qty_delivered' => 0,
            'qty_accepted' => 0,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => false,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Replace the placeholder PO number before submitting this AIR.');

        $service->submitDraft('user-1', (string) $created->id);
    }

    public function test_air_service_requires_at_least_one_item_before_submit(): void
    {
        DB::table('users')->insert([
            'id' => 'user-1',
            'primary_department_id' => null,
            'username' => 'gso.admin',
            'email' => 'gso.admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'code' => 'GSO',
            'name' => 'General Services Office',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            'id' => 'fund-gf',
            'code' => 'GF',
            'name' => 'General Fund',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(2);
        $accountableOfficers = Mockery::mock(AccountablePersonServiceInterface::class);
        $accountableOfficers->shouldReceive('createOrResolve')->times(2)->andReturn([
            'officer' => ['id' => 'officer-1', 'full_name' => 'Juan Dela Cruz'],
            'created' => false,
            'restored' => false,
            'reused' => true,
        ]);
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldNotReceive('findLatestBySubject');
        $tasks->shouldNotReceive('createUnassigned');
        $tasks->shouldNotReceive('recordEvent');
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $notifications->shouldNotReceive('notifyUsersByRoles');
        $workflowNotifications = Mockery::mock(WorkflowNotificationSettingsServiceInterface::class);
        $workflowNotifications->shouldNotReceive('rolesForEvent');
        $workflowNotifications->shouldNotReceive('messageTemplateForEvent');

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
            $accountableOfficers,
            $tasks,
            $notifications,
            $workflowNotifications,
        );

        $created = $service->createBlankDraft('user-1');
        $service->updateDraft('user-1', (string) $created->id, [
            'po_number' => 'PO-2026-010',
            'po_date' => '2026-03-21',
            'air_date' => '2026-03-21',
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-1',
            'fund_source_id' => 'fund-gf',
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Add at least one item before submitting this AIR.');

        $service->submitDraft('user-1', (string) $created->id);
    }

    public function test_it_creates_a_follow_up_air_for_unresolved_inspection_items(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-source',
            'parent_air_id' => null,
            'continuation_no' => 1,
            'po_number' => 'PO-2026-020',
            'po_date' => '2026-03-21',
            'air_number' => 'AIR-2026-0020',
            'air_date' => '2026-03-21',
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-1',
            'requesting_department_name_snapshot' => 'General Services Office',
            'requesting_department_code_snapshot' => 'GSO',
            'fund_source_id' => 'fund-gf',
            'fund' => 'General Fund',
            'status' => AirStatuses::INSPECTED,
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            [
                'id' => 'item-1',
                'item_name' => 'Laptop',
                'description' => 'Portable computer',
                'base_unit' => 'unit',
                'item_identification' => 'ITM-001',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'is_semi_expendable' => false,
                'is_selected' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-2',
                'item_name' => 'Bond Paper',
                'description' => 'A4 ream',
                'base_unit' => 'ream',
                'item_identification' => 'ITM-002',
                'tracking_type' => 'consumable',
                'requires_serial' => false,
                'is_semi_expendable' => false,
                'is_selected' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('air_items')->insert([
            [
                'id' => 'air-item-1',
                'air_id' => 'air-source',
                'item_id' => 'item-1',
                'stock_no_snapshot' => 'ITM-001',
                'item_name_snapshot' => 'Laptop',
                'description_snapshot' => 'Portable computer',
                'unit_snapshot' => 'unit',
                'acquisition_cost' => 55000,
                'qty_ordered' => 2,
                'qty_delivered' => 1,
                'qty_accepted' => 1,
                'tracking_type_snapshot' => 'property',
                'requires_serial_snapshot' => true,
                'is_semi_expendable_snapshot' => false,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'air-item-2',
                'air_id' => 'air-source',
                'item_id' => 'item-2',
                'stock_no_snapshot' => 'ITM-002',
                'item_name_snapshot' => 'Bond Paper',
                'description_snapshot' => 'A4 ream',
                'unit_snapshot' => 'ream',
                'acquisition_cost' => 250,
                'qty_ordered' => 5,
                'qty_delivered' => 5,
                'qty_accepted' => 5,
                'tracking_type_snapshot' => 'consumable',
                'requires_serial_snapshot' => false,
                'is_semi_expendable_snapshot' => false,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->twice();
        $accountableOfficers = Mockery::mock(AccountablePersonServiceInterface::class);
        $accountableOfficers->shouldReceive('createOrResolve')->times(2)->andReturn([
            'officer' => ['id' => 'officer-1', 'full_name' => 'Juan Dela Cruz'],
            'created' => false,
            'restored' => false,
            'reused' => true,
        ]);
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldReceive('findLatestBySubject')->once()->andReturn(null);
        $followUpTask = new Task([
            'status' => Task::STATUS_PENDING,
        ]);
        $followUpTask->id = 'task-follow-up';
        $tasks->shouldReceive('createUnassigned')->once()->andReturn($followUpTask);
        $tasks->shouldReceive('recordEvent')->once();
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $notifications->shouldReceive('notifyUsersByRoles')
            ->once()
            ->withArgs(function (
                array $roles,
                string $actorUserId,
                string $type,
                string $title,
                string $message,
                string $entityType,
                string $entityId,
                array $data
            ): bool {
                return $roles === ['Inspector']
                    && $actorUserId === 'user-1'
                    && $type === 'gso.air.submitted'
                    && str_contains($title, 'AIR ready for inspection:')
                    && str_contains($message, 'is submitted and ready for inspection review.')
                    && $entityType === 'air'
                    && $entityId !== ''
                    && ($data['url'] ?? '') === route('gso.tasks.show', ['id' => 'task-follow-up']);
            });
        $notifications->shouldReceive('notifyUsersByRoles')
            ->once()
            ->withArgs(function (
                array $roles,
                string $actorUserId,
                string $type,
                string $title,
                string $message,
                string $entityType,
                string $entityId,
                array $data
            ): bool {
                return $roles === ['Administrator', 'Staff']
                    && $actorUserId === 'user-1'
                    && $type === 'gso.air.follow-up.created'
                    && str_contains($title, 'Follow-up AIR created:')
                    && str_contains($message, 'follow-up AIR is created for unresolved inspection items')
                    && $entityType === 'air'
                    && $entityId !== ''
                    && ($data['url'] ?? '') === route('gso.air.inspect', ['air' => $entityId])
                    && ! empty($data['subject_url']);
            });
        $workflowNotifications = Mockery::mock(WorkflowNotificationSettingsServiceInterface::class);
        $workflowNotifications->shouldReceive('rolesForEvent')
            ->once()
            ->with('GSO', 'air.submitted')
            ->andReturn(['Inspector']);
        $workflowNotifications->shouldReceive('messageTemplateForEvent')
            ->once()
            ->with('GSO', 'air.submitted')
            ->andReturn('{air_label} is submitted and ready for inspection review. Click to open the assigned task and continue the workflow.');
        $workflowNotifications->shouldReceive('rolesForEvent')
            ->once()
            ->with('GSO', 'air.follow_up_created')
            ->andReturn(['Administrator', 'Staff']);
        $workflowNotifications->shouldReceive('messageTemplateForEvent')
            ->once()
            ->with('GSO', 'air.follow_up_created')
            ->andReturn('{air_label} follow-up AIR is created for unresolved inspection items. Click to open the assigned task and continue the workflow.');

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
            $accountableOfficers,
            $tasks,
            $notifications,
            $workflowNotifications,
        );

        $followUp = $service->createFollowUpFromInspection('user-1', 'air-source');

        $this->assertSame(AirStatuses::SUBMITTED, $followUp->status);
        $this->assertSame('air-source', (string) $followUp->parent_air_id);
        $this->assertSame(2, (int) $followUp->continuation_no);
        $this->assertNotSame('', trim((string) ($followUp->air_number ?? '')));

        $this->assertDatabaseHas('airs', [
            'id' => $followUp->id,
            'parent_air_id' => 'air-source',
            'status' => AirStatuses::SUBMITTED,
        ]);

        $this->assertSame(1, DB::table('air_items')->where('air_id', $followUp->id)->count());
        $this->assertDatabaseHas('air_items', [
            'air_id' => $followUp->id,
            'item_id' => 'item-1',
            'qty_delivered' => 0,
            'qty_accepted' => 0,
        ]);
    }

    public function test_it_reopens_an_inspected_air_back_to_submitted_and_reopens_its_task(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-reopen',
            'parent_air_id' => null,
            'continuation_no' => 1,
            'po_number' => 'PO-2026-021',
            'po_date' => '2026-03-21',
            'air_number' => 'AIR-2026-0021',
            'air_date' => '2026-03-21',
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-1',
            'requesting_department_name_snapshot' => 'General Services Office',
            'requesting_department_code_snapshot' => 'GSO',
            'fund_source_id' => 'fund-gf',
            'fund' => 'General Fund',
            'status' => AirStatuses::INSPECTED,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();
        $accountableOfficers = Mockery::mock(AccountablePersonServiceInterface::class);
        $accountableOfficers->shouldNotReceive('createOrResolve');
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldReceive('findLatestBySubject')->once()->andReturn(new Task([
            'id' => 'task-reopen',
            'status' => Task::STATUS_DONE,
        ]));
        $tasks->shouldReceive('syncTaskContext')->once()->andReturn(new Task([
            'id' => 'task-reopen',
            'status' => Task::STATUS_DONE,
        ]));
        $tasks->shouldReceive('changeStatus')->once()->andReturn(new Task([
            'id' => 'task-reopen',
            'status' => Task::STATUS_PENDING,
        ]));
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $notifications->shouldNotReceive('notifyUsersByRoles');
        $workflowNotifications = Mockery::mock(WorkflowNotificationSettingsServiceInterface::class);
        $workflowNotifications->shouldNotReceive('rolesForEvent');
        $workflowNotifications->shouldNotReceive('messageTemplateForEvent');

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
            $accountableOfficers,
            $tasks,
            $notifications,
            $workflowNotifications,
        );

        $reopened = $service->reopenInspection('user-1', 'air-reopen', 'Correction needed');

        $this->assertSame(AirStatuses::SUBMITTED, $reopened->status);
        $this->assertDatabaseHas('airs', [
            'id' => 'air-reopen',
            'status' => AirStatuses::SUBMITTED,
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('primary_department_id')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id')->nullable();
            $table->string('item_name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->nullable();
            $table->string('item_identification', 255)->nullable();
            $table->string('major_sub_account_group', 255)->nullable();
            $table->string('tracking_type', 50)->nullable();
            $table->boolean('requires_serial')->default(false);
            $table->boolean('is_semi_expendable')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('from_unit', 50);
            $table->unsignedInteger('multiplier')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('airs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_air_id')->nullable();
            $table->unsignedInteger('continuation_no')->default(1);
            $table->string('po_number', 255);
            $table->date('po_date')->nullable();
            $table->string('air_number', 255)->nullable();
            $table->date('air_date')->nullable();
            $table->string('invoice_number', 255)->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('supplier_name', 255)->nullable();
            $table->uuid('requesting_department_id')->nullable();
            $table->string('requesting_department_name_snapshot', 255)->nullable();
            $table->string('requesting_department_code_snapshot', 100)->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('fund', 255)->nullable();
            $table->string('status', 50)->default('draft');
            $table->date('date_received')->nullable();
            $table->string('received_completeness', 50)->nullable();
            $table->text('received_notes')->nullable();
            $table->date('date_inspected')->nullable();
            $table->boolean('inspection_verified')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->string('inspected_by_name', 255)->nullable();
            $table->string('accepted_by_name', 255)->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->string('created_by_name_snapshot', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_id');
            $table->uuid('item_id');
            $table->string('stock_no_snapshot', 255)->nullable();
            $table->string('item_name_snapshot', 255)->nullable();
            $table->text('description_snapshot')->nullable();
            $table->string('unit_snapshot', 50)->nullable();
            $table->decimal('acquisition_cost', 12, 2)->nullable();
            $table->unsignedInteger('qty_ordered')->nullable();
            $table->unsignedInteger('qty_delivered')->nullable();
            $table->unsignedInteger('qty_accepted')->nullable();
            $table->string('tracking_type_snapshot', 50)->nullable();
            $table->boolean('requires_serial_snapshot')->default(false);
            $table->boolean('is_semi_expendable_snapshot')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('air_item_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->uuid('inventory_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_unit_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->timestamps();
        });
    }
}
