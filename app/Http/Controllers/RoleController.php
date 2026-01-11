<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::paginate(10);

        $permissions = Permission::select('id', 'name')->get();

        return view('pages.roles.index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role berhasil dibuat',
                'data' => $role
            ], 201);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role berhasil diupdate',
                'data' => $role
            ]);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        // Prevent delete role yang sedang dipakai user
        if ($role->users()->count() > 0) {
            return back()->withErrors([
                'role' => 'Role masih digunakan oleh user'
            ]);
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role berhasil dihapus');
    }
}
