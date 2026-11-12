<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductClick;
use App\Models\User;
use App\Models\WebsiteVisit;
use Database\Seeders\SalesFoundationSeeder;

function cmsActor(): User
{
    (new SalesFoundationSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function cmsProduct(): Product
{
    $category = Category::create(['name' => 'CMS fixture']);

    return Product::create(['title' => 'CMS fixture', 'category_id' => $category->id, 'status' => 'active', 'price' => null]);
}

it('uses the same selected period and fills days without activity', function () {
    $this->travelTo(now()->setDate(2026, 10, 26)->setTime(12, 0));
    $actor = cmsActor();
    $product = cmsProduct();
    foreach ([now(), now()->subDays(8)] as $date) {
        WebsiteVisit::create(['ip_address' => '127.0.0.1', 'visited_at' => $date]);
        ProductClick::create(['product_id' => $product->id, 'ip_address' => '127.0.0.1', 'clicked_at' => $date]);
    }
    $response = $this->actingAs($actor)->get('/dashboard?days=7')->assertOk()->assertSee('Ringkasan katalog');
    expect($response->viewData('totalVisits'))->toBe(1)
        ->and($response->viewData('totalClicks'))->toBe(1)
        ->and($response->viewData('dailyVisits'))->toHaveCount(7)
        ->and($response->viewData('dailyVisits')->sum('total'))->toBe(1)
        ->and($response->viewData('topProducts')->first()->total)->toBe(1);
    $this->get('/dashboard?days=90')->assertOk()->assertViewHas('totalClicks', 2);
    $this->get('/dashboard?days=999')->assertSessionHasErrors('days');
});

it('stores and clears a per product official link without changing an unknown price', function () {
    $actor = cmsActor();
    $product = cmsProduct();
    $this->actingAs($actor)->put(route('products.update', $product), ['official_url' => 'https://example.com/official/product'])
        ->assertRedirect(route('products.index'));
    expect($product->fresh()->official_url)->toBe('https://example.com/official/product')->and($product->fresh()->price)->toBeNull();
    $this->put(route('products.update', $product), ['stock' => 0, 'status' => 'active', 'official_url' => 'https://example.com/official/product'])->assertRedirect();
    expect($product->fresh()->status)->toBe('active');
    $this->get('/item/'.$product->id)->assertOk()->assertSee('Kunjungi website resmi')->assertSee('https://example.com/official/product', false);
    $this->put(route('products.update', $product), ['official_url' => ''])->assertRedirect();
    $this->get('/item/'.$product->id)->assertOk()->assertDontSee('Kunjungi website resmi');
});

it('validates link protocols and prevents customer product edits', function () {
    $actor = cmsActor();
    $product = cmsProduct();
    foreach (['javascript:alert(1)', 'data:text/html,test', 'ftp://example.com/test'] as $url) {
        $this->actingAs($actor)->put(route('products.update', $product), ['official_url' => $url])->assertSessionHasErrors('official_url');
    }
    $this->actingAs(User::factory()->create())->put(route('products.update', $product), ['official_url' => 'https://example.com'])->assertForbidden();
    expect($product->fresh()->official_url)->toBeNull();
});

it('accepts an official link when creating a product', function () {
    $actor = cmsActor();
    $category = Category::create(['name' => 'New fixture']);
    $this->actingAs($actor)->post(route('products.store'), ['title' => 'New model', 'category_id' => $category->id,
        'stock' => 1, 'price' => null, 'official_url' => 'https://example.com/model'])->assertRedirect();
    expect(Product::where('title', 'New model')->first()->official_url)->toBe('https://example.com/model');
});

it('keeps editing the same product after a validation failure and shows the link to customers', function () {
    $admin = cmsActor();
    $product = cmsProduct();
    $this->actingAs($admin)->from(route('products.index'))->post(route('products.update', $product), [
        '_method' => 'PUT', '_editing_product_id' => $product->id,
        'official_url' => 'not-a-url',
    ])->assertRedirect(route('products.index'))->assertSessionHasErrors('official_url');
    $this->get(route('products.index'))->assertOk()
        ->assertSee('Perubahan belum tersimpan.')
        ->assertSee('action="'.route('products.update', $product).'"', false)
        ->assertSee('id="method" value="PUT"', false);
    $this->post(route('products.update', $product), [
        '_method' => 'PUT', '_editing_product_id' => $product->id,
        'official_url' => 'https://example.com/official/product',
    ])->assertRedirect(route('products.index'));
    $this->get(route('products.index'))->assertSee('Product berhasil diperbarui');
    $this->actingAs(User::factory()->create())->get('/item/'.$product->id)->assertOk()
        ->assertSeeInOrder(['Harga belum tersedia', 'Kunjungi website resmi', 'Deskripsi'])
        ->assertSee('target="_blank" rel="noopener noreferrer"', false);
});
