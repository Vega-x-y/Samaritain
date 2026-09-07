<?php

namespace App\Http\Controllers;

use App\Actions\UploadImage;
use App\Http\Requests\BureauFormRequest;
use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\Bureau;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BureauController extends Controller
{
    /**
     * Display a listing of the bureaux.
     */
    public function index(Request $request)
    {
        $query = Bureau::query()
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

        $bureaus = $query->paginate(15)->withQueryString();

        return view('pages.bureau.index', [
            'bureaus' => $bureaus,
            'cities' => City::select(['id', 'name'])->get(),
            'arrondissements' => Arrondissement::select(['id', 'name'])->get(),
            'categories' => Category::active()->select(['id', 'name'])->orderBy('sort_order')->get(),
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the dashboard for the user's bureaux.
     */
    public function dashboard()
    {
        $userId = Auth::id();
        $base = Bureau::where('created_by', $userId);

        $stats = [
            'total' => (clone $base)->count(),
            'verified' => (clone $base)->where('is_verify', true)->count(),
            'pending' => (clone $base)->where('is_verify', false)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
        ];

        $bureaus = (clone $base)->with(['city', 'category', 'images'])->latest()->paginate(10);

        return view('pages.bureau.dashboard', compact('bureaus', 'stats'));
    }

    /**
     * Show the form for creating a new bureau.
     */
    public function create()
    {
        return view('pages.bureau.create', $this->formData());
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
            'is_verify' => false,
            'is_active' => true,
            'conditions_accepted_at' => now(),
        ]);

        if ($request->filled('amenities')) {
            $bureau->amenities()->sync($request->validated('amenities'));
        }

        if ($request->hasFile('images')) {
            $storeImage->handle($bureau, $request->file('images'), 'images/bureaux');
        }

        return redirect()->route('bureau.dashboard')->with('success', 'Votre bureau a été créé avec succès.');
    }

    /**
     * Display the specified bureau.
     */
    public function show(Bureau $bureau)
    {
        Gate::authorize('view', $bureau);

        $bureau->load(['images', 'city', 'category', 'amenities', 'arrondissement', 'creator']);

        $similarBureaux = Bureau::with(['images', 'city', 'category'])
            ->where('id', '!=', $bureau->id)
            ->where('is_active', true)
            ->where('is_verify', true)
            ->latest()
            ->take(4)
            ->get();

        return view('pages.bureau.show', compact('bureau', 'similarBureaux'));
    }

    /**
     * Show the form for editing the specified bureau.
     */
    public function edit(Bureau $bureau)
    {
        Gate::authorize('update', $bureau);
        $bureau->load(['amenities', 'images']);

        return view('pages.bureau.edit', [...$this->formData(), 'bureau' => $bureau]);
    }

    /**
     * Update the specified bureau in storage.
     */
    public function update(BureauFormRequest $request, Bureau $bureau, UploadImage $storeImage)
    {
        Gate::authorize('update', $bureau);

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

        return redirect()->route('bureau.show', $bureau)->with('success', 'Bureau mis à jour avec succès.');
    }

    /**
     * Remove the specified bureau from storage.
     */
    public function destroy(Bureau $bureau)
    {
        Gate::authorize('delete', $bureau);

        $bureau->images()->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });

        $bureau->delete();

        return redirect()->route('bureau.dashboard')->with('success', 'Bureau supprimé avec succès.');
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
