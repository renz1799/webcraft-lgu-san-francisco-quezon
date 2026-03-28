<?php

namespace App\Modules\GSO\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Reports\PrintPropertyCardsRequest;
use App\Modules\GSO\Services\Contracts\PropertyCardsReportServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PropertyCardsReportController extends Controller
{
    public function __construct(
        private readonly PropertyCardsReportServiceInterface $propertyCards,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items');
    }

    public function print(PrintPropertyCardsRequest $request): View
    {
        $validated = $request->validated();

        $data = $this->propertyCards->getPrintViewData(
            filters: $validated,
            requestedPaper: $validated['paper_profile'] ?? null,
        );

        return view('gso::reports.property-cards.print.index', [
            'report' => $data['report'],
            'paperProfile' => $data['paperProfile'],
            'availableFunds' => $data['available_funds'],
            'availableDepartments' => $data['available_departments'],
            'availableItems' => $data['available_items'],
            'classificationOptions' => $data['classification_options'],
            'custodyOptions' => $data['custody_options'],
            'inventoryStatusOptions' => $data['inventory_status_options'],
            'recordStatusOptions' => $data['record_status_options'],
            'filters' => $validated,
        ]);
    }

    public function downloadPdf(PrintPropertyCardsRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();

        $path = $this->propertyCards->generatePdf(
            filters: $validated,
            requestedPaper: $validated['paper_profile'] ?? null,
        );

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
