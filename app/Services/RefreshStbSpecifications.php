<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use Illuminate\Support\Facades\DB;

class RefreshStbSpecifications
{
    public function apply(array $patch, array $baseline): array
    {
        $before = collect($baseline['products'])->keyBy('key');

        return DB::transaction(function () use ($patch, $before) {
            $result = ['applied' => 0, 'conflicts' => [], 'products' => 0];
            foreach ($patch['products'] as $row) {
                $product = Product::where('reference_metadata->catalogue_key', $row['key'])->first();
                if (! $product) {
                    continue;
                }
                $metadata = $product->reference_metadata;
                foreach ($row['facts'] as $fact) {
                    $group = SpecificationGroup::firstOrCreate(['name' => $fact['group']]);
                    $spec = Specification::firstOrCreate(['name' => $fact['name'], 'specification_group_id' => $group->id],
                        ['data_type' => $fact['data_type'], 'unit' => $fact['unit']]);
                    $current = $product->specifications()->where('specification_id', $spec->id)->first();
                    $original = collect($before[$row['key']]['specifications'] ?? [])->firstWhere('key', $fact['key']);
                    $oldProof = $metadata['specifications'][$spec->id] ?? null;
                    $same = fn ($a, $b) => is_numeric($a) && is_numeric($b) ? abs((float) $a - (float) $b) < 0.000001 : (string) $a === (string) $b;
                    if ($spec->data_type !== $fact['data_type'] || $spec->unit !== $fact['unit']
                        || ($current && ! $same($current->value, $fact['value']) && (! $original || ! $same($current->value, $original['value'])))
                        || ($oldProof && isset($oldProof['verified_by']) && ($oldProof['sources'][0]['url'] ?? '') !== $row['url'])) {
                        $result['conflicts'][] = $product->id.': '.$fact['name'];

                        continue;
                    }
                    $newProof = ['key' => $fact['key'], 'verified_value' => $fact['value'], 'public' => true,
                        'reference' => $fact['reference'], 'note' => $fact['note'], 'checked_at' => $patch['checked_at'],
                        'sources' => [['type' => 'official', 'url' => $row['url'], 'checked_at' => $patch['checked_at']]]];
                    if ($oldProof && $oldProof !== $newProof) {
                        $metadata['evidence_history'][$spec->id][] = $oldProof;
                    }
                    $product->specifications()->updateOrCreate(['specification_id' => $spec->id], ['value' => (string) $fact['value']]);
                    $metadata['specifications'][$spec->id] = $newProof;
                    $result['applied']++;
                }
                $metadata['official_page_review'] = ['url' => $row['url'], 'checked_at' => $patch['checked_at'],
                    'scope' => 'Hanya fakta pada patch yang diperbarui; bukan verifikasi menyeluruh setiap spesifikasi lama.'];
                $product->update(['reference_metadata' => $metadata]);
                $result['products']++;
            }

            return $result;
        });
    }
}
