<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Air\PrintAirRequest;
use App\Modules\GSO\Services\Contracts\AirPrintServiceInterface;
use Illuminate\View\View;

class AirPrintController extends Controller
{
    public function __construct(
        private readonly AirPrintServiceInterface $prints,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view AIR|modify AIR');
    }

    public function print(PrintAirRequest $request, string $air): View
    {
        return view('gso::air.print', $this->prints->getPrintViewData($air) + [
            'isPreview' => $request->boolean('preview', true),
        ]);
    }
}
