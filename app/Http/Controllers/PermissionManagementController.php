<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionManagementController extends Controller
{
    /**
     * Display a list of all permissions.
     */
    public function index()
    {
        $permissions = Permission::all();
        return view('permissions.manage', compact('permissions'));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
            'page' => 'required|string|max:255', // Validate the page field
            'guard_name' => 'nullable|string|in:web,api', // Default to 'web'
        ]);

        Permission::create([
            'name' => $request->name,
            'page' => $request->page, // Store Page Value
            'guard_name' => $request->guard_name ?? 'web', // Default guard name is 'web'
        ]);

        return redirect()->route('permissions.manage')->with('success', 'Permission created successfully.');
    }

    /**
     * Delete a permission.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.manage')->with('success', 'Permission deleted successfully.');
    }
}
