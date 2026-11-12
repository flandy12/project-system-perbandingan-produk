<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureEvidenceSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = json_decode(file_get_contents(base_path('docs/data/stb-catalogue-2026-10-04.json')), true, flags: JSON_THROW_ON_ERROR);
        $byKey = collect($catalogue['products'])->keyBy('key');
        DB::transaction(function () use ($byKey) {
            Product::with('specifications.specification')->get()->each(function ($product) use ($byKey) {
                $metadata = $product->reference_metadata ?? [];
                $reference = $byKey->get($metadata['catalogue_key'] ?? '');
                if (! $reference) {
                    return;
                }
                foreach ($product->specifications as $record) {
                    $entry = collect($reference['specifications'])->first(fn ($entry) => $entry['name'] === $record->specification?->name
                        && $entry['data_type'] === $record->specification?->data_type && $entry['unit'] === $record->specification?->unit);
                    if (! $entry || $entry['reference'] === null || ! is_numeric($entry['value']) || ! is_numeric($record->value)
                        || abs((float) $entry['value'] - (float) $record->value) > 0.000001) {
                        continue;
                    }
                    // Bind the evidence to the exact value already documented in the catalogue.
                    $metadata['specifications'][$record->specification_id] = [
                        'key' => $entry['key'], 'verified_value' => $entry['value'],
                        'sources' => $entry['sources'], 'reference' => $entry['reference'], 'public' => $entry['public'] ?? true,
                    ];
                }
                $product->update(['reference_metadata' => $metadata]);
            });
        });
    }
}
