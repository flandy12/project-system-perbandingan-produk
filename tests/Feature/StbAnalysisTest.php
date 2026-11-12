<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPriceObservation;
use App\Models\ProductSalesStat;
use App\Models\ProductSpecification;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Models\StbAnalysisRun;
use App\Models\User;
use App\Services\PublishedStbRecommendations;
use App\Services\StbAnalysisDataset;
use Database\Seeders\SalesFoundationSeeder;

function analysisProduct(float $price, int $units, array $values = [1080, 2, 1, 1, 1]): Product
{
    $product = Product::create(['title' => 'Fixture STB', 'price' => 999999, 'status' => 'active', 'category_id' => Category::firstOrCreate(['name' => 'Test STB'])->id, 'comparison_type' => 'terrestrial']);
    $metadata = [];
    $group = SpecificationGroup::firstOrCreate(['name' => 'Analysis tests']);
    foreach (config('stb_analysis.feature_rubrics.terrestrial') as $key => $rule) {
        $value = array_shift($values);
        if ($value === null) {
            continue;
        }
        $spec = Specification::firstOrCreate(['name' => $rule['name'], 'specification_group_id' => $group->id], ['data_type' => $rule['data_type'], 'unit' => $rule['unit']]);
        ProductSpecification::create(['product_id' => $product->id, 'specification_id' => $spec->id, 'value' => $value]);
        $metadata[$spec->id] = ['key' => $key, 'verified_value' => $value, 'sources' => [['type' => 'official', 'url' => 'https://example.test/spec']], 'public' => true];
    }
    $product->update(['reference_metadata' => ['specifications' => $metadata]]);
    $user = User::factory()->create();
    ProductPriceObservation::create(['product_id' => $product->id, 'price' => $price, 'observed_on' => '2025-07-15', 'source' => 'Fixture store', 'source_reference' => 'Test invoice only', 'user_id' => $user->id]);
    ProductSalesStat::create(['product_id' => $product->id, 'year' => 2025, 'month' => 7, 'total_sold' => $units, 'source' => 'Fixture July store report', 'source_reference' => 'Test report only', 'verification_status' => 'verified', 'is_simulated' => false]);

    return $product;
}
function analysisActor(string $role): User
{
    (new SalesFoundationSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
function analysisInput(): array
{
    return ['year' => 2025, 'month' => 7, 'type' => 'terrestrial', 'source' => 'Fixture July store report', 'k' => 2];
}

it('builds a same month dataset preserving true zero and avoiding current catalogue price', function () {
    $p = analysisProduct(150000, 0);
    $result = app(StbAnalysisDataset::class)->build(2025, 7, 'terrestrial', 'Fixture July store report');
    expect($result['rows'])->toHaveCount(1)->and($result['rows'][0]['values'])->toBe([150000.0, 90.0, 0.0]);
    expect(app(StbAnalysisDataset::class)->build(2025, 8, 'terrestrial', 'Fixture July store report')['rows'])->toBe([]);
    expect(app(StbAnalysisDataset::class)->build(2025, 7, 'satellite', 'Fixture July store report')['total'])->toBe(0);
});
it('excludes missing unsupported simulated and differently scoped evidence', function () {
    $p = analysisProduct(100, 10);
    $p->salesStats()->update(['is_simulated' => true]);
    $p2 = analysisProduct(200, 20);
    $p2->salesStats()->update(['verification_status' => 'unverified']);
    $p3 = analysisProduct(300, 30);
    ProductPriceObservation::where('product_id', $p3->id)->update(['observed_on' => '2025-08-01']);
    analysisProduct(400, 40, [1080]);
    $p5 = analysisProduct(500, 50);
    $p5->salesStats()->update(['source' => 'Another store']);
    $d = app(StbAnalysisDataset::class)->build(2025, 7, 'terrestrial', 'Fixture July store report');
    expect($d['rows'])->toBe([])->and($d['excluded'])->toHaveCount(5);
});
it('stores an auditable result publishes only by admin and withholds changed snapshots', function () {
    $manager = analysisActor('pengelola-data');
    $admin = analysisActor('admin');
    $p = analysisProduct(100000, 10, [1080, 1, 0, 0, 1]);
    analysisProduct(110000, 12, [1080, 1, 0, 0, 1]);
    analysisProduct(500000, 200, [2160, 2, 1, 1, 1]);
    analysisProduct(510000, 202, [2160, 2, 1, 1, 1]);
    $this->actingAs($manager)->post(route('stb-analysis.store'), analysisInput())->assertRedirect();
    $run = StbAnalysisRun::first();
    $snapshot = $run->snapshot;
    expect($run->status)->toBe('ready')->and($run->published_at)->toBeNull();
    $this->get(route('stb-analysis.show', $run))->assertOk()->assertSee('Normalisasi dan evaluasi')->assertSee('Grafik analisis')->assertSee('Kurva Silhouette');
    $this->post(route('stb-analysis.publish', $run))->assertForbidden();
    $this->actingAs($admin)->post(route('stb-analysis.publish', $run))->assertRedirect();
    expect(app(PublishedStbRecommendations::class)->runs())->toHaveCount(1);
    $this->get(route('stb-recommendations.index'))->assertOk()->assertSee('Kelompok 1')->assertDontSee('Grafik analisis');
    ProductPriceObservation::where('product_id', $p->id)->update(['price' => 120000]);
    expect(app(PublishedStbRecommendations::class)->runs())->toHaveCount(0)->and($run->fresh()->snapshot)->toBe($snapshot);
    $this->get(route('stb-recommendations.index'))->assertSee('belum diterbitkan');
    $this->post(route('stb-analysis.publish', $run))->assertSessionHasErrors('run');
});
it('records insufficient runs without inventing result data', function () {
    analysisProduct(150000, 5);
    $user = analysisActor('pengelola-data');
    $this->actingAs($user)->post(route('stb-analysis.store'), analysisInput())->assertRedirect();
    $run = StbAnalysisRun::first();
    expect($run->status)->toBe('insufficient')->and($run->result)->toBeNull();
    $this->get(route('stb-analysis.show', $run))->assertOk()->assertSee('Analisis belum dapat dijalankan');
});
it('rejects unfinished months invalid prices and customer management access', function () {
    $customer = analysisActor('pelanggan');
    $this->actingAs($customer)->get(route('stb-analysis.index'))->assertForbidden();
    $this->post(route('stb-analysis.store'), analysisInput())->assertForbidden();
    $manager = analysisActor('pengelola-data');
    $p = analysisProduct(150000, 5);
    $this->actingAs($manager)->post(route('stb-analysis.store'), array_replace(analysisInput(), ['year' => now()->year, 'month' => now()->month]))->assertSessionHasErrors('month');
    $this->post(route('stb-analysis.price'), ['product_id' => $p->id, 'price' => 0, 'observed_on' => today()->addDay()->toDateString(), 'source' => 'Store', 'source_reference' => 'Document'])->assertSessionHasErrors(['price', 'observed_on', 'confirmed']);
    $this->post(route('stb-analysis.price'), ['product_id' => $p->id, 'price' => 123000, 'observed_on' => '2025-07-01', 'source' => 'Store', 'source_reference' => 'Document', 'confirmed' => 1])->assertRedirect();
    expect($p->fresh()->price)->toBe('999999.00');
});
it('does not treat demo reviews or unverified sales as public recommendations', function () {
    $p = analysisProduct(150000, 1000);
    $p->salesStats()->update(['verification_status' => 'unverified']);
    $result = app(\App\Services\ProductRecommendations::class)->get();
    expect($result['description'])->toContain('belum cukup')->and($result['products'][0]->recommendation_reason)->not->toContain('unit terjual');
    $manager = analysisActor('pengelola-data');
    $this->actingAs($manager)->get(route('stb-analysis.index', ['year' => 2025, 'month' => 7]))->assertOk()->assertSee('produk siap');
});

it('imports only matching dated listing prices and remains repeatable without inventing historical prices', function () {
    analysisActor('admin');
    $manifest = json_decode(file_get_contents(base_path('docs/data/stb-catalogue-2026-10-04.json')), true);
    $listing = collect($manifest['products'])->first(fn ($p) => ($p['price_reference']['status'] ?? '') === 'listing');
    $indexed = collect($manifest['products'])->first(fn ($p) => ($p['price_reference']['status'] ?? '') === 'indexed');
    $category = Category::firstOrCreate(['name' => 'Fixture']);
    foreach ([$listing, $indexed] as $entry) {
        Product::create(['title' => $entry['title'], 'category_id' => $category->id, 'status' => 'active', 'price' => $entry['price'],
            'reference_metadata' => ['catalogue_key' => $entry['key']]]);
    }
    $seeder = new \Database\Seeders\AnalysisPriceEvidenceSeeder;
    $seeder->run();
    $seeder->run();
    expect(ProductPriceObservation::count())->toBe(1);
    $observation = ProductPriceObservation::first();
    expect($observation->observed_on->toDateString())->toBe($listing['price_reference']['checked_at'])
        ->and((float) $observation->price)->toBe((float) $listing['price'])
        ->and($observation->source_reference)->toBe($listing['price_reference']['url']);
});

it('includes month-end prices and uses the latest same-period correction', function () {
    $p = analysisProduct(100000, 0);
    $old = ProductPriceObservation::where('product_id', $p->id)->first();
    ProductPriceObservation::create(['product_id' => $p->id, 'price' => 125000, 'observed_on' => '2025-07-31',
        'source' => 'Fixture store', 'source_reference' => 'Month-end test quote', 'user_id' => $old->user_id]);
    $d = app(StbAnalysisDataset::class)->build(2025, 7, 'terrestrial', 'Fixture July store report');
    expect($d['rows'][0]['values'][0])->toBe(125000.0)->and($d['rows'][0]['price']['observed_on'])->toBe('2025-07-31');
});
