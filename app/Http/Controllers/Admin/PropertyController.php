<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFormRequest;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $properties = Property::all();

        return view('pages.admin.property.index', [
            'properties' => $properties,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.property.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyFormRequest $request)
    {
        $data = $request->validated();

        Property::create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.property.index')->with('success', 'Bien créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        return view('pages.admin.property.edit', [
            'property' => $property,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PropertyFormRequest $request, Property $property)
    {
        $data = $request->validated();

        $property->update($data);

        return redirect()->route('admin.property.index')->with('success', 'Bien mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete($property->id);

        return redirect()->route('admin.property.index')->with('success', 'Bien supprimé avec succès.');
    }
}
