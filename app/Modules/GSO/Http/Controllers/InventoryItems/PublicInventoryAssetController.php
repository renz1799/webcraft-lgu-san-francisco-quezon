<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Services\Contracts\InventoryItemPublicAssetServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicInventoryAssetController extends Controller
{
    public function __construct(
        private readonly InventoryItemPublicAssetServiceInterface $assets,
    ) {}

    public function show(string $code): View
    {
        try {
            $payload = $this->assets->getPublicAssetPagePayload($code);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return view($payload['view'], $payload['data']);
    }

    public function preview(string $code, string $file): Response
    {
        try {
            $preview = $this->assets->streamPublicAssetFile($code, $file);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return response($preview['bytes'], 200, [
            'Content-Type' => $preview['mime'] ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($preview['name'] ?: 'photo') . '"',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
