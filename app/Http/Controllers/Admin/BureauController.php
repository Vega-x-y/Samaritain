<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UploadImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\BureauFormRequest;
use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\Bureau;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BureauController extends Controller
{
    /**
     * Display a listing of bureaux in admin.
     */
    public function index()
    {
        $bureaus = Bureau::with(['city', 'category', 'creator'])->latest()->paginate(15);

        return view('pages.admin.bureau.index', compact('bureaus'));
    }

    /**
     * Show the form for creating a new bureau.
     */
    public function create()
    {
        return view('pages.admin.bureau.create', $this->formData());
    }

    /**
     * Store a newly created bureau in storage.
     */
    public function store(BureauFormRequest $request, UploadImage $storeImage)
    {
        $data = $request->validated();
        unset($data['conditions'], $data['amenities'], $data['images']);

        $bureau = Bureau::create([
            ...$data,
            'created_by' => Auth::id(),
            'is_verify' => $request->boolean('is_verify', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->filled('amenities')) {
            $bureau->amenities()->sync($request->validated('amenities'));
        }

        if ($request->hasFile('images')) {
            $storeImage->handle($bureau, $request->file('images'), 'images/bureaux');
        }

        return redirect()->route('admin.bureau.index')->with('success', 'Bureau créé avec succès.');
    }

    /**
     * Display the specified bureau.
     */
    public function show(Bureau $bureau)
    {
        $bureau->load(['amenities', 'images', 'category', 'city', 'arrondissement', 'creator']);

        return view('pages.admin.bureau.show', compact('bureau'));
    }

    /**
     * Show the form for editing the specified bureau.
     */
    public function edit(Bureau $bureau)
    {
        $bureau->load(['amenities', 'images']);

        return view('pages.admin.bureau.edit', [...$this->formData(), 'bureau' => $bureau]);
    }

    /**
     * Update the specified bureau in storage.
     */
    public function update(BureauFormRequest $request, Bureau $bureau, UploadImage $storeImage)
    {
        $data = $request->validated();
        unset($data['conditions'], $data['amenities'], $data['images'], $data['kept_images']);

        $bureau->update($data);

        if ($request->has('amenities')) {
            $bureau->amenities()->sync($request->validated('amenities', []));
        }

        $keptIds = $request->input('kept_images', []);
        $bureau->images()->whereNotIn('id', $keptIds)->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });

        if ($request->hasFile('images')) {
            $storeImage->handle($bureau, $request->file('images'), 'images/bureaux');
        }

        return redirect()->route('admin.bureau.index')->with('success', 'Bureau mis à jour avec succès.');
    }

    /**
     * Remove the specified bureau from storage.
     */
    public function destroy(Bureau $bureau)
    {
        $bureau->images()->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });

        $bureau->delete();

        return redirect()->route('admin.bureau.index')->with('success', 'Bureau supprimé avec succès.');
    }

    public function toggleActive(Bureau $bureau)
    {
        $bureau->update(['is_active' => ! $bureau->is_active]);

        return redirect()->route('admin.bureau.index')->with('success', 'Statut actif mis à jour.');
    }

    public function toggleVerify(Bureau $bureau)
    {
        $bureau->update(['is_verify' => ! $bureau->is_verify]);

        return redirect()->route('admin.bureau.index')->with('success', 'Statut de vérification mis à jour.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::active()->select(['id', 'name'])->orderBy('sort_order')->get(),
            'cities' => City::select(['id', 'name'])->get(),
            'amenities' => Amenity::active()->select(['id', 'name'])->orderBy('sort_order')->get(),
            'arrondissements' => Arrondissement::select(['id', 'name'])->get(),
        ];
    }
}
