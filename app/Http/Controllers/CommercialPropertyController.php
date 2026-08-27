<?php

namespace App\Http\Controllers;

use App\Actions\UploadImage;
use App\Http\Requests\PropertyFormRequest;
use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\Category;
use App\Models\City;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CommercialPropertyController extends Controller
{
    public function index(Request $request, string $type)
    {
        abort_unless(in_array($type, ['boutique', 'bureau'], true), 404);

        $query = Property::query()->where('property_type', $type)
            ->where('is_active', true)->where('is_verify', true)
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
        if ($request->filled('rooms')) {
            $query->where('rooms', $request->input('rooms'));
        }
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($search) use ($keyword) {
                $search->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhereHas('city', fn ($city) => $city->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('amenities', fn ($amenity) => $amenity->where('name', 'like', "%{$keyword}%"));
            });
        }

        return view('pages.property.index', $this->listingData($request, $query->latest()->paginate(21)->withQueryString(), $type));
    }

    public function show(Request $request, Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        Gate::authorize('view', $property);
        $property->load(['images', 'city', 'category', 'amenities', 'arrondissement', 'creator']);

        return view('pages.property.show', [
            'property' => $property,
            'similarProperties' => Property::with(['images', 'city', 'category'])
                ->where('property_type', $type)->where('id', '!=', $property->id)
                ->where('is_active', true)->where('is_verify', true)->latest()->take(6)->get(),
            'propertyType' => $type,
        ]);
    }

    public function create(string $type)
    {
        return view('pages.property.create', $this->formData($type));
    }

    public function store(PropertyFormRequest $request, UploadImage $storeImage, string $type)
    {
        abort_unless(in_array($type, ['boutique', 'bureau'], true), 404);
        $request->merge(['property_type' => $type]);
        $data = $request->validated();
        $data['property_type'] = $type;
        unset($data['conditions']);
        $property = Property::create([...$data, 'created_by' => Auth::id(), 'is_verify' => false, 'is_active' => true, 'conditions_accepted_at' => now()]);
        $property->amenities()->sync($request->validated('amenities'));
        if ($request->hasFile('images')) {
            $storeImage->handle($property, $request->file('images'));
        }

        return redirect()->route("{$type}.dashboard")->with('success', "Votre {$this->label($type)} a été créé avec succès.");
    }

    public function edit(Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        Gate::authorize('update', $property);
        $property->load(['amenities', 'images']);

        return view('pages.property.edit', [...$this->formData($type), 'property' => $property]);
    }

    public function update(PropertyFormRequest $request, Property $property, UploadImage $storeImage, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        Gate::authorize('update', $property);
        $data = $request->validated();
        $data['property_type'] = $type;
        $property->update($data);
        $property->amenities()->sync($request->validated('amenities'));
        $keptIds = $request->input('kept_images', []);
        $property->images()->whereNotIn('id', $keptIds)->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });
        if ($request->hasFile('images')) {
            $storeImage->handle($property, $request->file('images'));
        }

        return redirect()->route("{$type}.dashboard")->with('success', "Votre {$this->label($type)} a été mis à jour.");
    }

    public function destroy(Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        Gate::authorize('delete', $property);
        $property->images()->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });
        $property->delete();

        return redirect()->route("{$type}.index")->with('success', "Votre {$this->label($type)} a été supprimé.");
    }

    public function dashboard(string $type)
    {
        $properties = Property::where('created_by', Auth::id())->where('property_type', $type)
            ->with(['city', 'category'])->latest()->paginate(10);
        $base = Property::where('created_by', Auth::id())->where('property_type', $type);
        $stats = ['total' => (clone $base)->count(), 'verified' => (clone $base)->where('is_verify', true)->count(), 'pending' => (clone $base)->where('is_verify', false)->count(), 'active' => (clone $base)->where('is_active', true)->count()];

        return view('pages.property.dashboard', compact('properties', 'stats') + ['propertyType' => $type]);
    }

    private function listingData(Request $request, $properties, string $type): array
    {
        return ['properties' => $properties, 'cities' => City::select(['id', 'name'])->get(), 'arrondissements' => Arrondissement::select(['id', 'name'])->get(), 'filters' => $request->all(), 'propertyType' => $type];
    }

    private function formData(string $type): array
    {
        return ['propertyType' => $type, 'categories' => Category::active()->select(['id', 'name'])->orderBy('sort_order')->get(), 'cities' => City::select(['id', 'name'])->get(), 'amenities' => Amenity::active()->select(['id', 'name'])->orderBy('sort_order')->get(), 'arrondissements' => Arrondissement::select(['id', 'name'])->get()];
    }

    private function label(string $type): string
    {
        return $type === 'boutique' ? 'boutique' : 'bureau';
    }
}
