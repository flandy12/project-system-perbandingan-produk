<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageStbData
{
    public const RESOURCES = [
        'users' => 'user', 'roles' => 'role', 'permissions' => 'permission',
        'products' => 'product', 'category' => 'category', 'discount' => 'discount',
        'headline-slide' => 'headline', 'specification-groups' => 'specification.group',
        'specification-scores' => 'specification.score', 'specifications' => 'specification',
        'product-specifications' => 'product.specification', 'product-specification-scores' => 'product.specification.score',
        'score-weights' => 'score.weight', 'product-final-scores' => 'product.final.score',
        'product-sales-stats' => 'product.sales', 'comments' => 'comment',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $name = $request->route()->getName() ?? '';
        if ($name === 'products.comments.store') {
            return $next($request);
        }
        if (in_array($name, ['roles.permissions.edit', 'roles.permissions.update', 'give-permission.index'])) {
            abort_unless($request->user()->can('edit.role'), 403);
        } elseif ($name === 'dashboard' || str_starts_with($name, 'top-product.')) {
            abort_unless($request->user()->can($name === 'dashboard' ? 'view.dashboard' : 'view.top.product'), 403);
        } else {
            [$resource, $action] = array_pad(explode('.', $name, 2), 2, '');
            // Customer product comments have their own route and remain available.
            if (isset(self::RESOURCES[$resource])) {
                $verb = match ($action) {
                    'store', 'create' => 'create',
                    'update', 'edit', 'approve', 'reject' => 'edit',
                    'destroy' => 'delete',
                    default => 'view',
                };
                abort_unless($request->user()->can($verb.'.'.self::RESOURCES[$resource]), 403);
            }
        }

        return $next($request);
    }
}
