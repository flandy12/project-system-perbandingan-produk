<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // PRODUCT
            'product.view',
            'product.create',
            'product.update',
            'product.delete',

            // USER
            'user.view',
            'user.create',
            'user.update',
            'user.delete',

            // ROLE & PERMISSION
            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            'permission.view',
            'permission.create',
            'permission.update',
            'permission.delete',

            'report.view',
            'report.download',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web', // ganti 'sanctum' jika pakai sanctum
            ]);
        }
    }
}
