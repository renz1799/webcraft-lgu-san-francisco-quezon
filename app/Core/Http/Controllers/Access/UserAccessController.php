<?php

namespace App\Core\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Users\AccessUsersDataRequest;
use App\Core\Http\Requests\Users\DeleteUserRequest;
use App\Core\Http\Requests\Users\ResetUserPasswordRequest;
use App\Core\Http\Requests\Users\UpdateUserModulePermissionsRequest;
use App\Core\Http\Requests\Users\UpdateUserStatusRequest;
use App\Core\Http\Requests\Users\ViewUserPermissionsRequest;
use App\Core\Models\User;
use App\Core\Services\Contracts\Access\UserAccessServiceInterface;
use App\Core\Support\AdminRouteResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class UserAccessController extends Controller
{
    public function __construct(private readonly UserAccessServiceInterface $svc) {}

    public function index(): View
    {
        return view('access.users.index');
    }

    public function data(AccessUsersDataRequest $request): JsonResponse
    {
        $payload = $this->svc->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
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
        $isActive = (bool) $request->validated()['is_active'];
        $adminRoutes = app(AdminRouteResolver::class);

        if ($adminRoutes->isModuleScoped()) {
            $this->svc->updateModuleStatus($user, $isActive);

            return response()->json(['message' => 'Module access updated successfully.'], 200);
        }

        $this->svc->updateStatus($user, $isActive);

        return response()->json(['message' => 'User status updated successfully.'], 200);
    }

    /** JSON: soft delete a user */
    public function destroy(DeleteUserRequest $request, User $user): JsonResponse
    {
        $this->svc->deleteUser($user);

        return response()->json(['message' => 'User account deleted successfully.'], 200);
    }

    public function restore(string $user): JsonResponse
    {
        $ok = $this->svc->restoreUser($user);

        if (! $ok) {
            return response()->json(['message' => 'User not found or not restorable.'], 404);
        }

        return response()->json(['message' => 'User restored successfully.'], 200);
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

