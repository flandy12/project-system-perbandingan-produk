<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\HeadlineSliderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductFinalScoreController;
use App\Http\Controllers\ProductSalesStatController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\ProductSpecificationScoreController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ScoreWeightController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\SpecificationGroupController;
use App\Http\Controllers\SpecificationScoreController;
use App\Http\Controllers\TopSalesController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Role;

Route::get('/rekomendasi-stb', [\App\Http\Controllers\StbAnalysisController::class, 'publicIndex'])->name('stb-recommendations.index');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/item/{product}', [HomeController::class, 'show'])->name('product.show');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('gallery.index');
Route::post('/track/product-click/{product}', [TrackingController::class, 'productClick']);
Route::post('/track/page-view', [TrackingController::class, 'pageView']);
Route::get('/compare', [HomeController::class, 'compare'])->name('versus.index');
Route::get('/hubungi-kami', [HomeController::class, 'contact'])->name('contact.index');
Route::post('/ratings', [RatingController::class, 'store'])->middleware(['auth', 'throttle:10,1']);



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    \App\Http\Middleware\ManageStbData::class,
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
    Route::resource('top-product', TopSalesController::class);
    Route::get('/sales-import', [\App\Http\Controllers\SalesImportController::class, 'index'])->name('sales-import.index');
    Route::get('/sales-import/template', [\App\Http\Controllers\SalesImportController::class, 'template'])->name('sales-import.template');
    Route::post('/sales-import', [\App\Http\Controllers\SalesImportController::class, 'preview'])->name('sales-import.preview');
    Route::get('/sales-import/{batch}', [\App\Http\Controllers\SalesImportController::class, 'show'])->name('sales-import.show');
    Route::post('/sales-import/{batch}/apply', [\App\Http\Controllers\SalesImportController::class, 'apply'])->name('sales-import.apply');
    Route::get('/stb-analysis', [\App\Http\Controllers\StbAnalysisController::class, 'index'])->name('stb-analysis.index');
    Route::post('/stb-analysis/prices', [\App\Http\Controllers\StbAnalysisController::class, 'price'])->name('stb-analysis.price');
    Route::post('/stb-analysis', [\App\Http\Controllers\StbAnalysisController::class, 'store'])->name('stb-analysis.store');
    Route::post('/stb-analysis/simulation', [\App\Http\Controllers\StbAnalysisController::class, 'simulate'])->name('stb-analysis.simulate');
    Route::get('/stb-analysis/{run}/export', [\App\Http\Controllers\StbAnalysisController::class, 'export'])->name('stb-analysis.export');
    Route::get('/stb-analysis/{run}', [\App\Http\Controllers\StbAnalysisController::class, 'show'])->name('stb-analysis.show');
    Route::post('/stb-analysis/{run}/publish', [\App\Http\Controllers\StbAnalysisController::class, 'publish'])->name('stb-analysis.publish');
    Route::delete('/stb-analysis/{run}/publish', [\App\Http\Controllers\StbAnalysisController::class, 'unpublish'])->name('stb-analysis.unpublish');
    Route::resource('product-sales-stats', ProductSalesStatController::class);

    Route::put('/ratings/{rating}', [RatingController::class, 'update']);
    Route::delete('/ratings/{rating}', [RatingController::class, 'destroy']);

    Route::resource('comments', CommentController::class);

    Route::post('/products/{product}/comments', [CommentController::class, 'store'])->middleware('throttle:10,1')->name('products.comments.store');
    Route::patch('/comments/{comment}/approve', [CommentController::class, 'approve'])
        ->name('comments.approve');

    Route::patch('/comments/{comment}/reject', [CommentController::class, 'reject'])
        ->name('comments.reject');
});
