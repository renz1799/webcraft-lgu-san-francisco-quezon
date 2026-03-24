<?php

namespace App\Modules\GSO\Http\Controllers\Air;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\RIS\GenerateRisFromAirRequest;
use App\Modules\GSO\Services\Contracts\RIS\RisServiceInterface;
use Illuminate\Http\JsonResponse;

class AirRisController extends Controller
{
    public function __construct(
        private readonly RisServiceInterface $risService,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify AIR|create RIS|modify RIS');
    }

    public function generate(GenerateRisFromAirRequest $request, string $air): JsonResponse
    {
        $ris = $this->risService->generateFromAir(
            actorUserId: (string) $request->user()?->id,
            airId: $air,
            overrides: [],
        );

        return response()->json([
            'message' => 'RIS is ready.',
            'redirect_url' => route('gso.ris.edit', ['ris' => (string) $ris->id]),
            'data' => [
                'ris_id' => (string) $ris->id,
            ],
        ]);
    }
}
