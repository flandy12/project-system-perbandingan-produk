<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index(Role $role) {
      
      return view('pages.give-permission.index', compact('role'));
    }
    
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0]; // grouping by module
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.permissions', compact(
            'role',
            'permissions',
            'rolePermissions'
        ));
    }

/*************  ✨ Windsurf Command ⭐  *************/
/*******  4a43b573-441d-4ceb-8fa2-ac34c6b74346  *******/
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Permission berhasil diperbarui');
    }
}
