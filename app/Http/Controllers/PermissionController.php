<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permissions\DestroyPermissionRequest;
use App\Http\Requests\Permissions\StorePermissionRequest;
use App\Models\Permission;
use App\Services\Contracts\PermissionServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionServiceInterface $permissions
    ) {
        $this->middleware(['auth', 'role_or_permission:Administrator'])
            ->only(['index', 'store', 'destroy']);
    }

    public function index(): View
    {
        $permissions = $this->permissions->paginate(30);

        return view('access.permissions.index', [
            'permissions' => $permissions,
        ]);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $permission = $this->permissions->create($request->validated());

        return redirect()
            ->route('access.permissions.index')
            ->with('success', "Permission \"{$permission->name}\" created.");
    }

    public function destroy(DestroyPermissionRequest $request, Permission $permission)
    {
        $this->permissions->delete($permission);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Permission deleted.']);
        }

        return back()->with('success', 'Permission deleted successfully.');
    }

    public function restore(string $permission): RedirectResponse
    {
        $this->permissions->restore($permission);

        return back()->with('success', 'Permission restored.');
    }

    public function forceDestroy(Permission $permission): RedirectResponse
    {
        $this->permissions->forceDelete($permission);

        return back()->with('success', 'Permission permanently deleted.');
    }
}
