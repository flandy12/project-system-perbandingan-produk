<?php

namespace App\Services;

use App\Models\Product;

class ProductFeatureIndex
{
    public function evaluate(Product $product): array
    {
        $rubric = config('stb_analysis.feature_rubrics.'.$product->comparison_type, []);
        $version = config('stb_analysis.feature_version');
        $product->loadMissing('specifications.specification');
        $details = [];
        $weighted = 0;
        $weights = 0;
        $complete = 0;
        foreach ($rubric as $key => $rule) {
            $matches = $product->specifications->filter(fn ($row) => $row->specification?->name === $rule['name']);
            $record = $matches->count() === 1 ? $matches->first() : null;
            $metadata = $record ? ($product->reference_metadata['specifications'][$record->specification_id] ?? []) : [];
            $value = $record?->value;
            $reason = null;
            if (! $record) {
                $reason = $matches->count() > 1 ? 'Pemetaan spesifikasi ganda.' : 'Spesifikasi belum tersedia.';
            } elseif ($record->specification->data_type !== $rule['data_type'] || $record->specification->unit !== $rule['unit']) {
                $reason = 'Jenis data atau satuan berbeda dari rubrik.';
            } elseif (($metadata['public'] ?? true) === false || ($metadata['key'] ?? null) !== $key) {
                $reason = 'Spesifikasi belum dipetakan ke bukti rubrik.';
            } elseif (! array_key_exists('verified_value', $metadata) || ! $this->hasSource($metadata['sources'] ?? [])) {
                $reason = 'Bukti nilai spesifikasi belum lengkap.';
            } elseif (! is_numeric($value) || ! is_numeric($metadata['verified_value']) || (float) $value < 0 || ! is_finite((float) $value)) {
                $reason = 'Nilai spesifikasi tidak sah.';
            } elseif ($rule['data_type'] === 'boolean' && ! in_array((float) $value, [0.0, 1.0], true)) {
                $reason = 'Dukungan fitur harus bernilai 0 atau 1.';
            } elseif (abs((float) $value - (float) $metadata['verified_value']) > 0.000001) {
                $reason = 'Nilai berubah dari bukti sumber; perlu diperiksa kembali.';
            }
            $score = $reason === null ? min(100, (float) $value / $rule['reference'] * 100) : null;
            if ($score !== null) {
                $complete++;
                $weighted += $score * $rule['weight'];
            }
            $weights += $rule['weight'];
            $details[] = ['key' => $key, 'name' => $rule['name'], 'value' => $reason === null ? (float) $value : null,
                'unit' => $rule['unit'], 'reference' => $rule['reference'], 'weight' => $rule['weight'],
                'score' => $score === null ? null : round($score, 2), 'reason' => $reason,
                'sources' => $metadata['sources'] ?? []];
        }
        $ready = count($rubric) > 0 && $complete === count($rubric);

        return ['version' => $version, 'type' => $product->comparison_type, 'ready' => $ready,
            'score' => $ready ? round($weighted / $weights, 2) : null,
            'complete' => $complete, 'required' => count($rubric), 'criteria' => $details,
            'missing' => array_values(array_map(fn ($row) => $row['name'].': '.$row['reason'], array_filter($details, fn ($row) => $row['reason'] !== null))),
        ];
    }

    private function hasSource(mixed $sources): bool
    {
        if (! is_array($sources)) {
            return false;
        }
        foreach ($sources as $source) {
            if (! is_array($source)) {
                continue;
            }
            if (($source['type'] ?? '') === 'photo' && ! empty($source['path'])) {
                return true;
            }
            if (in_array($source['type'] ?? '', ['official', 'retailer', 'marketplace'], true)
                && preg_match('~^https?://~i', $source['url'] ?? '')) {
                return true;
            }
        }

        return false;
    }
}
