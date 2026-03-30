<?php

namespace App\Modules\GSO\Http\Controllers\Departments;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Departments\DepartmentTableDataRequest;
use App\Modules\GSO\Services\Contracts\DepartmentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentServiceInterface $departments,
    ) {
        $this->middleware('permission:departments.view|departments.create|departments.update|departments.archive|departments.restore')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('gso::departments.index');
    }

    public function data(DepartmentTableDataRequest $request): JsonResponse
    {
        $payload = $this->departments->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }
}
