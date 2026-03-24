<?php

namespace App\Modules\GSO\Http\Controllers\RIS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\RIS\RisPrintServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RisPrintController extends Controller
{
    public function __construct(
        private readonly RisPrintServiceInterface $risPrints,
    ) {
    }

    public function print(Request $request, Ris $ris): View
    {
        return view('gso::ris.print', $this->risPrints->getPrintData((string) $ris->id));
    }
}
