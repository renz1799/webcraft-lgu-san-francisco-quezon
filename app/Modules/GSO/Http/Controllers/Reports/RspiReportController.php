<?php

namespace App\Modules\GSO\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Reports\PrintRspiRequest;
use App\Modules\GSO\Services\Contracts\RspiReportServiceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class RspiReportController extends Controller
{
    public function __construct(
        private readonly RspiReportServiceInterface $rspiReports,
    ) {
        $this->middleware('permission:reports.rspi.view|inventory_items.view|inventory_items.update');
    }

    public function print(PrintRspiRequest $request): View
    {
        $validated = $request->validated();

        $data = $this->rspiReports->getPrintViewData(
            fundSourceId: $validated['fund_source_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
            signatories: [
                'prepared_by_name' => $validated['prepared_by_name'] ?? null,
                'prepared_by_designation' => $validated['prepared_by_designation'] ?? null,
                'reviewed_by_name' => $validated['reviewed_by_name'] ?? null,
                'reviewed_by_designation' => $validated['reviewed_by_designation'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_designation' => $validated['approved_by_designation'] ?? null,
            ],
            requestedPaper: $validated['paper_profile'] ?? null,
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::reports.rspi.print.index', [
            'report' => $data['report'],
            'paperProfile' => $data['paperProfile'],
            'availableFunds' => $data['available_funds'],
            'availableDepartments' => $data['available_departments'],
            'availableAccountableOfficers' => $data['available_accountable_officers'],
            'filters' => $validated,
        ]);
    }

    public function downloadPdf(PrintRspiRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();

        $path = $this->rspiReports->generatePdf(
            fundSourceId: $validated['fund_source_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
            signatories: [
                'prepared_by_name' => $validated['prepared_by_name'] ?? null,
                'prepared_by_designation' => $validated['prepared_by_designation'] ?? null,
                'reviewed_by_name' => $validated['reviewed_by_name'] ?? null,
                'reviewed_by_designation' => $validated['reviewed_by_designation'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_designation' => $validated['approved_by_designation'] ?? null,
            ],
            requestedPaper: $validated['paper_profile'] ?? null,
            paperOverrides: $request->paperOverrides(),
        );

        if ($request->boolean('inline')) {
            return response()->file(
                $path,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                ],
            )->deleteFileAfterSend(true);
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
