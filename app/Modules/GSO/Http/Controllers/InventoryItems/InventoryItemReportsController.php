<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\InventoryItems\PrintRegspiRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\PrintRpcppeRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\PrintRpcspRequest;
use App\Modules\GSO\Services\Contracts\RegspiReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcppeReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcspReportServiceInterface;
use Illuminate\View\View;

class InventoryItemReportsController extends Controller
{
    public function __construct(
        private readonly RegspiReportServiceInterface $regspiReports,
        private readonly RpcppeReportServiceInterface $rpcppeReports,
        private readonly RpcspReportServiceInterface $rpcspReports,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items');
    }

    public function printRegspi(PrintRegspiRequest $request): View
    {
        $validated = $request->validated();

        $data = $this->regspiReports->getPrintViewData(
            fundSourceId: $validated['fund_source_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            asOf: $validated['as_of'] ?? null,
            signatories: [
                'prepared_by_name' => $validated['prepared_by_name'] ?? null,
                'prepared_by_designation' => $validated['prepared_by_designation'] ?? null,
                'reviewed_by_name' => $validated['reviewed_by_name'] ?? null,
                'reviewed_by_designation' => $validated['reviewed_by_designation'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_designation' => $validated['approved_by_designation'] ?? null,
            ],
        );

        return view('gso::reports.regspi-print', $data + [
            'isPreview' => $request->boolean('preview', true),
        ]);
    }

    public function printRpcppe(PrintRpcppeRequest $request): View
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
        );

        return view('gso::reports.rpcppe-print', $data + [
            'isPreview' => $request->boolean('preview', true),
        ]);
    }

    public function printRpcsp(PrintRpcspRequest $request): View
    {
        $validated = $request->validated();

        $data = $this->rpcspReports->getPrintViewData(
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
        );

        return view('gso::reports.rpcsp-print', $data + [
            'isPreview' => $request->boolean('preview', true),
        ]);
    }
}
