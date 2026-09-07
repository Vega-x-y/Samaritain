<?php

namespace App\Http\Controllers;

use App\Actions\UploadImage;
use App\Http\Requests\BoutiqueFormRequest;
use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\Boutique;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BoutiqueController extends Controller
{
    /**
     * Display a listing of the boutiques.
     */
    public function index(Request $request)
    {
        $query = Boutique::query()
            ->where('is_active', true)
            ->where('is_verify', true)
            ->with(['city', 'category', 'images', 'arrondissement']);

        foreach (['city_id', 'arrondissement_id', 'category_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }
        if ($request->filled('surface')) {
            $query->where('surface', '>=', $request->input('surface'));
        }
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhereHas('city', fn ($city) => $city->where('name', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('sort')) {
            match ($request->input('sort')) {
                'name_asc' => $query->orderBy('name', 'asc'),
                'name_desc' => $query->orderBy('name', 'desc'),
                'price_asc' => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                default => $query->latest(),
            };
        } else {
            $query->latest();
        }

        $boutiques = $query->paginate(15)->withQueryString();

        return view('pages.boutique.index', [
            'boutiques' => $boutiques,
            'cities' => City::select(['id', 'name'])->get(),
            'arrondissements' => Arrondissement::select(['id', 'name'])->get(),
            'categories' => Category::active()->select(['id', 'name'])->orderBy('sort_order')->get(),
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the dashboard for the user's boutiques.
     */
    public function dashboard()
    {
        $userId = Auth::id();
        $base = Boutique::where('created_by', $userId);

        $stats = [
            'total' => (clone $base)->count(),
            'verified' => (clone $base)->where('is_verify', true)->count(),
            'pending' => (clone $base)->where('is_verify', false)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
        ];

        $boutiques = (clone $base)->with(['city', 'category', 'images'])->latest()->paginate(10);

        return view('pages.boutique.dashboard', compact('boutiques', 'stats'));
    }

    /**
     * Show the form for creating a new boutique.
     */
    public function create()
    {
        return view('pages.boutique.create', $this->formData());
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
            'is_verify' => false,
            'is_active' => true,
            'conditions_accepted_at' => now(),
        ]);

        if ($request->filled('amenities')) {
            $boutique->amenities()->sync($request->validated('amenities'));
        }

        if ($request->hasFile('images')) {
            $storeImage->handle($boutique, $request->file('images'), 'images/boutiques');
        }

        return redirect()->route('boutique.dashboard')->with('success', 'Votre boutique a été créée avec succès.');
    }

    /**
     * Display the specified boutique.
     */
    public function show(Boutique $boutique)
    {
        Gate::authorize('view', $boutique);

        $boutique->load(['images', 'city', 'category', 'amenities', 'arrondissement', 'creator']);

        $similarBoutiques = Boutique::with(['images', 'city', 'category'])
            ->where('id', '!=', $boutique->id)
            ->where('is_active', true)
            ->where('is_verify', true)
            ->latest()
            ->take(4)
            ->get();

        return view('pages.boutique.show', compact('boutique', 'similarBoutiques'));
    }

    /**
     * Show the form for editing the specified boutique.
     */
    public function edit(Boutique $boutique)
    {
        Gate::authorize('update', $boutique);
        $boutique->load(['amenities', 'images']);

        return view('pages.boutique.edit', [...$this->formData(), 'boutique' => $boutique]);
    }

    /**
     * Update the specified boutique in storage.
     */
    public function update(BoutiqueFormRequest $request, Boutique $boutique, UploadImage $storeImage)
    {
        Gate::authorize('update', $boutique);

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

        return redirect()->route('boutique.show', $boutique)->with('success', 'Boutique mise à jour avec succès.');
    }

    /**
     * Remove the specified boutique from storage.
     */
    public function destroy(Boutique $boutique)
    {
        Gate::authorize('delete', $boutique);

        $boutique->images()->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });

        $boutique->delete();

        return redirect()->route('boutique.dashboard')->with('success', 'Boutique supprimée avec succès.');
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
