<?php

namespace Tests\Feature;

use App\Builders\AuditLogs\AuditLogPrintReportBuilder;
use App\Data\AuditLogs\AuditLogPrintData;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\AuditLogs\AuditLogPrintService;
use App\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use Mockery;
use Tests\TestCase;

class AuditLogPrintServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_build_report_uses_repository_builder_and_default_paper_profile_when_requested_paper_is_not_allowed(): void
    {
        config()->set('print.modules.audit_logs.default_paper', 'a4-portrait');
        config()->set('print.modules.audit_logs.allowed_papers', ['a4-portrait']);
        config()->set('print.papers.a4-portrait', ['label' => 'A4 Portrait']);
        config()->set('print.modules.audit_logs.profiles.a4-portrait', ['orientation' => 'portrait']);

        $filters = [
            'module' => 'access',
            'paper_profile' => 'letter-portrait',
        ];

        $logs = collect([(object) ['id' => 'log-1']]);
        $report = new AuditLogPrintData(
            title: 'Audit Log Report',
            filters: $filters,
            rows: [],
            total: 0,
            generatedAt: '2026-03-20 01:00 PM',
        );

        $repository = Mockery::mock(AuditLogRepositoryInterface::class);
        $builder = Mockery::mock(AuditLogPrintReportBuilder::class);
        $pdf = Mockery::mock(PdfGeneratorInterface::class);

        $repository->shouldReceive('findForPrint')
            ->once()
            ->with($filters)
            ->andReturn($logs);

        $builder->shouldReceive('build')
            ->once()
            ->with($logs, $filters)
            ->andReturn($report);

        $pdf->shouldNotReceive('generateFromView');

        $service = new AuditLogPrintService($repository, $builder, $pdf);
        $payload = $service->buildReport($filters);

        $this->assertSame($report, $payload['report']);
        $this->assertSame('A4 Portrait', $payload['paperProfile']['label']);
        $this->assertSame('portrait', $payload['paperProfile']['orientation']);
    }

    public function test_generate_pdf_passes_rendered_report_and_resolved_paper_profile_to_pdf_generator(): void
    {
        config()->set('print.modules.audit_logs.default_paper', 'a4-portrait');
        config()->set('print.modules.audit_logs.allowed_papers', ['a4-portrait', 'letter-portrait']);
        config()->set('print.papers.letter-portrait', ['label' => 'Letter Portrait']);
        config()->set('print.modules.audit_logs.profiles.letter-portrait', ['orientation' => 'portrait']);

        $filters = [
            'module' => 'access',
            'paper_profile' => 'letter-portrait',
        ];

        $logs = collect([(object) ['id' => 'log-1']]);
        $report = new AuditLogPrintData(
            title: 'Audit Log Report',
            filters: $filters,
            rows: [],
            total: 0,
            generatedAt: '2026-03-20 01:00 PM',
        );

        $repository = Mockery::mock(AuditLogRepositoryInterface::class);
        $builder = Mockery::mock(AuditLogPrintReportBuilder::class);
        $pdf = Mockery::mock(PdfGeneratorInterface::class);

        $repository->shouldReceive('findForPrint')
            ->once()
            ->with($filters)
            ->andReturn($logs);

        $builder->shouldReceive('build')
            ->once()
            ->with($logs, $filters)
            ->andReturn($report);

        $pdf->shouldReceive('generateFromView')
            ->once()
            ->withArgs(function (string $view, array $data, string $outputPath) use ($report): bool {
                $this->assertSame('audit-logs.print.pdf', $view);
                $this->assertSame($report, $data['report']);
                $this->assertSame('Letter Portrait', $data['paperProfile']['label']);
                $this->assertSame('portrait', $data['paperProfile']['orientation']);
                $this->assertStringContainsString('audit-log-report-', $outputPath);
                $this->assertStringEndsWith('.pdf', $outputPath);

                return true;
            })
            ->andReturn('C:\\tmp\\audit-log-report.pdf');

        $service = new AuditLogPrintService($repository, $builder, $pdf);

        $this->assertSame('C:\\tmp\\audit-log-report.pdf', $service->generatePdf($filters));
    }
}
