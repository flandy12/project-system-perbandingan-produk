<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSalesStat;
use App\Models\Rating;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Models\User;
use App\Services\CatalogueQuery;
use App\Services\ProductFeatureIndex;
use App\Services\SafeProductHtml;
use Database\Seeders\SalesFoundationSeeder;

function readinessProduct(string $name = 'Test model'): Product
{
    $category = Category::firstOrCreate(['name' => 'Readiness fixture']);

    return Product::create(['title' => $name, 'category_id' => $category->id, 'status' => 'active', 'price' => 180000, 'comparison_type' => 'terrestrial']);
}

function readinessUser(string $role): User
{
    (new SalesFoundationSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('gives Owner data analysis access without account administration or publication', function () {
    $owner = readinessUser('owner');
    $this->actingAs($owner)->get('/dashboard')->assertOk();
    $this->get('/stb-analysis')->assertOk();
    $this->get('/products')->assertOk();
    $this->get('/users')->assertForbidden();
    expect($owner->can('publish.stb.analysis'))->toBeFalse();
    $customer = User::factory()->create();
    (new SalesFoundationSeeder)->run();
    expect($customer->fresh()->hasRole('pelanggan'))->toBeTrue();
    $this->actingAs($customer)->get('/dashboard')->assertForbidden();
});

it('saves sourced specifications and withholds changed values until explicit reverification', function () {
    $owner = readinessUser('owner');
    $product = readinessProduct();
    $group = SpecificationGroup::create(['name' => 'Fixture']);
    $values = [];
    $proofs = [];
    foreach (config('stb_analysis.feature_rubrics.terrestrial') as $key => $rule) {
        $spec = Specification::create(['name' => $rule['name'], 'data_type' => $rule['data_type'], 'unit' => $rule['unit'], 'specification_group_id' => $group->id]);
        $values[$spec->id] = (string) $rule['reference'];
        $proofs[$spec->id] = ['url' => 'https://example.com/specification', 'type' => 'official', 'confirmed' => '1'];
    }
    $this->actingAs($owner)->put(route('products.update', $product), ['comparison_type' => 'terrestrial', 'specifications' => $values, 'proofs' => $proofs])->assertRedirect()->assertSessionHasNoErrors();
    expect(app(ProductFeatureIndex::class)->evaluate($product->fresh())['ready'])->toBeTrue();
    $id = array_key_first($values);
    $this->put(route('products.update', $product), ['specifications' => [$id => '1080']])->assertSessionHasNoErrors();
    expect(app(ProductFeatureIndex::class)->evaluate($product->fresh())['ready'])->toBeFalse();
    $this->put(route('products.update', $product), ['specifications' => [$id => '1080'], 'proofs' => [$id => $proofs[$id]]])->assertSessionHasNoErrors();
    expect(app(ProductFeatureIndex::class)->evaluate($product->fresh())['ready'])->toBeTrue();
    $this->put(route('products.update', $product), ['title' => 'Should roll back', 'specifications' => [$id => '-1']])->assertSessionHasErrors('specifications.'.$id);
    expect($product->fresh()->title)->toBe('Test model');
    $this->get('/products')->assertOk()->assertSee('Bukti & verifikasi', false);
});

it('creates products with analysis type and sanitizes editorial content', function () {
    $owner = readinessUser('owner');
    $category = Category::create(['name' => 'New model']);
    $this->actingAs($owner)->post('/products', ['title' => 'New safe model', 'category_id' => $category->id, 'status' => 'active', 'stock' => 0,
        'comparison_type' => 'android', 'description' => '<p onclick="bad()">Deskripsi <strong>aman</strong></p><script>alert(1)</script>'])->assertSessionHasNoErrors();
    $product = Product::where('title', 'New safe model')->sole();
    expect($product->comparison_type)->toBe('android')->and($product->description)->toContain('<strong>aman</strong>')->not->toContain('onclick', '<script', 'alert(1)');
    $this->get('/item/'.$product->id)->assertOk()->assertSee('aman');
});

it('excludes test reviews and unverified sales from all public statistics', function () {
    $a = readinessProduct('Real model');
    $b = readinessProduct('Demo model');
    $real = User::factory()->create();
    $demo = User::factory()->create(['is_active' => false]);
    Rating::create(['product_id' => $a->id, 'user_id' => $real->id, 'rating' => 3, 'review' => 'Actual review']);
    Rating::create(['product_id' => $a->id, 'user_id' => $demo->id, 'rating' => 5, 'review' => '[SIMULASI PENGUJIAN] Test']);
    Rating::create(['product_id' => $b->id, 'user_id' => $real->id, 'rating' => 5, 'review' => '[SIMULASI PENGUJIAN] Test']);
    ProductSalesStat::create(['product_id' => $a->id, 'year' => 2025, 'month' => 1, 'total_sold' => 999999]);
    $page = $this->get('/item/'.$a->id)->assertOk()->assertViewHas('totalSold', null)->assertViewHas('totalRatings', 1)->assertViewHas('averageRating', 3.0);
    $this->get('/')->assertOk()->assertViewHas('topSales', fn ($products) => $products->isEmpty());
    $rows = (new CatalogueQuery)->build(['sort' => 'best'])->get();
    expect($rows->first()->id)->toBe($a->id)->and($rows->first()->ratings_count)->toBe(1)->and($rows->last()->ratings_count)->toBe(0);
    ProductSalesStat::create(['product_id' => $a->id, 'year' => 2025, 'month' => 2, 'total_sold' => 0, 'source' => 'Laporan toko', 'source_reference' => 'LAP-02', 'verification_status' => 'verified', 'is_simulated' => false]);
    $this->get('/item/'.$a->id)->assertViewHas('totalSold', 0)->assertViewHas('salesPeriodLabel', '02/2025');
    $compare = $this->get('/compare?ids='.$a->id.','.$b->id)->assertOk();
    expect((int) $compare->viewData('productA')->sold)->toBe(0)->and($compare->viewData('productB')->sold)->toBeNull();
});

it('requires login and limits repeated review requests while preserving owner-only edits', function () {
    $product = readinessProduct();
    $payload = ['product_id' => $product->id, 'rating' => 4, 'review' => 'Ulasan nyata'];
    $this->postJson('/ratings', $payload)->assertUnauthorized();
    $customer = readinessUser('pelanggan');
    $this->actingAs($customer)->postJson('/ratings', $payload)->assertOk();
    $rating = Rating::sole();
    $other = readinessUser('pelanggan');
    $this->actingAs($other)->putJson('/ratings/'.$rating->id, ['rating' => 1])->assertForbidden();
    $this->actingAs($customer);
    for ($i = 0; $i < 9; $i++) {
        $this->postJson('/ratings', $payload)->assertOk();
    }
    $this->postJson('/ratings', $payload)->assertStatus(429);
    expect(Rating::count())->toBe(1);
});

it('provides real contact actions and strips active content from legacy descriptions', function () {
    $this->get('/hubungi-kami')->assertOk()->assertSee(config('company.whatsapp'), false)->assertDontSee('action="/contact"', false);
    $html = app(SafeProductHtml::class)->clean('<p>Normal</p><img src=x onerror=alert(1)><a href="javascript:bad()">Teks</a><svg onload="bad()"></svg><iframe src="https://example.com"></iframe>');
    expect($html)->toContain('Normal', 'Teks')->not->toContain('onerror', 'javascript', '<svg', '<iframe', '<img');
});
