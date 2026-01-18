<?php

namespace App\Http\Controllers;

use App\Models\SpecificationGroup;
use Illuminate\Http\Request;

class SpecificationGroupController extends Controller
{
    public function index()
    {
        $specifications = SpecificationGroup::withCount('specifications')
            ->orderBy('name')
            ->paginate(15);
        $groups = SpecificationGroup::withCount('specifications')
            ->orderBy('name')
            ->paginate(15);

        return view('pages.specification-groups.index', compact('groups', 'specifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.specification-groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:specification_groups,name'
        ]);

        SpecificationGroup::create($validated);

        return redirect()
            ->route('specification-groups.index')
            ->with('success', 'Specification Group berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(SpecificationGroup $specificationGroup)
    {
        $specificationGroup->load('specifications');

        return view('pages.specification-groups.show', compact('specificationGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SpecificationGroup $specificationGroup)
    {
        return view('pages.specification-groups.edit', compact('specificationGroup'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpecificationGroup $specificationGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:specification_groups,name,' . $specificationGroup->id
        ]);

        $specificationGroup->update($validated);

        return redirect()
            ->route('specification-groups.index')
            ->with('success', 'Specification Group berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpecificationGroup $specificationGroup)
    {
        if ($specificationGroup->specifications()->exists()) {
            return back()->withErrors('Group tidak bisa dihapus karena masih memiliki spesifikasi');
        }

        $specificationGroup->delete();

        return redirect()
            ->route('specification-groups.index')
            ->with('success', 'Specification Group berhasil dihapus');
    }
}
