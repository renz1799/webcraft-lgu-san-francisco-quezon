<?php

namespace App\Services\AuditLogs;

use App\Builders\AuditLogs\AuditLogPrintReportBuilder;
use App\Data\AuditLogs\AuditLogPrintData;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;
use App\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use Illuminate\Support\Str;

class AuditLogPrintService implements AuditLogPrintServiceInterface
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly AuditLogPrintReportBuilder $reportBuilder,
        private readonly PdfGeneratorInterface $pdfGenerator,
    ) {
    }

    public function buildReport(array $filters): AuditLogPrintData
    {
        $logs = $this->auditLogRepository->findForPrint($filters);

        return $this->reportBuilder->build($logs, $filters);
    }

    public function generatePdf(array $filters): string
    {
        $report = $this->buildReport($filters);

        $filename = 'audit-log-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'audit-logs.print.pdf',
            data: ['report' => $report],
            outputPath: $outputPath,
        );
    }
}