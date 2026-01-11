<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class GivePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::where('name', 'Admin')
            ->firstOrFail()
            ->givePermissionTo([
                'product.view',
                'user.view',
                'user.create',
                'user.update',
                'user.delete',
                'role.view',
                'role.create',
                'role.update',
                'role.delete',
                'permission.view',
                'permission.create',
                'permission.update',
                'permission.delete',
            ]);
    
        Role::where('name', 'owner')
            ->firstOrFail()
            ->givePermissionTo([
                'product.view', 'product.update', 'product.delete', 'user.view', 'role.view', 'permission.view', 'report.view', 'report.download',
            ]);

        Role::where('name', 'staff')
            ->firstOrFail()
            ->givePermissionTo([
                'product.view', 'product.create', 'product.update', 'product.delete', 'report.view', 'report.download',
            ]);

        User::where('email', 'admin@gmail.com')
            ->firstOrFail()
            ->assignRole('admin');
    
        User::where('email', 'owner@gmail.com')
            ->firstOrFail()
            ->assignRole('owner');

        User::where('email', 'staff@gmail.com')
            ->firstOrFail()
            ->assignRole('staff');
        
    }
}
