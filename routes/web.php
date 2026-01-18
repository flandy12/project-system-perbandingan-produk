<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\HeadlineSliderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\SpecificationGroupController;
use App\Http\Controllers\SpecificationScoreController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Role;

Route::get('/', function () {
    return view('pages.home.index');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    // Role Permission (gunakan PUT agar konsisten REST)
    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'edit'])
        ->name('roles.permissions.edit');

    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])
        ->name('roles.permissions.update');

    Route::get('give-permission', [RolePermissionController::class, 'index'])
        ->name('give-permission.index');

    Route::resource('products', ProductController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('discount', DiscountController::class);

    Route::resource('headline-slide', HeadlineSliderController::class );
    Route::resource('specification-groups', SpecificationGroupController::class );
    Route::resource('specification-scores', SpecificationScoreController::class );
    Route::resource('specifications', SpecificationController::class);
    Route::resource('product-specifications', ProductSpecificationController::class );
});

