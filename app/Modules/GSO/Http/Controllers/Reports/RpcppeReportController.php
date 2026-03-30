<?php

namespace App\Modules\GSO\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Reports\PrintRpcppeRequest;
use App\Modules\GSO\Services\Contracts\RpcppeReportServiceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class RpcppeReportController extends Controller
{
    public function __construct(
        private readonly RpcppeReportServiceInterface $rpcppeReports,
    ) {
        $this->middleware('permission:reports.rpcppe.view|inventory_items.view|inventory_items.update');
    }

    public function print(PrintRpcppeRequest $request): View
    {
        $validated = $request->validated();

        $data = $this->rpcppeReports->getPrintViewData(
            fundSourceId: $validated['fund_source_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            asOf: $validated['as_of'] ?? null,
            prefillCount: (bool) ($validated['prefill_count'] ?? false),
            signatories: [
                'accountable_officer_name' => $validated['accountable_officer_name'] ?? null,
                'accountable_officer_designation' => $validated['accountable_officer_designation'] ?? null,
                'committee_chair_name' => $validated['committee_chair_name'] ?? null,
                'committee_member_1_name' => $validated['committee_member_1_name'] ?? null,
                'committee_member_2_name' => $validated['committee_member_2_name'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_designation' => $validated['approved_by_designation'] ?? null,
                'verified_by_name' => $validated['verified_by_name'] ?? null,
                'verified_by_designation' => $validated['verified_by_designation'] ?? null,
            ],
            requestedPaper: $validated['paper_profile'] ?? null,
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::reports.rpcppe.print.index', [
            'report' => $data['report'],
            'paperProfile' => $data['paperProfile'],
            'availableFunds' => $data['available_funds'],
            'availableDepartments' => $data['available_departments'],
            'availableAccountableOfficers' => $data['available_accountable_officers'],
            'filters' => $validated,
        ]);
    }

    public function downloadPdf(PrintRpcppeRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();

        $path = $this->rpcppeReports->generatePdf(
            fundSourceId: $validated['fund_source_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            asOf: $validated['as_of'] ?? null,
            prefillCount: (bool) ($validated['prefill_count'] ?? false),
            signatories: [
                'accountable_officer_name' => $validated['accountable_officer_name'] ?? null,
                'accountable_officer_designation' => $validated['accountable_officer_designation'] ?? null,
                'committee_chair_name' => $validated['committee_chair_name'] ?? null,
                'committee_member_1_name' => $validated['committee_member_1_name'] ?? null,
                'committee_member_2_name' => $validated['committee_member_2_name'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_designation' => $validated['approved_by_designation'] ?? null,
                'verified_by_name' => $validated['verified_by_name'] ?? null,
                'verified_by_designation' => $validated['verified_by_designation'] ?? null,
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
