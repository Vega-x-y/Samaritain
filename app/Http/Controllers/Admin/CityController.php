<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(Request $request): View
    {
        $query = City::withCount('arrondissements');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        return view('pages.admin.configuration.city.index', [
            'cities' => $query->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.configuration.city.create');
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        City::create($request->validated());

        return redirect()->route('admin.configuration.city.index')
            ->with('success', 'La ville a été créée avec succès.');
    }

    public function edit(City $city): View
    {
        return view('pages.admin.configuration.city.edit', compact('city'));
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $city->update($request->validated());

        return redirect()->route('admin.configuration.city.index')
            ->with('success', 'La ville a été mise à jour avec succès.');
    }

    public function destroy(City $city): RedirectResponse
    {
        if ($city->arrondissements()->exists()) {
            return back()->with('error', 'Cette ville possède encore des arrondissements. Supprimez-les avant de supprimer la ville.');
        }

        $city->delete();

        return redirect()->route('admin.configuration.city.index')
            ->with('success', 'La ville a été supprimée avec succès.');
    }
}
