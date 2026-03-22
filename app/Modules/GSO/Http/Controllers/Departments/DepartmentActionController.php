<?php

namespace App\Modules\GSO\Http\Controllers\Departments;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\Departments\DestroyDepartmentRequest;
use App\Modules\GSO\Http\Requests\Departments\RestoreDepartmentRequest;
use App\Modules\GSO\Http\Requests\Departments\StoreDepartmentRequest;
use App\Modules\GSO\Http\Requests\Departments\UpdateDepartmentRequest;
use App\Modules\GSO\Services\Contracts\DepartmentServiceInterface;
use Illuminate\Http\JsonResponse;

class DepartmentActionController extends Controller
{
    public function __construct(
        private readonly DepartmentServiceInterface $departments,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify Departments')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departments->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => $department->only(['id', 'code', 'name', 'short_name', 'type', 'is_active']),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, string $department): JsonResponse
    {
        $updated = $this->departments->update((string) $request->user()->id, $department, $request->validated());

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => $updated->only(['id', 'code', 'name', 'short_name', 'type', 'is_active']),
        ]);
    }

    public function destroy(DestroyDepartmentRequest $request, string $department): JsonResponse
    {
        $this->departments->delete((string) $request->user()->id, $department);

        return response()->json([
            'message' => 'Department archived successfully.',
        ]);
    }

    public function restore(RestoreDepartmentRequest $request, string $department): JsonResponse
    {
        $this->departments->restore((string) $request->user()->id, $department);

        return response()->json([
            'message' => 'Department restored successfully.',
        ]);
    }
}
