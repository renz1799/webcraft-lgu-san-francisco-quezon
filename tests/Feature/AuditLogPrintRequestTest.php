<?php

namespace Tests\Feature;

use App\Http\Requests\AuditLogs\AuditLogPrintRequest;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AuditLogPrintRequestTest extends TestCase
{
    public function test_prepare_for_validation_maps_legacy_module_name_and_fqcn_subject_type(): void
    {
        $request = TestableAuditLogPrintRequest::create('/audit-logs/print', 'GET', [
            'module_name' => ' Access ',
            'actor_id' => ' aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa ',
            'subject_type' => Role::class,
            'search' => ' role.updated ',
        ]);

        $request->normalizeForTest();

        $this->assertSame('Access', $request->input('module'));
        $this->assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $request->input('actor_id'));
        $this->assertSame('role', $request->input('subject_type'));
        $this->assertNull($request->input('module_name'));

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rejects_non_uuid_actor_id(): void
    {
        $request = TestableAuditLogPrintRequest::create('/audit-logs/print', 'GET', [
            'actor_id' => '123',
        ]);

        $request->normalizeForTest();

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('actor_id', $validator->errors()->toArray());
    }

    public function test_validation_rejects_unknown_subject_type(): void
    {
        $request = TestableAuditLogPrintRequest::create('/audit-logs/print', 'GET', [
            'subject_type' => 'task',
        ]);

        $request->normalizeForTest();

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('subject_type', $validator->errors()->toArray());
    }
}

class TestableAuditLogPrintRequest extends AuditLogPrintRequest
{
    public function normalizeForTest(): void
    {
        $this->prepareForValidation();
    }
}
