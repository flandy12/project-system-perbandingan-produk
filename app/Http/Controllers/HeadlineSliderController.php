<?php

namespace App\Http\Controllers;

use App\Models\HeadlineSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeadlineSliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headlines = HeadlineSlide::orderBy('position')->get();
        return view('pages.headline-slide.index', compact('headlines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'subtitle'  => 'required|string',
            'link'      => 'nullable|url',
            'image'     => 'required|image|max:2048',
            'position'  => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $data['image'] = $request->file('image')
            ->store('headline', 'public');

        HeadlineSlide::create($data);

        return back()->with('success', 'Headline berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HeadlineSlide $headlineSlide)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'subtitle'  => 'nullable|string',
            'link'      => 'nullable|url',
            'image'     => 'nullable|image|max:2048',
            'position'  => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {

            // hapus image lama
            if ($headlineSlide->image && Storage::disk('public')->exists($headlineSlide->image)) {
                Storage::disk('public')->delete($headlineSlide->image);
            }

            $data['image'] = $request->file('image')
                ->store('headlines', 'public');
        } else {
            // ⬅️ PENTING: jangan update kolom image
            unset($data['image']);
        }

        $headlineSlide->update($data);

        return back()->with('success', 'Headline berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HeadlineSlide $headlineSlide)
    {
        Storage::disk('public')->delete($headlineSlide->image);
        $headlineSlide->delete();

        return back()->with('success', 'Headline berhasil dihapus');
    }
}
