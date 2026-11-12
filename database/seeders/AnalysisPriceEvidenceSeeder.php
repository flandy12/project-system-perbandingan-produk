<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductPriceObservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnalysisPriceEvidenceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::role('admin')->orderBy('id')->first();
        if (! $user) {
            return;
        }
        $manifest = json_decode(file_get_contents(base_path('docs/data/stb-catalogue-2026-10-04.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($manifest['products'] as $entry) {
            $reference = $entry['price_reference'] ?? [];
            if (($reference['status'] ?? '') !== 'listing' || ! isset($entry['price']) || $entry['price'] <= 0
                || ! preg_match('~^https?://~i', $reference['url'] ?? '') || empty($reference['checked_at'])) {
                continue;
            }
            $product = Product::all()->first(fn ($p) => ($p->reference_metadata['catalogue_key'] ?? null) === $entry['key']);
            if (! $product || $product->price === null || abs((float) $product->price - (float) $entry['price']) > 0.001) {
                continue;
            }
            $exists = ProductPriceObservation::where('product_id', $product->id)
                ->whereDate('observed_on', $reference['checked_at'])->where('price', $entry['price'])
                ->where('source_reference', $reference['url'])->exists();
            if (! $exists) {
                ProductPriceObservation::create(['product_id' => $product->id, 'observed_on' => $reference['checked_at'],
                    'price' => $entry['price'], 'source_reference' => $reference['url'],
                    'source' => $reference['seller'] ?? 'Sumber katalog terdokumentasi', 'user_id' => $user->id]);
            }
        }
    }
}
