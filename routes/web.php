<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\HeadlineSliderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductFinalScoreController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\ProductSpecificationScoreController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ScoreWeightController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\SpecificationGroupController;
use App\Http\Controllers\SpecificationScoreController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Role;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('gallery.index');
Route::post('/track/product-click/{product}', [TrackingController::class, 'productClick']);
Route::post('/track/page-view', [TrackingController::class, 'pageView']);


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [AnalyticsController::class, 'index'])->name('dashboard');

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

    Route::resource('headline-slide', HeadlineSliderController::class);
    Route::resource('specification-groups', SpecificationGroupController::class);
    Route::resource('specification-scores', SpecificationScoreController::class);
    Route::resource('specifications', SpecificationController::class);
    Route::resource('product-specifications', ProductSpecificationController::class);
    Route::resource('product-specification-scores', ProductSpecificationScoreController::class);
    Route::resource('score-weights', ScoreWeightController::class);
    Route::resource('product-final-scores', ProductFinalScoreController::class);
});
