<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class CatalogueQuery
{
    public function build(array $filters): Builder
    {
        $query = Product::query()->where('status', 'active')
            ->withAvg(['ratings' => fn ($q) => $q->real()], 'rating')->withCount(['ratings' => fn ($q) => $q->real()]);
        foreach (['price_min' => '>=', 'price_max' => '<='] as $field => $operator) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where('price', $operator, $filters[$field]);
            }
        }
        if (($filters['year'] ?? '') === 'unknown') {
            $query->whereNull('production_year');
        } elseif (! empty($filters['year'])) {
            $query->where('production_year', $filters['year']);
        }
        switch ($filters['sort'] ?? '') {
            case 'best':
            case 'worst':
                // Unrated products follow rated products in both directions.
                $query->orderByRaw('ratings_avg_rating IS NULL asc')
                    ->orderBy('ratings_avg_rating', $filters['sort'] === 'best' ? 'desc' : 'asc')
                    ->orderByDesc('ratings_count');
                break;
            case 'oldest':
                $query->orderByRaw('production_year IS NULL')->orderBy('production_year');
                break;
            default:
                $query->orderByRaw('production_year IS NULL')->orderByDesc('production_year');
        }

        return $query->orderBy('products.id');
    }
}
