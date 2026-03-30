<?php

namespace App\Modules\GSO\Http\Controllers\Stocks;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Stocks\AdjustStockRequest;
use App\Modules\GSO\Http\Requests\Stocks\PrintRpciRequest;
use App\Modules\GSO\Http\Requests\Stocks\PrintSsmiRequest;
use App\Modules\GSO\Http\Requests\Stocks\PrintStockCardRequest;
use App\Modules\GSO\Http\Requests\Stocks\ShowStockLedgerRequest;
use App\Modules\GSO\Http\Requests\Stocks\StockTableDataRequest;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Services\Contracts\StockServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StockController extends Controller
{
    public function __construct(
        private readonly StockServiceInterface $stocks,
    ) {
        $this->middleware('permission:stocks.view|stocks.adjust|stocks.view_ledger|reports.rpci.view|reports.ssmi.view|reports.stock_cards.view')
            ->only(['index', 'data', 'ledger', 'printCard', 'printRpci', 'printSsmi']);

        $this->middleware('permission:stocks.adjust')
            ->only(['adjust']);
    }

    public function index(): View
    {
        return view('gso::stocks.index', [
            'fundSources' => FundSource::query()
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('code')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function data(StockTableDataRequest $request): JsonResponse
    {
        $payload = $this->stocks->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function ledger(ShowStockLedgerRequest $request, string $item): View
    {
        return view('gso::stocks.ledger', $this->stocks->getLedgerViewData($item, $request->validated()));
    }

    public function printCard(PrintStockCardRequest $request, string $item): View
    {
        $validated = $request->validated();

        return view('gso::stocks.card-print', $this->stocks->getCardPrintViewData(
            itemId: $item,
            fundSourceId: $validated['fund_source_id'] ?? null,
            asOf: $validated['as_of'] ?? null,
        ) + [
            'isPreview' => $request->boolean('preview', true),
        ]);
    }

    public function printRpci(PrintRpciRequest $request): View
    {
        $validated = $request->validated();

        $payload = $this->stocks->getRpciPrintViewData(
            fundSourceId: $validated['fund_source_id'] ?? null,
            asOf: $validated['as_of'] ?? null,
            inventoryType: $validated['inventory_type'] ?? null,
            prefillCount: (bool) ($validated['prefill_count'] ?? false),
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            signatories: [
                'accountable_officer_name' => $validated['accountable_officer_name'] ?? null,
                'accountable_officer_designation' => $validated['accountable_officer_designation'] ?? null,
                'date_of_assumption' => $validated['date_of_assumption'] ?? null,
                'committee_chair_name' => $validated['committee_chair_name'] ?? null,
                'committee_member_1_name' => $validated['committee_member_1_name'] ?? null,
                'committee_member_2_name' => $validated['committee_member_2_name'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_designation' => $validated['approved_by_designation'] ?? null,
                'verified_by_name' => $validated['verified_by_name'] ?? null,
                'verified_by_designation' => $validated['verified_by_designation'] ?? null,
            ],
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::reports.rpci.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'availableFunds' => $payload['available_funds'] ?? [],
            'filters' => $request->validated(),
        ]);
    }

    public function downloadRpciPdf(PrintRpciRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();

        $path = $this->stocks->generateRpciPdf(
            fundSourceId: $validated['fund_source_id'] ?? null,
            asOf: $validated['as_of'] ?? null,
            inventoryType: $validated['inventory_type'] ?? null,
            prefillCount: (bool) ($validated['prefill_count'] ?? false),
            accountableOfficerId: $validated['accountable_officer_id'] ?? null,
            signatories: [
                'accountable_officer_name' => $validated['accountable_officer_name'] ?? null,
                'accountable_officer_designation' => $validated['accountable_officer_designation'] ?? null,
                'date_of_assumption' => $validated['date_of_assumption'] ?? null,
                'committee_chair_name' => $validated['committee_chair_name'] ?? null,
                'committee_member_1_name' => $validated['committee_member_1_name'] ?? null,
                'committee_member_2_name' => $validated['committee_member_2_name'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'approved_by_designation' => $validated['approved_by_designation'] ?? null,
                'verified_by_name' => $validated['verified_by_name'] ?? null,
                'verified_by_designation' => $validated['verified_by_designation'] ?? null,
            ],
            requestedPaper: $request->validated('paper_profile'),
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

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf'],
        )->deleteFileAfterSend(true);
    }

    public function printSsmi(PrintSsmiRequest $request): View
    {
        $validated = $request->validated();

        $data = $this->stocks->getSsmiPrintViewData(
            fundSourceId: $validated['fund_source_id'] ?? null,
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
            signatories: [
                'prepared_by_name' => $validated['prepared_by_name'] ?? null,
                'prepared_by_designation' => $validated['prepared_by_designation'] ?? null,
                'prepared_by_date' => $validated['prepared_by_date'] ?? null,
                'certified_by_name' => $validated['certified_by_name'] ?? null,
                'certified_by_designation' => $validated['certified_by_designation'] ?? null,
                'certified_by_date' => $validated['certified_by_date'] ?? null,
            ],
            requestedPaper: $validated['paper_profile'] ?? null,
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::reports.ssmi.print.index', [
            'report' => $data['report'],
            'paperProfile' => $data['paperProfile'],
            'availableFunds' => $data['available_funds'],
            'filters' => $validated,
        ]);
    }

    public function downloadSsmiPdf(PrintSsmiRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();

        $path = $this->stocks->generateSsmiPdf(
            fundSourceId: $validated['fund_source_id'] ?? null,
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
            signatories: [
                'prepared_by_name' => $validated['prepared_by_name'] ?? null,
                'prepared_by_designation' => $validated['prepared_by_designation'] ?? null,
                'prepared_by_date' => $validated['prepared_by_date'] ?? null,
                'certified_by_name' => $validated['certified_by_name'] ?? null,
                'certified_by_designation' => $validated['certified_by_designation'] ?? null,
                'certified_by_date' => $validated['certified_by_date'] ?? null,
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

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf'],
        )->deleteFileAfterSend(true);
    }

    public function adjust(AdjustStockRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $actorName = trim((string) ($user?->name ?? $user?->username ?? $user?->email ?? 'System User'));

        return response()->json($this->stocks->adjustManual(
            actorUserId: (string) $user?->id,
            actorName: $actorName,
            itemId: (string) $validated['item_id'],
            type: (string) $validated['type'],
            qty: (int) $validated['qty'],
            fundSourceId: $validated['fund_source_id'] ?? null,
            remarks: $validated['remarks'] ?? null,
        ));
    }
}
