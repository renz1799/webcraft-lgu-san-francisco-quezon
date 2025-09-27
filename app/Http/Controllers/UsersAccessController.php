<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Contracts\UserAccessServiceInterface;
use App\Http\Requests\Users\UserAccessRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\UpdateUserStatusRequest;
use App\Http\Requests\Users\UpdateUserModulePermissionsRequest;
use App\Http\Requests\Users\ResetUserPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

class UsersAccessController extends Controller
{
    public function __construct(private readonly UserAccessServiceInterface $svc) {}

    /** Page: list users + grouped permissions */
    public function index(): View
    {
        $data = $this->svc->indexData();
        return view('permissions.permissions', $data);
    }

    /** JSON: a single user's role/permissions */
    public function show(User $user): JsonResponse
    {
        return response()->json($this->svc->getUserPermissions($user));
    }

    /** JSON: update a user's role and/or custom permissions */
    public function update(UserAccessRequest $request, User $user): JsonResponse
    {
        $payload = $request->validated();
        $this->svc->updateUserRoleAndPermissions(
            $user,
            $payload['role'] ?? null,
            $payload['permissions'] ?? []
        );

        return response()->json(['message' => 'User role and permissions updated successfully.'], 200);
    }

    /** JSON: toggle active status */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        $this->svc->updateStatus($user, (bool) $request->validated()['is_active']);
        return response()->json(['message' => 'User status updated successfully.'], 200);
    }

    /** JSON: delete a user */
    public function destroy(DeleteUserRequest $request, User $user): JsonResponse
    {
        $this->svc->deleteUser($user);
        return response()->json(['message' => 'User account deleted successfully.'], 200);
    }

    public function restore(User $user, UserAccessService $service)
    {
        $service->restoreUser($user);
        return back()->with('success', 'User restored.');
    }

    public function forceDelete(User $user, UserAccessService $service)
    {
        $service->forceDeleteUser($user);
        return back()->with('success', 'User permanently deleted.');
    }

    public function edit(User $user)
    {
        // Render your existing "set-user-role-permissions" page with prepared data
        $data = $this->svc->getEditData($user);
        return view('permissions.set-user-role-permissions', $data);
    }

    public function updateModulePermissions(UpdateUserModulePermissionsRequest $request, User $user)
    {
        $payload = $request->validated();

        // HIGH-SIGNAL: what did the UI actually send?
        Log::info('perm.update: incoming payload', [
            'user_id'        => $user->id,
            'role_provided'  => array_key_exists('role', $payload) ? ($payload['role'] ?? null) : '__absent__',
            'pages'          => array_keys($payload['permissions'] ?? []),
            'permissions_ci' => collect($payload['permissions'] ?? [])->map(function ($group) {
                return collect($group)->map(function ($actions) {
                    return collect($actions)->map(fn($a) => strtolower(trim($a)))->values()->all();
                });
            }),
        ]);

        $count = $this->svc->syncNestedPermissions(
            $user,
            $payload['permissions'] ?? [],
            $payload['role'] ?? null
        );

        Log::info('perm.update: applied', [
            'user_id' => $user->id,
            'count'   => $count,
        ]);

        return response()->json(['message' => 'Permissions updated.', 'count' => $count], 200);
    }
    
    public function resetPassword(ResetUserPasswordRequest $request, User $user): JsonResponse
    {
        $temp = $this->svc->resetPasswordToTemporary($user);

        return response()->json([
            'message'            => 'Temporary password generated.',
            'temporary_password' => $temp, // displayed to admin via SweetAlert
        ], 200);
    }
        
}
