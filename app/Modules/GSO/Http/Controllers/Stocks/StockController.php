<?php

namespace App\Modules\GSO\Http\Controllers\Stocks;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Stocks\AdjustStockRequest;
use App\Modules\GSO\Http\Requests\Stocks\PrintStockCardRequest;
use App\Modules\GSO\Http\Requests\Stocks\ShowStockLedgerRequest;
use App\Modules\GSO\Http\Requests\Stocks\StockTableDataRequest;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Services\Contracts\StockServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        private readonly StockServiceInterface $stocks,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Stocks|modify Stocks')
            ->only(['index', 'data', 'ledger', 'printCard']);

        $this->middleware('role_or_permission:Administrator|admin|modify Stocks')
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
