<?php

namespace App\Core\Services\AuditLogs;

use App\Core\Builders\Contracts\AuditLogs\AuditLogPrintReportBuilderInterface;
use App\Core\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use Illuminate\Support\Str;

class AuditLogPrintService implements AuditLogPrintServiceInterface
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly AuditLogPrintReportBuilderInterface $reportBuilder,
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly PrintConfigLoaderInterface $printConfigLoader,
    ) {
    }

    public function buildReport(array $filters): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('audit_logs', $filters['paper_profile'] ?? null);

        $logs = $this->auditLogRepository->findForPrint($filters);
        $report = $this->reportBuilder->build($logs, $filters);

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(array $filters): string
    {
        $payload = $this->buildReport($filters);

        $filename = 'audit-log-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'audit-logs.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }
}
