<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GivePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | OWNER
        |--------------------------------------------------------------------------
        */

        Role::findByName('owner')->syncPermissions([

            // Dashboard
            'view.dashboard',

            // User
            'view.user',
            'create.user',
            'edit.user',
            'delete.user',

            // Role
            'view.role',
            'create.role',
            'edit.role',
            'delete.role',

            // Permission
            'view.permission',
            'create.permission',
            'edit.permission',
            'delete.permission',

            // Product
            'view.product',
            'create.product',
            'edit.product',
            'delete.product',

            // Category
            'view.category',
            'create.category',
            'edit.category',
            'delete.category',

            // Discount
            'view.discount',
            'create.discount',
            'edit.discount',
            'delete.discount',

            // Headline
            'view.headline',
            'create.headline',
            'edit.headline',
            'delete.headline',

            // Product Sales
            'view.product.sales',
            'create.product.sales',
            'edit.product.sales',
            'delete.product.sales',

            // Top Product
            'view.top.product',

            // Specification Group
            'view.specification.group',
            'create.specification.group',
            'edit.specification.group',
            'delete.specification.group',

            // Specification
            'view.specification',
            'create.specification',
            'edit.specification',
            'delete.specification',

            // Product Specification
            'view.product.specification',
            'create.product.specification',
            'edit.product.specification',
            'delete.product.specification',

            // Score Weight
            'view.score.weight',
            'create.score.weight',
            'edit.score.weight',
            'delete.score.weight',
        ]);

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        Role::findByName('admin')->syncPermissions([

            'view.dashboard',

            'view.product',
            'create.product',
            'edit.product',
            'delete.product',

            'view.category',
            'create.category',
            'edit.category',
            'delete.category',

            'view.discount',
            'create.discount',
            'edit.discount',
            'delete.discount',

            'view.headline',
            'create.headline',
            'edit.headline',
            'delete.headline',

            'view.product.sales',
            'create.product.sales',
            'edit.product.sales',
            'delete.product.sales',

            'view.top.product',

            'view.specification.group',
            'create.specification.group',
            'edit.specification.group',
            'delete.specification.group',

            'view.specification',
            'create.specification',
            'edit.specification',
            'delete.specification',

            'view.product.specification',
            'create.product.specification',
            'edit.product.specification',
            'delete.product.specification',

            'view.score.weight',
            'create.score.weight',
            'edit.score.weight',
            'delete.score.weight',

            'view.discount',
            'create.discount',
            'edit.discount',
            'delete.discount',

            'view.user',
            'view.role',
            'view.permission',

            'create.user',
            'edit.user',
            'delete.user',

            'create.role',
            'edit.role',
            'delete.role',

            'create.permission',
            'edit.permission',
            'delete.permission',
        ]);

        /*
        |--------------------------------------------------------------------------
        | STAFF
        |--------------------------------------------------------------------------
        */

        Role::findByName('staff')->syncPermissions([

            'view.dashboard',

            'view.product',
            'edit.product',

            'view.category',

            'view.discount',

            'view.top.product',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Assign Role
        |--------------------------------------------------------------------------
        */

        User::where('email', 'owner@gmail.com')
            ->firstOrFail()
            ->syncRoles('owner');

        User::where('email', 'admin@gmail.com')
            ->firstOrFail()
            ->syncRoles('admin');

        User::where('email', 'staff@gmail.com')
            ->firstOrFail()
            ->syncRoles('staff');
    }
}
