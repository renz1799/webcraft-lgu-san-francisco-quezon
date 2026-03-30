<?php

namespace App\Core\Http\Controllers\AccountablePersons;

use App\Core\Http\Requests\AccountablePersons\DestroyAccountablePersonRequest;
use App\Core\Http\Requests\AccountablePersons\RestoreAccountablePersonRequest;
use App\Core\Http\Requests\AccountablePersons\StoreAccountablePersonRequest;
use App\Core\Http\Requests\AccountablePersons\UpdateAccountablePersonRequest;
use App\Core\Services\Contracts\AccountablePersons\AccountablePersonServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AccountablePersonActionController extends Controller
{
    public function __construct(
        private readonly AccountablePersonServiceInterface $accountablePersons,
    ) {
        $this->middleware('permission:accountable_persons.create|accountable_persons.update|accountable_persons.archive|accountable_persons.restore')
            ->only(['store', 'update', 'destroy', 'restore']);
    }

    public function store(StoreAccountablePersonRequest $request): JsonResponse
    {
        $accountablePerson = $this->accountablePersons->create((string) $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Accountable person created successfully.',
            'data' => $accountablePerson->only(['id', 'full_name', 'designation', 'office', 'department_id', 'is_active']),
        ]);
    }

    public function update(UpdateAccountablePersonRequest $request, string $accountablePerson): JsonResponse
    {
        $updated = $this->accountablePersons->update((string) $request->user()->id, $accountablePerson, $request->validated());

        return response()->json([
            'message' => 'Accountable person updated successfully.',
            'data' => $updated->only(['id', 'full_name', 'designation', 'office', 'department_id', 'is_active']),
        ]);
    }

    public function destroy(DestroyAccountablePersonRequest $request, string $accountablePerson): JsonResponse
    {
        $this->accountablePersons->delete((string) $request->user()->id, $accountablePerson);

        return response()->json([
            'message' => 'Accountable person archived successfully.',
        ]);
    }

    public function restore(RestoreAccountablePersonRequest $request, string $accountablePerson): JsonResponse
    {
        $this->accountablePersons->restore((string) $request->user()->id, $accountablePerson);

        return response()->json([
            'message' => 'Accountable person restored successfully.',
        ]);
    }
}
