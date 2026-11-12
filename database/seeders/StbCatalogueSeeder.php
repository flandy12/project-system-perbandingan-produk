<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationScore;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StbCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = json_decode(file_get_contents(base_path('docs/data/stb-catalogue-2026-10-04.json')), true, flags: JSON_THROW_ON_ERROR);
        $types = ['terrestrial' => 'STB DVB-T2', 'satellite' => 'Receiver parabola', 'combo' => 'Receiver combo', 'android' => 'Android TV Box'];

        DB::transaction(function () use ($catalogue, $types) {
            foreach ($catalogue['products'] as $row) {
                if (! is_file(storage_path('app/public/'.$row['image']))) {
                    throw new RuntimeException("Foto produk tidak ditemukan: {$row['key']}");
                }
                $product = $row['existing_id']
                    ? Product::findOrFail($row['existing_id'])
                    : Product::where('reference_metadata->catalogue_key', $row['key'])->first();
                if ($product && $product->reference_metadata
                    && ($product->reference_metadata['catalogue_key'] ?? $row['key']) !== $row['key']) {
                    throw new RuntimeException("Pemetaan produk berbeda: {$row['key']}");
                }
                $category = Category::firstOrCreate(['name' => $types[$row['comparison_type']]], ['is_active' => true]);
                $specificationReferences = [];
                $description = '<p>'.e($row['summary']).'</p><h4>Fitur utama</h4><ul>';
                foreach ($row['highlights'] as $highlight) {
                    $description .= '<li>'.e($highlight).'</li>';
                }
                $description .= '</ul>';
                $attributes = [
                    'title' => $row['title'], 'description' => $description, 'image' => $row['image'],
                    'category_id' => $category->id, 'price' => $row['price'], 'status' => 'active',
                    'comparison_type' => $row['comparison_type'],
                    'release_year' => $row['release_year'] ?? null,
                ];
                if (! $product) {
                    $product = Product::create($attributes);
                } else {
                    $product->update($attributes);
                }

                $managedSpecIds = [];
                $scoredSpecIds = [];
                foreach ($row['specifications'] as $entry) {
                    $group = SpecificationGroup::firstOrCreate(['name' => $entry['group']]);
                    $spec = Specification::firstOrCreate(
                        ['name' => $entry['name'], 'specification_group_id' => $group->id],
                        ['data_type' => $entry['data_type'], 'unit' => $entry['unit']]
                    );
                    $spec->update(['data_type' => $entry['data_type'], 'unit' => $entry['unit']]);
                    ProductSpecification::updateOrCreate(
                        ['product_id' => $product->id, 'specification_id' => $spec->id],
                        ['value' => (string) $entry['value']]
                    );
                    $managedSpecIds[] = $spec->id;
                    if ($entry['reference'] !== null) {
                        if (! is_numeric($entry['value']) || $entry['value'] < 0 || $entry['reference'] <= 0) {
                            throw new RuntimeException("Nilai atau acuan tidak valid: {$entry['name']}");
                        }
                        $scoredSpecIds[] = $spec->id;
                        ProductSpecificationScore::updateOrCreate(
                            ['product_id' => $product->id, 'specification_id' => $spec->id],
                            ['raw_value' => $entry['value'], 'normalized_score' => round(min($entry['value'] / $entry['reference'] * 100, 100), 2)]
                        );
                    }
                    $specificationReferences[$spec->id] = ['key' => $entry['key'], 'verified_value' => $entry['value'], 'sources' => $entry['sources'], 'reference' => $entry['reference'], 'public' => $entry['public'] ?? true];
                }

                // Previously imported scores must not survive a changed rubric or source status.
                $previousIds = array_keys($product->reference_metadata['specifications'] ?? []);
                $previousIds = array_merge($previousIds, $managedSpecIds, Specification::where('name', 'like', 'Simulasi:%')->pluck('id')->all());
                ProductSpecificationScore::where('product_id', $product->id)
                    ->whereIn('specification_id', $previousIds)->whereNotIn('specification_id', $scoredSpecIds)->delete();
                // Remove our earlier app-count score for the original two products.
                if (in_array($product->id, [4, 6], true)) {
                    $oldIds = Specification::whereIn('name', ['Jumlah aplikasi yang dicantumkan', 'Memori sesuai label produsen'])->pluck('id');
                    ProductSpecificationScore::where('product_id', $product->id)->whereIn('specification_id', $oldIds)->delete();
                }
                $product->update(['reference_metadata' => [
                    'summary' => $row['summary'], 'highlights' => $row['highlights'],
                    'catalogue_key' => $row['key'], 'checked_at' => $catalogue['checked_at'],
                    'source_status' => $row['source_status'], 'source_url' => $row['source_url'],
                    'price_source' => $row['price_source'], 'notes' => $row['notes'],
                    'price_reference' => $row['price_reference'] ?? null,
                    'release_reference' => $row['release_reference'] ?? null,
                    'alternate_images' => $row['alternate_images'], 'specifications' => $specificationReferences,
                ]]);
                $this->command?->info("{$product->id}: {$product->title} — ".count($managedSpecIds).' spesifikasi, '.count($scoredSpecIds).' skor bersumber.');
            }
        });
    }
}
