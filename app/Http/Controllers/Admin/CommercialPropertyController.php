<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UploadImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFormRequest;
use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\Category;
use App\Models\City;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommercialPropertyController extends Controller
{
    public function index(string $type)
    {
        return view('pages.admin.property.index', ['properties' => Property::where('property_type', $type)->with(['city', 'category'])->paginate(10), 'propertyType' => $type]);
    }

    public function show(Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        $property->load(['amenities', 'images', 'category', 'city', 'arrondissement', 'creator']);

        return view('pages.admin.property.show', compact('property') + ['propertyType' => $type]);
    }

    public function create(string $type)
    {
        return view('pages.admin.property.create', $this->formData($type));
    }

    public function store(PropertyFormRequest $request, UploadImage $storeImage, string $type)
    {
        $data = $request->validated();
        $data['property_type'] = $type;
        unset($data['conditions']);
        $property = Property::create([...$data, 'created_by' => Auth::id()]);
        $property->amenities()->sync($request->validated('amenities'));
        if ($request->hasFile('images')) {
            $storeImage->handle($property, $request->file('images'));
        }

        return redirect()->route("admin.{$type}.index")->with('success', 'Le bien a été créé avec succès.');
    }

    public function edit(Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        $property->load(['amenities', 'images']);

        return view('pages.admin.property.edit', [...$this->formData($type), 'property' => $property]);
    }

    public function update(PropertyFormRequest $request, Property $property, UploadImage $storeImage, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
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

        return redirect()->route("admin.{$type}.index")->with('success', 'Le bien a été mis à jour.');
    }

    public function destroy(Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        $property->images()->get()->each(function ($image) {
            Storage::delete($image->getRawOriginal('image_url'));
            $image->delete();
        });
        $property->delete();

        return redirect()->route("admin.{$type}.index");
    }

    public function toggleActive(Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        $property->update(['is_active' => ! $property->is_active]);

        return redirect()->route("admin.{$type}.index");
    }

    public function toggleVerify(Property $property, string $type)
    {
        abort_unless($property->property_type?->value === $type, 404);
        $property->update(['is_verify' => ! $property->is_verify]);

        return redirect()->route("admin.{$type}.index");
    }

    private function formData(string $type): array
    {
        return ['propertyType' => $type, 'categories' => Category::active()->select(['id', 'name'])->orderBy('sort_order')->get(), 'cities' => City::select(['id', 'name'])->get(), 'amenities' => Amenity::active()->select(['id', 'name'])->orderBy('sort_order')->get(), 'arrondissements' => Arrondissement::select(['id', 'name'])->get()];
    }
}
