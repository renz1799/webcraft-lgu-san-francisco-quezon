<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Contracts\UserAccessServiceInterface;
use App\Http\Requests\Users\UserAccessRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\UpdateUserStatusRequest;
use App\Http\Requests\Users\UpdateUserModulePermissionsRequest;
use App\Http\Requests\Users\ResetUserPasswordRequest;
use App\Http\Requests\Users\ViewUserPermissionsRequest ;
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
    public function show(ViewUserPermissionsRequest $request, User $user): JsonResponse
    {
        return response()->json($this->svc->getUserPermissions($user));
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

    public function restore(User $user)
    {
        $this->svc->restoreUser($user);
        return response()->json(['message' => 'User restored successfully.'], 200);
    }

    public function forceDelete(User $user)
    {
        $this->svc->forceDeleteUser($user);
        return response()->json(['message' => 'User permanently deleted.'], 200);
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

        /* HIGH-SIGNAL: what did the UI actually send?
        Log::info('perm.update: incoming payload', [
            'user_id'        => $user->id,
            'role_provided'  => array_key_exists('role', $payload) ? ($payload['role'] ?? null) : '__absent__',
            'pages'          => array_keys($payload['permissions'] ?? []),
            'permissions_ci' => collect($payload['permissions'] ?? [])->map(function ($group) {
                return collect($group)->map(function ($actions) {
                    return collect($actions)->map(fn($a) => strtolower(trim($a)))->values()->all();
                });
            }),
        ]); */

        $count = $this->svc->syncNestedPermissions(
            $user,
            $payload['permissions'] ?? [],
            $payload['role'] ?? null
        );

      /*  Log::info('perm.update: applied', [
            'user_id' => $user->id,
            'count'   => $count,
        ]); */

        return response()->json(['message' => 'Permissions updated.', 'count' => $count], 200);
    }
    
    public function resetPassword(ResetUserPasswordRequest $request, User $user): JsonResponse
    {
        $temp = $this->svc->resetPasswordToTemporary($user);

        return response()->json([
            'message'            => 'Temporary password generated.',
            'temporary_password' => $temp,
        ], 200)
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
        ->header('Pragma', 'no-cache');
    }
        
}
