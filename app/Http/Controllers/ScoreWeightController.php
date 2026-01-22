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
        // Ambil input mentah dulu
        $input = $request->all();

        // Ganti koma dengan titik di 'weight'
        if (isset($input['weight'])) {
            $input['weight'] = str_replace(',', '.', $input['weight']);
        }

        // Validasi
        $validated = validator($input, [
            'key' => ['required', 'string', 'max:255', 'unique:score_weights,key'],
            'weight' => ['required', 'numeric', 'min:0', 'max:1'],
        ])->validate();

        // Cast ke float
        $validated['weight'] = (float) $validated['weight'];

        // Simpan dengan transaction
        DB::transaction(function () use ($validated) {
            ScoreWeight::create($validated);

            // Validasi total weight hanya jika total > 0
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

            // Nonaktifkan exception saat hapus, biar bisa hapus semua record dulu
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
        $totalRounded = round($total, 3);
        $epsilon = 0.0001; // toleransi floating point

        // Jika tabel kosong atau total = 0, jangan error
        if ($totalRounded <= 0) {
            return;
        }

        // Jika total melebihi 1.000 → tetap error
        if ($totalRounded - 1.000 > $epsilon) {
            if ($throwException) {
                abort(
                    422,
                    'Total weight tidak boleh lebih dari 1.000. Total saat ini: ' . number_format($totalRounded, 3)
                );
            }
        }

        // Jika total < 1.000 → boleh, tidak abort, tapi bisa kasih warning
        if ($totalRounded < 1.000 - $epsilon) {
            session()->flash('warning', 'Total weight saat ini: ' . number_format($totalRounded, 3) . '. Total belum 1.000.');
        }
    }
}
