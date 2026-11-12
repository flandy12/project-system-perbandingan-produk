<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Specification;
use Illuminate\Validation\ValidationException;

class SaveProductSpecifications
{
    public function save(Product $product, array $values, array $proofs, int $userId): void
    {
        $definitions = Specification::whereIn('id', array_keys($values))->get()->keyBy('id');
        $metadata = $product->reference_metadata ?? [];
        $rules = config('stb_analysis.feature_rubrics.'.$product->comparison_type, []);
        foreach ($values as $id => $value) {
            $spec = $definitions->get($id);
            if (! $spec) {
                throw ValidationException::withMessages(['specifications.'.$id => 'Spesifikasi tidak dikenal.']);
            }
            if ($value === null || trim($value) === '') {
                if (! empty($proofs[$id]['confirmed'])) {
                    throw ValidationException::withMessages(['specifications.'.$id => 'Isi nilai sebelum mengonfirmasi sumber.']);
                }
                $product->specifications()->where('specification_id', $id)->delete();
                unset($metadata['specifications'][$id]);

                continue;
            }
            if (in_array($spec->data_type, ['numeric', 'boolean'], true) && (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0)) {
                throw ValidationException::withMessages(['specifications.'.$id => 'Gunakan angka tidak negatif tanpa satuan.']);
            }
            if ($spec->data_type === 'boolean' && ! in_array((float) $value, [0.0, 1.0], true)) {
                throw ValidationException::withMessages(['specifications.'.$id => 'Gunakan 1 untuk didukung atau 0 untuk tidak didukung.']);
            }
            $product->specifications()->updateOrCreate(['specification_id' => $id], ['value' => $value]);
            $proof = $proofs[$id] ?? [];
            // Existing evidence remains bound to its old verified value until explicitly reviewed.
            if (empty($proof['confirmed'])) {
                continue;
            }
            if (empty($proof['url'])) {
                throw ValidationException::withMessages(['proofs.'.$id.'.url' => 'URL sumber wajib diisi saat verifikasi.']);
            }
            $key = null;
            foreach ($rules as $candidate => $rule) {
                if ($rule['name'] === $spec->name && $rule['data_type'] === $spec->data_type && $rule['unit'] === $spec->unit) {
                    $key = $candidate;
                }
            }
            $metadata['specifications'][$id] = ['key' => $key, 'verified_value' => $value, 'public' => true,
                'verified_by' => $userId, 'verified_at' => now()->toIso8601String(),
                'sources' => [['type' => $proof['type'] ?? 'retailer', 'url' => $proof['url']]]];
        }
        // Rebuild catalogue copy from current specifications instead of leaving stale imported highlights.
        unset($metadata['summary'], $metadata['highlights']);
        $product->update(['reference_metadata' => $metadata]);
    }
}
