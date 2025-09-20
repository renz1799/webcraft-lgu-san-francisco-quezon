<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Contracts\UserPermissionsServiceInterface;
use App\Http\Requests\Permissions\UpdateUserRolePermissionsRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\UpdateUserStatusRequest;

class PermissionsController extends Controller
{
    public function __construct(private readonly UserPermissionsServiceInterface $svc) {}

    /** Display list of users (excluding Administrators) + grouped permissions. */
    public function index()
    {
        $data = $this->svc->indexData();
        return view('permissions.permissions', $data);
    }

    /** Fetch a user's permissions/roles summary (JSON). */
    public function getUserPermissions(User $user)
    {
        return response()->json($this->svc->getUserPermissions($user));
    }

    /** Update a user's role and/or custom permissions (JSON). */
    public function update(UpdateUserRolePermissionsRequest $request, User $user)
    {
        $payload = $request->validated();
        $this->svc->updateUserRoleAndPermissions(
            $user,
            $payload['role'] ?? null,
            $payload['permissions'] ?? []
        );

        return response()->json(['message' => 'User role and permissions updated successfully.'], 200);
    }

    /** Delete a user (JSON). */
    public function deleteUser(DeleteUserRequest $request, User $user)
    {
        $this->svc->deleteUser($user);
        return response()->json(['message' => 'User account deleted successfully.'], 200);
    }

    /** Update a user's active status (JSON). */
    public function updateStatus(UpdateUserStatusRequest $request, User $user)
    {
        $this->svc->updateStatus($user, (bool) $request->validated()['is_active']);
        return response()->json(['message' => 'User status updated successfully.'], 200);
    }
}
