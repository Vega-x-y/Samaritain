<?php

namespace App\Http\Controllers;

use App\Actions\UploadHotelImage;
use App\Http\Requests\HotelFormRequest;
use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\City;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelController extends Controller
{
    /**
     * Display a listing of hotels for public view.
     */
    public function index(Request $request)
    {
        $query = Hotel::query()
            ->where('is_active', true)
            ->where('is_verify', true)
            ->with(['city', 'images', 'arrondissement']);

        // Appliquer les filtres si présents
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('arrondissement_id')) {
            $query->where('arrondissement_id', $request->arrondissement_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        if ($request->filled('star_rating')) {
            $query->where('star_rating', $request->star_rating);
        }

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->keyword.'%')
                    ->orWhere('description', 'like', '%'.$request->keyword.'%')
                    ->orWhere('address', 'like', '%'.$request->keyword.'%')
                    ->orWhereHas('city', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%'.$request->keyword.'%');
                    })
                    ->orWhereHas('arrondissement', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%'.$request->keyword.'%');
                    })
                    ->orWhereHas('amenities', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%'.$request->keyword.'%');
                    });
            });
        }

        $hotels = $query->latest()->paginate(21)->withQueryString();

        return view('pages.hotel.index', [
            'hotels' => $hotels,
            'cities' => City::select(['id', 'name'])->get(),
            'arrondissements' => Arrondissement::select(['id', 'name'])->get(),
        ]);
    }

    /**
     * Display the specified hotel.
     */
    public function show(Request $request, Hotel $hotel)
    {
        Gate::authorize('view', $hotel);

        $this->registerView($request, $hotel);

        $hotel->load([
            'images',
            'city',
            'amenities',
            'arrondissement',
            'creator',
        ]);

        $similarHotels = Hotel::with([
            'images',
            'city',
        ])
            ->where('id', '!=', $hotel->id)
            ->where('is_active', true)
            ->where('is_verify', true)
            ->where('city_id', $hotel->city_id)
            ->latest()
            ->take(6)
            ->get();

        return view('pages.hotel.show', [
            'hotel' => $hotel,
            'similarHotels' => $similarHotels,
        ]);
    }

    public function create()
    {
        return view('pages.hotel.create', [
            'cities' => City::select(['id', 'name'])->get(),
            'amenities' => Amenity::select(['id', 'name'])->get(),
            'arrondissements' => Arrondissement::select(['id', 'name'])->get(),
        ]);
    }

    /**
     * Store a newly created hotel in storage.
     */
    public function store(HotelFormRequest $request, UploadHotelImage $storeImage)
    {
        $data = $request->validated();
        unset($data['conditions']);

        $hotel = Hotel::create([
            ...$data,
            'created_by' => Auth::id(),
            'is_verify' => false, // Par défaut, non vérifié
            'is_active' => true,   // Par défaut, actif
            'conditions_accepted_at' => now(),
        ]);

        $hotel->amenities()->sync($request->validated('amenities'));

        if ($request->hasFile('images')) {
            $storeImage->handle($hotel, $request->file('images'));
        }

        return redirect()->route('hotel.dashboard')
            ->with('success', 'Votre hôtel a été créé avec succès. Il sera visible après validation par un administrateur.');
    }

    /**
     * Show the form for editing the specified hotel.
     */
    public function edit(Hotel $hotel)
    {
        Gate::authorize('update', $hotel);

        $hotel->load(['amenities', 'images']);

        return view('pages.hotel.edit', [
            'hotel' => $hotel,
            'cities' => City::select(['id', 'name'])->get(),
            'amenities' => Amenity::select(['id', 'name'])->get(),
            'arrondissements' => Arrondissement::select(['id', 'name'])->get(),
        ]);
    }

    /**
     * Update the specified hotel in storage.
     */
    public function update(HotelFormRequest $request, Hotel $hotel, UploadHotelImage $storeImage)
    {
        Gate::authorize('update', $hotel);

        $hotel->update($request->validated());
        $hotel->amenities()->sync($request->validated('amenities'));

        // Après modification, l'hôtel redevient non vérifié (nécessite une nouvelle validation)
        $hotel->update(['is_verify' => false]);

        // Supprimer uniquement les images non conservées
        $keptIds = $request->input('kept_images', []);

        $hotel->images()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(function ($image) {
                Storage::delete($image->getRawOriginal('image_url'));
                $image->delete();
            });

        if ($request->hasFile('images')) {
            $storeImage->handle($hotel, $request->file('images'));
        }

        return redirect()->route('hotel.dashboard')
            ->with('success', 'Votre hôtel a été mis à jour avec succès. Il sera de nouveau vérifié par un administrateur.');
    }

    /**
     * Remove the specified hotel from storage.
     */
    public function destroy(Hotel $hotel)
    {
        Gate::authorize('delete', $hotel);

        $hotel->images()
            ->get()
            ->each(function ($image) {
                Storage::delete($image->getRawOriginal('image_url'));
                $image->delete();
            });

        $hotel->delete();

        return redirect()->route('hotel.index')
            ->with('success', 'Votre hôtel a été supprimé avec succès.');
    }

    /**
     * Display hotels for a specific city (filter)
     */
    public function byCity(City $city)
    {
        $hotels = Hotel::where('city_id', $city->id)
            ->where('is_active', true)
            ->where('is_verify', true)
            ->with(['city'])
            ->latest()
            ->paginate(21);

        return view('pages.hotel.index', [
            'hotels' => $hotels,
            'cities' => City::select(['id', 'name'])->get(),
            'selectedCity' => $city,
        ]);
    }

    /**
     * Display hotels by category
     */

    /**
     * Search hotels
     */
    public function search(Request $request)
    {
        $query = Hotel::query()
            ->where('is_active', true)
            ->where('is_verify', true);

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('arrondissement_id')) {
            $query->where('arrondissement_id', $request->arrondissement_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        if ($request->filled('star_rating')) {
            $query->where('star_rating', $request->star_rating);
        }

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->keyword.'%')
                    ->orWhere('description', 'like', '%'.$request->keyword.'%')
                    ->orWhere('address', 'like', '%'.$request->keyword.'%')
                    ->orWhereHas('city', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%'.$request->keyword.'%');
                    })
                    ->orWhereHas('arrondissement', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%'.$request->keyword.'%');
                    })
                    ->orWhereHas('amenities', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%'.$request->keyword.'%');
                    });
            });
        }

        $hotels = $query->with(['city', 'arrondissement', 'images'])
            ->latest()
            ->paginate(21)
            ->withQueryString();

        return view('pages.hotel.index', [
            'hotels' => $hotels,
            'cities' => City::select(['id', 'name'])->get(),
            'arrondissements' => Arrondissement::select(['id', 'name'])->get(),
            'filters' => $request->all(),
        ]);
    }

    /**
     * Display user's dashboard with their hotels
     */
    public function dashboard()
    {
        $hotels = Hotel::where('created_by', Auth::id())
            ->with(['city'])
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Hotel::where('created_by', Auth::id())->count(),
            'verified' => Hotel::where('created_by', Auth::id())->where('is_verify', true)->count(),
            'pending' => Hotel::where('created_by', Auth::id())->where('is_verify', false)->count(),
            'active' => Hotel::where('created_by', Auth::id())->where('is_active', true)->count(),
        ];

        return view('pages.hotel.dashboard', [
            'hotels' => $hotels,
            'stats' => $stats,
        ]);
    }

    /**
     * Duplicate a hotel (for quick creation)
     */
    public function duplicate(Hotel $hotel, UploadHotelImage $storeImage)
    {
        Gate::authorize('update', $hotel);

        // Créer une copie
        $newHotel = $hotel->replicate();
        $newHotel->title = $hotel->title.' (Copie)';
        $newHotel->slug = $this->generateUniqueSlug($newHotel->title);
        $newHotel->is_verify = false;
        $newHotel->created_by = Auth::id();
        $newHotel->save();

        // Copier les amenities
        $newHotel->amenities()->sync($hotel->amenities->pluck('id')->toArray());

        // Copier les images
        foreach ($hotel->images as $image) {
            $oldPath = $image->getRawOriginal('image_url');
            $newPath = 'images/hotels/'.uniqid('hotel_', true).'.jpg';

            // Copier le fichier
            if (Storage::exists($oldPath)) {
                Storage::copy($oldPath, $newPath);

                // Créer la nouvelle image
                $newHotel->images()->create([
                    'image_url' => $newPath,
                ]);
            }
        }

        return redirect()->route('hotel.edit', $newHotel)
            ->with('success', 'Hôtel dupliqué avec succès. Vous pouvez maintenant modifier les informations.');
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Hotel::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Enregistre une vue si elle n'a pas déjà été comptée dans les dernières 24h
     * pour cet IP et cet hôtel.
     */
    protected function registerView(Request $request, Hotel $hotel): void
    {
        $cacheKey = 'hotel_view_'.$hotel->id.'_'.$request->ip();

        // Cache::add() retourne true si la clé a été ajoutée (donc n'existait pas)
        if (Cache::add($cacheKey, true, now()->addHours(24))) {
            $hotel->incrementViews();
        }
    }
}
