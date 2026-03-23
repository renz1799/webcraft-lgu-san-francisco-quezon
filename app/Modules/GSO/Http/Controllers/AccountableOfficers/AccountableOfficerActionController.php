<?php

namespace App\Modules\GSO\Http\Controllers\AccountableOfficers;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\AccountableOfficers\DestroyAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\ResolveAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\RestoreAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\StoreAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\UpdateAccountableOfficerRequest;
use App\Modules\GSO\Services\Contracts\AccountableOfficerServiceInterface;
use Illuminate\Http\JsonResponse;

class AccountableOfficerActionController extends Controller
{
    public function __construct(
        private readonly AccountableOfficerServiceInterface $accountableOfficers,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify Accountable Officers')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreAccountableOfficerRequest $request): JsonResponse
    {
        $accountableOfficer = $this->accountableOfficers->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Accountable officer created successfully.',
            'data' => $accountableOfficer->only(['id', 'full_name', 'designation', 'office', 'department_id', 'is_active']),
        ]);
    }

    public function resolve(ResolveAccountableOfficerRequest $request): JsonResponse
    {
        $resolved = $this->accountableOfficers->createOrResolve(
            (string) $request->user()->id,
            $request->validated(),
        );

        $message = match (true) {
            $resolved['created'] => 'Accountable officer created successfully.',
            $resolved['restored'] => 'Archived accountable officer restored and reused successfully.',
            $resolved['reused'] => 'Existing accountable officer reused successfully.',
            default => 'Accountable officer resolved successfully.',
        };

        return response()->json([
            'message' => $message,
            'data' => $resolved['officer'],
            'meta' => [
                'created' => (bool) $resolved['created'],
                'restored' => (bool) $resolved['restored'],
                'reused' => (bool) $resolved['reused'],
            ],
        ]);
    }

    public function update(UpdateAccountableOfficerRequest $request, string $accountableOfficer): JsonResponse
    {
        $updated = $this->accountableOfficers->update((string) $request->user()->id, $accountableOfficer, $request->validated());

        return response()->json([
            'message' => 'Accountable officer updated successfully.',
            'data' => $updated->only(['id', 'full_name', 'designation', 'office', 'department_id', 'is_active']),
        ]);
    }

    public function destroy(DestroyAccountableOfficerRequest $request, string $accountableOfficer): JsonResponse
    {
        $this->accountableOfficers->delete((string) $request->user()->id, $accountableOfficer);

        return response()->json([
            'message' => 'Accountable officer archived successfully.',
        ]);
    }

    public function restore(RestoreAccountableOfficerRequest $request, string $accountableOfficer): JsonResponse
    {
        $this->accountableOfficers->restore((string) $request->user()->id, $accountableOfficer);

        return response()->json([
            'message' => 'Accountable officer restored successfully.',
        ]);
    }
}
