<?php

namespace App\Http\Controllers;

use App\Models\ScoreWeight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScoreWeightController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $weights = ScoreWeight::orderBy('key')->get();

        return view('pages.score-weights.index', compact('weights'));
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:score_weights,key'],
            'weight' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        DB::transaction(function () use ($validated) {
            ScoreWeight::create($validated);

            $this->validateTotalWeight();
        });

        return redirect()
            ->back()
            ->with('success', 'Score weight berhasil ditambahkan.');
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, ScoreWeight $scoreWeight)
    {
        $validated = $request->validate([
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('score_weights', 'key')->ignore($scoreWeight->id),
            ],
            'weight' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        DB::transaction(function () use ($scoreWeight, $validated) {
            $scoreWeight->update($validated);

            $this->validateTotalWeight();
        });

        return redirect()
            ->back()
            ->with('success', 'Score weight berhasil diperbarui.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(ScoreWeight $scoreWeight)
    {
        DB::transaction(function () use ($scoreWeight) {
            $scoreWeight->delete();
            $this->validateTotalWeight(false);
        });

        return redirect()
            ->back()
            ->with('success', 'Score weight berhasil dihapus.');
    }

    /**
     * Validate total weight must be equal to 1.000
     */
    protected function validateTotalWeight(bool $throwException = true): void
    {
        $total = ScoreWeight::sum('weight');

        if (round($total, 3) !== 1.000) {
            if ($throwException) {
                abort(
                    422,
                    'Total weight harus bernilai 1.000. Total saat ini: ' . number_format($total, 3)
                );
            }
        }
    }
}
