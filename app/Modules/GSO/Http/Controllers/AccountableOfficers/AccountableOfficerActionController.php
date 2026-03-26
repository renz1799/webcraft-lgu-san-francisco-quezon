<?php

namespace App\Modules\GSO\Http\Controllers\AccountableOfficers;

use App\Core\Services\Contracts\AccountablePersons\AccountablePersonServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\AccountableOfficers\DestroyAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\ResolveAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\RestoreAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\StoreAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\AccountableOfficers\UpdateAccountableOfficerRequest;
use Illuminate\Http\JsonResponse;

class AccountableOfficerActionController extends Controller
{
    public function __construct(
        private readonly AccountablePersonServiceInterface $accountablePersons,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|modify Accountable Officers')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreAccountableOfficerRequest $request): JsonResponse
    {
        $accountablePerson = $this->accountablePersons->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Accountable person created successfully.',
            'data' => $accountablePerson->only(['id', 'full_name', 'designation', 'office', 'department_id', 'is_active']),
        ]);
    }

    public function resolve(ResolveAccountableOfficerRequest $request): JsonResponse
    {
        $resolved = $this->accountablePersons->createOrResolve(
            (string) $request->user()->id,
            $request->validated(),
        );

        $message = match (true) {
            $resolved['created'] => 'Accountable person created successfully.',
            $resolved['restored'] => 'Archived accountable person restored and reused successfully.',
            $resolved['reused'] => 'Existing accountable person reused successfully.',
            default => 'Accountable person resolved successfully.',
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
        $updated = $this->accountablePersons->update((string) $request->user()->id, $accountableOfficer, $request->validated());

        return response()->json([
            'message' => 'Accountable person updated successfully.',
            'data' => $updated->only(['id', 'full_name', 'designation', 'office', 'department_id', 'is_active']),
        ]);
    }

    public function destroy(DestroyAccountableOfficerRequest $request, string $accountableOfficer): JsonResponse
    {
        $this->accountablePersons->delete((string) $request->user()->id, $accountableOfficer);

        return response()->json([
            'message' => 'Accountable person archived successfully.',
        ]);
    }

    public function restore(RestoreAccountableOfficerRequest $request, string $accountableOfficer): JsonResponse
    {
        $this->accountablePersons->restore((string) $request->user()->id, $accountableOfficer);

        return response()->json([
            'message' => 'Accountable person restored successfully.',
        ]);
    }
}
