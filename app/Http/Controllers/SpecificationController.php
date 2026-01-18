<?php

namespace App\Http\Controllers;

use App\Models\Specification;
use App\Models\SpecificationGroup;
use Illuminate\Http\Request;

class SpecificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specifications = Specification::with('group')
            ->latest()
            ->paginate(10);

        $groups = SpecificationGroup::orderBy('name')->get();

        return view('pages.specifications.index', compact('specifications', 'groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specification_group_id' => 'required|exists:specification_groups,id',
            'data_type' => 'required|in:numeric,boolean,text',
            'unit' => 'nullable|string|max:50',
        ]);

        Specification::create($validated);

        return redirect()
            ->route('specifications.index')
            ->with('success', 'Specification berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Specification $specification)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specification_group_id' => 'required|exists:specification_groups,id',
            'data_type' => 'required|in:numeric,boolean,text',
            'unit' => 'nullable|string|max:50',
        ]);

        $specification->update($validated);

        return redirect()
            ->route('specifications.index')
            ->with('success', 'Specification berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specification $specification)
    {
        // Optional safety check:
        if ($specification->productSpecifications()->exists()) {
            return back()->with('error', 'Specification masih digunakan oleh produk');
        }

        $specification->delete();

        return redirect()
            ->route('specifications.index')
            ->with('success', 'Specification berhasil dihapus');
    }
}
