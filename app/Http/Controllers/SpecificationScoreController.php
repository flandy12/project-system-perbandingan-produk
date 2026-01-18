<?php

namespace App\Http\Controllers;

use App\Models\Specification;
use App\Models\SpecificationScore;
use Illuminate\Http\Request;

class SpecificationScoreController extends Controller
{
    public function index()
    {
        $scores = SpecificationScore::with('specification')
            ->latest()
            ->paginate(10);

        return view('pages.specification-scores.index', compact('scores'));
    }

    public function create()
    {
        $specifications = Specification::orderBy('name')->get();

        return view('specification-scores.create', compact('specifications'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'specification_id' => ['required', 'exists:specifications,id'],
            'is_used'          => ['required', 'boolean'],
        ]);

        SpecificationScore::create($validated);

        return redirect()
            ->route('specification-scores.index')
            ->with('success', 'Specification score berhasil ditambahkan');
    }

    public function edit(SpecificationScore $specificationScore)
    {
        $specifications = Specification::orderBy('name')->get();

        return view(
            'specification-scores.edit',
            compact('specificationScore', 'specifications')
        );
    }

    public function update(Request $request, SpecificationScore $specificationScore)
    {
        $validated = $request->validate([
            'specification_id' => ['required', 'exists:specifications,id'],
            'is_used'          => ['required', 'boolean'],
        ]);

        $specificationScore->update($validated);

        return redirect()
            ->route('specification-scores.index')
            ->with('success', 'Specification score berhasil diupdate');
    }

    public function destroy(SpecificationScore $specificationScore)
    {
        $specificationScore->delete();

        return redirect()
            ->route('specification-scores.index')
            ->with('success', 'Specification score berhasil dihapus');
    }
}
