<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index() {
        $properties = Property::paginate(21);

        return view('pages.property.index', [
            'properties' => $properties,
            'cities' => City::select(['id', 'name'])->get()
        ]);
    }
    
    public function show(Property $property)
    {
        $property->load([
            'images',
            'city',
            'category',
            'amenities',
        ]);

        return view('pages.property.show', [
            'property' => $property
        ]);
    }
}
