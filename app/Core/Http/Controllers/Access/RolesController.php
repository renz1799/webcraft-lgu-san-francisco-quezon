<?php

namespace App\Core\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Roles\AccessRolesDataRequest;
use App\Core\Http\Requests\Roles\DeleteRoleRequest;
use App\Core\Http\Requests\Roles\RestoreRoleRequest;
use App\Core\Http\Requests\Roles\StoreRoleRequest;
use App\Core\Http\Requests\Roles\UpdateRoleRequest;
use App\Core\Models\Role;
use App\Core\Services\Contracts\Access\RoleServiceInterface;
use App\Core\Support\AdminRouteResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RolesController extends Controller
{
    public function __construct(private readonly RoleServiceInterface $roles)
    {
        $this->middleware(['auth', 'permission:roles.view|roles.create|roles.update|roles.archive|roles.restore|access.roles.view|access.roles.manage']);
    }

    public function index(): View
    {
        return view('access.roles.index', $this->roles->indexData());
    }

    public function data(AccessRolesDataRequest $request): JsonResponse
    {
        $payload = $this->roles->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function restore(RestoreRoleRequest $request, string $role): JsonResponse
    {
        $ok = $this->roles->restoreRole($role);

        if (! $ok) {
            return response()->json(['message' => 'Role not found or not restorable.'], 404);
        }

        return response()->json(['message' => 'Role restored successfully.'], 200);
    }

    public function create(): View
    {
        return view('access.roles.index', $this->roles->indexData());
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());

        return redirect()
            ->to(app(AdminRouteResolver::class)->route('access.roles.index'))
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        return view('access.roles.index', $this->roles->indexData());
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse|JsonResponse
    {
        $this->roles->update($role, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Role updated successfully.'], 200);
        }

        return redirect()
            ->to(app(AdminRouteResolver::class)->route('access.roles.index'))
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(DeleteRoleRequest $request, Role $role): RedirectResponse|JsonResponse
    {
        $this->roles->delete($role);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Role deleted successfully.'], 200);
        }

        return redirect()
            ->to(app(AdminRouteResolver::class)->route('access.roles.index'))
            ->with('success', 'Role deleted successfully.');
    }
}
