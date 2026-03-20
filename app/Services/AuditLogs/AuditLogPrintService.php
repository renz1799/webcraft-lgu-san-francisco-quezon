<?php

namespace App\Services\AuditLogs;

use App\Builders\Contracts\AuditLogs\AuditLogPrintReportBuilderInterface;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;
use App\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use Illuminate\Support\Str;

class AuditLogPrintService implements AuditLogPrintServiceInterface
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly AuditLogPrintReportBuilderInterface $reportBuilder,
        private readonly PdfGeneratorInterface $pdfGenerator,
    ) {
    }

    public function buildReport(array $filters): array
    {
        $paperProfile = $this->resolvePaperProfile($filters['paper_profile'] ?? null);

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

    protected function resolvePaperProfile(?string $requestedPaper): array
    {
        $moduleConfig = config('print.modules.audit_logs', []);

        $defaultPaper = $moduleConfig['default_paper'] ?? 'a4-portrait';
        $allowedPapers = $moduleConfig['allowed_papers'] ?? [$defaultPaper];

        $paperCode = in_array($requestedPaper, $allowedPapers, true)
            ? $requestedPaper
            : $defaultPaper;

        $paperDefaults = config("print.papers.{$paperCode}", []);
        $moduleProfile = config("print.modules.audit_logs.profiles.{$paperCode}", []);

        $resolved = array_merge($paperDefaults, $moduleProfile);

        if ($resolved === []) {
            $fallbackDefaults = config("print.papers.{$defaultPaper}", []);
            $fallbackModuleProfile = config("print.modules.audit_logs.profiles.{$defaultPaper}", []);

            $resolved = array_merge($fallbackDefaults, $fallbackModuleProfile);
        }

        return $resolved;
    }
}
