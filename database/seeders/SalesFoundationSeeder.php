<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SalesFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [];
        foreach (array_unique(\App\Http\Middleware\ManageStbData::RESOURCES) as $resource) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $name = $action.'.'.$resource;
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $permissions[] = $name;
            }
        }
        foreach (['view.dashboard', 'view.top.product', 'view.stb.analysis', 'create.stb.analysis'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $permissions[] = $name;
        }
        $dataPermissions = array_values(array_filter($permissions, function ($name) {
            return ! preg_match('/\\.(user|role|permission|score\\.weight|specification\\.score|specification\\.group|product\\.final\\.score)$/', $name);
        }));
        Role::firstOrCreate(['name' => 'pengelola-data', 'guard_name' => 'web'])->givePermissionTo($dataPermissions);
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web'])->syncPermissions($dataPermissions);
        Role::firstOrCreate(['name' => 'pelanggan', 'guard_name' => 'web']);
        \App\Models\User::whereDoesntHave('roles')->each(fn ($user) => $user->assignRole('pelanggan'));
        Permission::firstOrCreate(['name' => 'publish.stb.analysis', 'guard_name' => 'web']);
        $permissions[] = 'publish.stb.analysis';
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->givePermissionTo($permissions);
        Product::whereNull('product_code')->orderBy('id')->each(function ($product) {
            $product->forceFill(['product_code' => 'STB-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT)])->save();
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
