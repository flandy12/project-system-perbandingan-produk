<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSpecificationScore;
use Illuminate\Support\Collection;

class VerifiedComparisonScores
{
    public function forProduct(Product $product): Collection
    {
        // Preserve isolated legacy fixtures; persisted catalogue products require evidence.
        if (! $product->exists) {
            return $product->specificationScores;
        }
        $product->loadMissing('specifications.specification.group');
        $rules = config('stb_analysis.feature_rubrics.'.$product->comparison_type, []);
        $scores = collect();
        foreach ($product->specifications as $entry) {
            $spec = $entry->specification;
            $proof = $product->reference_metadata['specifications'][$entry->specification_id] ?? [];
            if (! $spec || ! ($proof['public'] ?? true) || empty($proof['sources'])
                || ! is_numeric($entry->value) || ! is_numeric($proof['verified_value'] ?? null)
                || ! is_finite((float) $entry->value) || (float) $entry->value < 0
                || abs((float) $entry->value - (float) $proof['verified_value']) > 0.000001) {
                continue;
            }
            $hasSource = collect($proof['sources'])->contains(fn ($source) => in_array($source['type'] ?? '', ['official', 'seller', 'retailer', 'marketplace'], true)
                    ? preg_match('~^https?://~i', $source['url'] ?? '')
                    : (($source['type'] ?? '') === 'photo' && ! empty($source['path'])));
            if (! $hasSource || ! in_array($spec->data_type, ['numeric', 'boolean'], true)
                || ($spec->data_type === 'boolean' && ! in_array((float) $entry->value, [0.0, 1.0], true))) {
                continue;
            }
            $reference = $proof['reference'] ?? null;
            // A numeric reference supplied by the CMS comes only from the configured rubric.
            if (! array_key_exists('reference', $proof)) {
                $rule = $rules[$proof['key'] ?? ''] ?? null;
                if ($rule && $rule['name'] === $spec->name && $rule['data_type'] === $spec->data_type && $rule['unit'] === $spec->unit) {
                    $reference = $rule['reference'];
                }
            }
            if (! is_numeric($reference) || ! is_finite((float) $reference) || $reference <= 0) {
                continue;
            }
            $score = new ProductSpecificationScore(['specification_id' => $entry->specification_id,
                'raw_value' => $entry->value, 'normalized_score' => round(min((float) $entry->value / $reference * 100, 100), 2)]);
            $score->setAttribute('evidence_reference', (float) $reference);
            $scores->push($score->setRelation('specification', $spec));
        }

        return $scores;
    }
}
