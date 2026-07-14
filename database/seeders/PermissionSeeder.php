<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

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

            // Top Product
            'view.top.product',

            // Product Sales
            'view.product.sales',
            'create.product.sales',
            'edit.product.sales',
            'delete.product.sales',

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

        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);

        }
    }
}