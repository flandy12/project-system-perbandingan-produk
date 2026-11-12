<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CatalogueQuery;
use Illuminate\Support\Facades\DB;

it('sorts user ratings in both directions with unrated products last', function () {
    $category = Category::create(['name' => 'Test', 'is_active' => true]);
    $user = User::factory()->create();
    $make = fn ($title) => Product::create(['title' => $title, 'category_id' => $category->id, 'status' => 'active', 'price' => 100]);
    $unrated = $make('Unrated');
    $high = $make('High');
    $low = $make('Low');
    foreach ([[$high, 5], [$low, 2]] as [$product, $rating]) {
        DB::table('ratings')->insert(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => $rating]);
    }
    $query = new CatalogueQuery;
    expect($query->build(['sort' => 'best'])->pluck('id')->all())->toBe([$high->id, $low->id, $unrated->id])
        ->and($query->build(['sort' => 'worst'])->pluck('id')->all())->toBe([$low->id, $high->id, $unrated->id]);
});

it('filters input production years rather than catalogue creation years', function () {
    $category = Category::create(['name' => 'Test', 'is_active' => true]);
    $old = Product::create(['title' => 'Old', 'category_id' => $category->id, 'status' => 'active', 'price' => 100, 'production_year' => 2021]);
    $unknown = Product::create(['title' => 'Unknown', 'category_id' => $category->id, 'status' => 'active', 'price' => null]);
    $query = new CatalogueQuery;
    expect($query->build(['year' => '2021'])->pluck('id')->all())->toBe([$old->id])
        ->and($query->build(['year' => 'unknown'])->pluck('id')->all())->toBe([$unknown->id])
        ->and($query->build(['price_min' => 0])->pluck('id')->all())->toBe([$old->id]);
});

it('sorts by input production year with missing years last', function () {
    $category = Category::create(['name' => 'Test', 'is_active' => true]);
    $ids = [];
    foreach ([null, 2020, 2024] as $year) {
        $ids[] = Product::create(['title' => 'STB', 'category_id' => $category->id, 'status' => 'active', 'price' => 100, 'production_year' => $year])->id;
    }
    $query = new CatalogueQuery;
    expect($query->build(['sort' => 'newest'])->pluck('id')->all())->toBe([$ids[2], $ids[1], $ids[0]])
        ->and($query->build(['sort' => 'oldest'])->pluck('id')->all())->toBe([$ids[1], $ids[2], $ids[0]]);
});
