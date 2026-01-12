<?php

namespace App\Http\Controllers;

use App\Http\Requests\Logs\LoginLogsDataRequest;
use App\Services\Contracts\LoginLogServiceInterface;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class LoginLogController extends Controller
{
    public function __construct(
        private readonly LoginLogServiceInterface $logs
    ) {
        $this->middleware(['auth', 'role_or_permission:Administrator']);
    }

    /** Page */
    public function index(): View
    {
        return view('logs.index');
    }


    public function data(LoginLogsDataRequest $request): JsonResponse
    {
        \Log::info('LoginLogController@data HIT', [
            'user_id' => optional($request->user())->id,
            'query'   => $request->query(),
            'all'     => $request->all(),
        ]);

        $payload = $this->logs->datatable($request->validated());

        \Log::info('LoginLogController@data RESPONSE', [
            'returned_rows' => count($payload['data'] ?? []),
            'last_page'     => $payload['last_page'] ?? null,
            'total'         => $payload['total'] ?? null,
            // keep these if you still include them:
            'recordsTotal'    => $payload['recordsTotal'] ?? null,
            'recordsFiltered' => $payload['recordsFiltered'] ?? null,
        ]);

        return response()->json([
            'data'      => $payload['data'] ?? [],
            'last_page' => $payload['last_page'] ?? 1,
            'total'     => $payload['total'] ?? 0,
        ]);
    }


}
