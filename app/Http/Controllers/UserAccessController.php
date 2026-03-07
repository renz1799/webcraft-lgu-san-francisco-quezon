<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Contracts\UserAccessServiceInterface;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\ResetUserPasswordRequest;
use App\Http\Requests\Users\UpdateUserModulePermissionsRequest;
use App\Http\Requests\Users\UpdateUserStatusRequest;
use App\Http\Requests\Users\UserPermissionsTableRequest;
use App\Http\Requests\Users\ViewUserPermissionsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class UserAccessController extends Controller
{
    public function __construct(private readonly UserAccessServiceInterface $svc) {}

    /** Page: list users + grouped permissions */
    public function index(): View
    {
        $q = request()->string('q')->toString();
        $data = $this->svc->indexData($q ?: null);

        return view('access.users.index', $data);
    }

    public function data(UserPermissionsTableRequest $request): JsonResponse
    {
        $f = $request->filters();

        $page = $f['page'];
        $size = $f['size'];
        $q = $f['q'] ?: null;

        $p = $this->svc->paginateForPermissionsTable($q, $page, $size);

        $rows = $p->getCollection()->map(function (User $u) {
            return [
                'id' => $u->id,
                'username' => $u->username,
                'email' => $u->email,
                'role' => optional($u->roles->first())->name ?? 'No Role Assigned',
                'created' => optional($u->created_at)?->format('d M Y'),
                'is_active' => (bool) $u->is_active,

                'edit_url' => route('access.users.edit', $u),
                'status_url' => route('access.users.status.update', $u),
                'delete_url' => route('access.users.destroy', $u),
            ];
        })->values();

        return response()->json([
            'data' => $rows,
            'total' => $p->total(),
            'last_page' => $p->lastPage(),
        ]);
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

    public function restore(User $user): JsonResponse
    {
        $this->svc->restoreUser($user);

        return response()->json(['message' => 'User restored successfully.'], 200);
    }

    public function forceDelete(User $user): JsonResponse
    {
        $this->svc->forceDeleteUser($user);

        return response()->json(['message' => 'User permanently deleted.'], 200);
    }

    public function edit(User $user): View
    {
        $data = $this->svc->getEditData($user);

        return view('access.users.edit', $data);
    }

    public function updateModulePermissions(UpdateUserModulePermissionsRequest $request, User $user): JsonResponse
    {
        $payload = $request->validated();

        $count = $this->svc->syncNestedPermissions(
            $user,
            $payload['permissions'] ?? [],
            $payload['role'] ?? null
        );

        return response()->json(['message' => 'Permissions updated.', 'count' => $count], 200);
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): JsonResponse
    {
        $temp = $this->svc->resetPasswordToTemporary($user);

        return response()->json([
            'message' => 'Temporary password generated.',
            'temporary_password' => $temp,
        ], 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }
}
