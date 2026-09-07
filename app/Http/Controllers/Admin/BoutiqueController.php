<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UploadImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\BoutiqueFormRequest;
use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\Boutique;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BoutiqueController extends Controller
{
    /**
     * Display a listing of boutiques in admin.
     */
    public function index()
    {
        $boutiques = Boutique::with(['city', 'category', 'creator'])->latest()->paginate(15);

        return view('pages.admin.boutique.index', compact('boutiques'));
    }

    /**
     * Show the form for creating a new boutique.
     */
    public function create()
    {
        return view('pages.admin.boutique.create', $this->formData());
    }

    /**
     * Store a newly created boutique in storage.
     */
    public function store(BoutiqueFormRequest $request, UploadImage $storeImage)
    {
        $data = $request->validated();
        unset($data['conditions'], $data['amenities'], $data['images']);

        $boutique = Boutique::create([
            ...$data,
            'created_by' => Auth::id(),
            'is_verify' => $request->boolean('is_verify', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->filled('amenities')) {
            $boutique->amenities()->sync($request->validated('amenities'));
        }

        if ($request->hasFile('images')) {
            $storeImage->handle($boutique, $request->file('images'), 'images/boutiques');
        }

        return redirect()->route('admin.boutique.index')->with('success', 'Boutique créée avec succès.');
    }

    /**
     * Display the specified boutique.
     */
    public function show(Boutique $boutique)
    {
        $boutique->load(['amenities', 'images', 'category', 'city', 'arrondissement', 'creator']);

        return view('pages.admin.boutique.show', compact('boutique'));
    }

    /**
     * Show the form for editing the specified boutique.
     */
    public function edit(Boutique $boutique)
    {
        $boutique->load(['amenities', 'images']);

        return view('pages.admin.boutique.edit', [...$this->formData(), 'boutique' => $boutique]);
    }

    /**
     * Update the specified boutique in storage.
     */
    public function update(BoutiqueFormRequest $request, Boutique $boutique, UploadImage $storeImage)
    {
        $data = $request->validated();
        unset($data['conditions'], $data['amenities'], $data['images'], $data['kept_images']);

        $boutique->update($data);

        if ($request->has('amenities')) {
            $boutique->amenities()->sync($request->validated('amenities', []));
        }

        $keptIds = $request->input('kept_images', []);
        $boutique->images()->whereNotIn('id', $keptIds)->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });

        if ($request->hasFile('images')) {
            $storeImage->handle($boutique, $request->file('images'), 'images/boutiques');
        }

        return redirect()->route('admin.boutique.index')->with('success', 'Boutique mise à jour avec succès.');
    }

    /**
     * Remove the specified boutique from storage.
     */
    public function destroy(Boutique $boutique)
    {
        $boutique->images()->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });

        $boutique->delete();

        return redirect()->route('admin.boutique.index')->with('success', 'Boutique supprimée avec succès.');
    }

    public function toggleActive(Boutique $boutique)
    {
        $boutique->update(['is_active' => ! $boutique->is_active]);

        return redirect()->route('admin.boutique.index')->with('success', 'Statut actif mis à jour.');
    }

    public function toggleVerify(Boutique $boutique)
    {
        $boutique->update(['is_verify' => ! $boutique->is_verify]);

        return redirect()->route('admin.boutique.index')->with('success', 'Statut de vérification mis à jour.');
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
