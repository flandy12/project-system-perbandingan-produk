<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.users.index', [
            'users' => User::with('roles')->paginate(10),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'role'      => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'is_active' => $validated['is_active'],
        ]);

        // pastikan role ada
        $role = Role::firstOrCreate([
            'name' => $validated['role'],
            'guard_name' => 'web',
        ]);

        $user->assignRole($role);

        // ===== RESPONSE =====
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User berhasil disimpan',
                'data' => $user
            ], 201);
        }

        return back()->withErrors($validated)->withInput();

        return redirect()
            ->route('users.index')
            ->with('success', 'Berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'role'      => 'required|string',
            'is_active' => 'required|boolean',
            'password'  => 'nullable|min:6',
        ]);

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'is_active' => $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        // ===== RESPONSE JSON (AJAX) =====
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User berhasil diupdate',
                'data'    => $user->load('roles'),
            ], 200);
        }

        // ===== RESPONSE WEB =====
        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diupdate');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus');
    }
}
