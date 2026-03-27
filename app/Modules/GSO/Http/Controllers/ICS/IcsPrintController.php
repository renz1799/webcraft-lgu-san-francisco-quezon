<?php

namespace App\Modules\GSO\Http\Controllers\ICS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ICS\PrintIcsRequest;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Services\Contracts\ICS\IcsPrintServiceInterface;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IcsPrintController extends Controller
{
    public function __construct(
        private readonly IcsPrintServiceInterface $printer,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view ICS|modify ICS');
    }

    public function print(PrintIcsRequest $request, Ics $ics): View
    {
        $payload = $this->printer->buildReport(
            icsId: (string) $ics->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return view('gso::ics.print.index', [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
            'filters' => $request->validated(),
        ]);
    }

    public function downloadPdf(PrintIcsRequest $request, Ics $ics): BinaryFileResponse
    {
        $path = $this->printer->generatePdf(
            icsId: (string) $ics->id,
            requestedPaper: $request->validated('paper_profile'),
            paperOverrides: $request->paperOverrides(),
        );

        return response()->download(
            file: $path,
            name: basename($path),
            headers: ['Content-Type' => 'application/pdf'],
        )->deleteFileAfterSend(true);
    }
}
