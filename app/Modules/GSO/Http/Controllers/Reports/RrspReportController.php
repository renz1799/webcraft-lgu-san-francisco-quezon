<?php

namespace App\Modules\GSO\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Reports\PrintRrspRequest;
use App\Modules\GSO\Services\Contracts\RrspReportServiceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class RrspReportController extends Controller
{
    public function __construct(
        private readonly RrspReportServiceInterface $rrspReports,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items');
    }

    public function print(PrintRrspRequest $request): View
    {
        $validated = $request->validated();

        $data = $this->rrspReports->getPrintViewData(
            fundSourceId: $validated['fund_source_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            returnDate: $validated['return_date'] ?? null,
            signatories: [
                'returned_by_name' => $validated['returned_by_name'] ?? null,
                'returned_by_designation' => $validated['returned_by_designation'] ?? null,
                'received_by_name' => $validated['received_by_name'] ?? null,
                'received_by_designation' => $validated['received_by_designation'] ?? null,
                'noted_by_name' => $validated['noted_by_name'] ?? null,
                'noted_by_designation' => $validated['noted_by_designation'] ?? null,
            ],
            requestedPaper: $validated['paper_profile'] ?? null,
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::reports.rrsp.print.index', [
            'report' => $data['report'],
            'paperProfile' => $data['paperProfile'],
            'availableFunds' => $data['available_funds'],
            'availableDepartments' => $data['available_departments'],
            'availableAccountableOfficers' => $data['available_accountable_officers'],
            'filters' => $validated,
        ]);
    }

    public function downloadPdf(PrintRrspRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();

        $path = $this->rrspReports->generatePdf(
            fundSourceId: $validated['fund_source_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            returnDate: $validated['return_date'] ?? null,
            signatories: [
                'returned_by_name' => $validated['returned_by_name'] ?? null,
                'returned_by_designation' => $validated['returned_by_designation'] ?? null,
                'received_by_name' => $validated['received_by_name'] ?? null,
                'received_by_designation' => $validated['received_by_designation'] ?? null,
                'noted_by_name' => $validated['noted_by_name'] ?? null,
                'noted_by_designation' => $validated['noted_by_designation'] ?? null,
            ],
            requestedPaper: $validated['paper_profile'] ?? null,
            paperOverrides: $request->paperOverrides(),
        );

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
