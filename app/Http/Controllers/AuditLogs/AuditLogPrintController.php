<?php

namespace App\Http\Controllers\AuditLogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogs\AuditLogPrintRequest;
use App\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;

class AuditLogPrintController extends Controller
{
    public function __construct(
        private readonly AuditLogPrintServiceInterface $printService,
    ) {
    }

    public function preview(AuditLogPrintRequest $request)
    {
        $filters = $request->validated();
        $report = $this->printService->buildReport($filters);

        return view('audit-logs.print.index', [
            'report' => $report,
            'filters' => $filters,
        ]);
    }

    public function downloadPdf(AuditLogPrintRequest $request)
    {
        $path = $this->printService->generatePdf($request->validated());

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
    }
}