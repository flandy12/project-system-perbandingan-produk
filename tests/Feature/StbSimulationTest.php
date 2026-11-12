<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPriceObservation;
use App\Models\ProductSalesStat;
use App\Models\ProductSpecification;
use App\Models\StbAnalysisRun;
use App\Models\User;
use App\Services\PublishedStbRecommendations;
use App\Services\StbSimulationDataset;
use Database\Seeders\SalesFoundationSeeder;

function simulationProducts(int $count = 5, string $type = 'terrestrial'): void
{
    $category = Category::firstOrCreate(['name' => 'Simulation fixture']);
    for ($i = 0; $i < $count; $i++) {
        Product::create(['title' => 'Fixture model '.$i, 'price' => $i === 0 ? null : 150000 + $i * 25000,
            'category_id' => $category->id, 'status' => 'active', 'comparison_type' => $type]);
    }
}
function simulationActor(string $role = 'pengelola-data'): User
{
    (new SalesFoundationSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
function simulationInput(string $type = 'terrestrial'): array
{
    return ['year' => 2025, 'month' => 7, 'type' => $type, 'scenario' => 'balanced'];
}

it('builds repeatable explicitly assumed rows without filling real tables', function () {
    simulationProducts();
    $before = Product::all()->toArray();
    $builder = new StbSimulationDataset;
    $a = $builder->build(2025, 7, 'terrestrial');
    $b = $builder->build(2025, 7, 'terrestrial');
    expect($a['rows'])->toBe($b['rows'])->and($a['rows'])->toHaveCount(5)
        ->and($a['is_simulated'])->toBeTrue()->and($a['rows'][0]['assumptions'])->toHaveCount(7)
        ->and($a['rows'][0]['values'][0])->toBe(212500.0)
        ->and(Product::all()->toArray())->toBe($before)
        ->and(ProductSalesStat::count())->toBe(0)->and(ProductPriceObservation::count())->toBe(0)
        ->and(ProductSpecification::count())->toBe(0);
});
it('provides ordered low and high demand fixtures without claiming predictions', function () {
    simulationProducts();
    $builder = new StbSimulationDataset;
    $low = $builder->build(2025, 7, 'terrestrial', 'low');
    $base = $builder->build(2025, 7, 'terrestrial');
    $high = $builder->build(2025, 7, 'terrestrial', 'high');
    foreach ($base['rows'] as $i => $row) {
        expect($low['rows'][$i]['values'][2])->toBeLessThanOrEqual($row['values'][2])
            ->and($high['rows'][$i]['values'][2])->toBeGreaterThanOrEqual($row['values'][2]);
    }
});
it('runs simulation through controller with visible labels and an audit export', function () {
    simulationProducts();
    $user = simulationActor();
    $this->actingAs($user)->post(route('stb-analysis.simulate'), simulationInput())->assertRedirect();
    $run = StbAnalysisRun::first();
    expect($run->is_simulated)->toBeTrue()->and($run->status)->toBe('ready')
        ->and($run->result['converged'])->toBeTrue()->and(ProductSalesStat::verifiedReal()->count())->toBe(0);
    $this->get(route('stb-analysis.show', $run))->assertOk()->assertSee('SIMULASI PENGUJIAN')->assertSee('Grafik analisis')->assertDontSee('Terbitkan rekomendasi');
    $export = $this->get(route('stb-analysis.export', $run))->assertOk();
    $json = json_decode($export->streamedContent(), true, 512, JSON_THROW_ON_ERROR);
    expect($json['is_simulated'])->toBeTrue()->and($json['snapshot']['rows'])->toHaveCount(5)
        ->and($json['result']['k'])->toBe($run->result['k']);
});
it('blocks publishing even by admin and rejects simulated snapshots with a forged flag', function () {
    simulationProducts();
    $admin = simulationActor('admin');
    $this->actingAs($admin)->post(route('stb-analysis.simulate'), simulationInput())->assertRedirect();
    $run = StbAnalysisRun::first();
    $this->post(route('stb-analysis.publish', $run))->assertSessionHasErrors('run');
    $run->update(['published_at' => now()]);
    $service = new PublishedStbRecommendations;
    expect($service->valid($run->fresh()))->toBeFalse()->and($service->runs())->toHaveCount(0)
        ->and($service->products($run))->toHaveCount(0);
    $run->update(['is_simulated' => false]);
    expect($service->valid($run->fresh()))->toBeFalse()->and($service->runs())->toHaveCount(0);
    $this->post(route('stb-analysis.publish', $run))->assertSessionHasErrors('run');
    $this->get(route('stb-recommendations.index'))->assertDontSee('Kelompok 1');
});
it('keeps the single Android product insufficient rather than manufacturing more devices', function () {
    simulationProducts(1, 'android');
    $user = simulationActor();
    $this->actingAs($user)->post(route('stb-analysis.simulate'), simulationInput('android'))->assertRedirect();
    $run = StbAnalysisRun::first();
    expect($run->status)->toBe('insufficient')->and($run->result)->toBeNull()->and(Product::count())->toBe(1);
    $this->get(route('stb-analysis.show', $run))->assertOk()->assertSee('Produk palsu tidak ditambahkan')->assertDontSee('Grafik analisis');
});
it('requires management permissions and validates simulation inputs', function () {
    simulationProducts();
    $customer = simulationActor('pelanggan');
    $this->actingAs($customer)->post(route('stb-analysis.simulate'), simulationInput())->assertForbidden();
    $manager = simulationActor();
    $this->actingAs($manager)->post(route('stb-analysis.simulate'), array_replace(simulationInput(), ['scenario' => 'unknown']))->assertSessionHasErrors('scenario');
    $this->post(route('stb-analysis.simulate'), array_replace(simulationInput(), ['month' => now()->month, 'year' => now()->year]))->assertSessionHasErrors('month');
});
