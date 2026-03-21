<?php

namespace Tests\Feature;

use App\Core\Http\Requests\AuditLogs\AuditLogsDataRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AuditLogsDataRequestTest extends TestCase
{
    public function test_prepare_for_validation_trims_module_filter_and_validated_adds_defaults(): void
    {
        $request = TestableAuditLogsDataRequest::create('/audit-logs/data', 'GET', [
            'module' => ' Access ',
            'search' => ' role.updated ',
        ]);

        $request->normalizeForTest();

        $this->assertSame('Access', $request->input('module'));
        $this->assertSame('role.updated', $request->input('search'));

        $validator = Validator::make($request->all(), $request->rules());
        $this->assertFalse($validator->fails());

        $request->setContainer($this->app);
        $request->setValidator($validator);

        $validated = $request->validated();

        $this->assertSame(1, $validated['page']);
        $this->assertSame(15, $validated['size']);
        $this->assertSame('Access', $validated['module']);
    }

    public function test_validation_rejects_invalid_subject_type(): void
    {
        $request = TestableAuditLogsDataRequest::create('/audit-logs/data', 'GET', [
            'subject_type' => 'task',
        ]);

        $request->normalizeForTest();

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('subject_type', $validator->errors()->toArray());
    }
}

class TestableAuditLogsDataRequest extends AuditLogsDataRequest
{
    public function normalizeForTest(): void
    {
        $this->prepareForValidation();
    }
}
