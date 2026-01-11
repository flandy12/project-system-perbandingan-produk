<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = 'web'; // ganti ke sanctum jika perlu

        // ROLES
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $kepalaToko = Role::firstOrCreate(['name' => 'owner', 'guard_name' => $guard]);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => $guard]);
    }
}
