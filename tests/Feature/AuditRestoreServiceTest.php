<?php

namespace Tests\Feature;

use App\Core\Services\AuditLogs\AuditRestoreService;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class AuditRestoreServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_restore_restores_model_and_records_audit_entry(): void
    {
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')
            ->once()
            ->withArgs(function (string $action, Model $subject, array $before, array $after, array $meta): bool {
                $this->assertSame('testrestorableauditmodel.restored', $action);
                $this->assertSame('subject-1', $subject->getKey());
                $this->assertSame(['deleted_at' => '2026-03-20 10:00:00'], $before);
                $this->assertSame(['deleted_at' => null], $after);
                $this->assertSame(['source' => 'audit.restore.service'], $meta);

                return true;
            });

        $model = new TestRestorableAuditModel();
        $model->forceFill([
            'id' => 'subject-1',
            'deleted_at' => Carbon::parse('2026-03-20 10:00:00'),
        ]);

        $service = new AuditRestoreService($audit);

        $this->assertTrue($service->restore($model));
        $this->assertNull($model->deleted_at);
    }

    public function test_restore_returns_false_without_audit_write_when_model_restore_fails(): void
    {
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $model = new TestRestorableAuditModel();
        $model->forceFill([
            'id' => 'subject-2',
            'deleted_at' => Carbon::parse('2026-03-20 10:00:00'),
            'restore_result' => false,
        ]);

        $service = new AuditRestoreService($audit);

        $this->assertFalse($service->restore($model));
    }
}

class TestRestorableAuditModel extends Model
{
    protected $fillable = ['id', 'deleted_at', 'restore_result'];
    protected $casts = ['restore_result' => 'boolean'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function restore(): bool
    {
        if ($this->restore_result === false) {
            return false;
        }

        $this->deleted_at = null;

        return true;
    }
}
