<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArrondissementRequest;
use App\Http\Requests\Admin\UpdateArrondissementRequest;
use App\Models\Arrondissement;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArrondissementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Arrondissement::with('city');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhereHas('city', fn ($cityQuery) => $cityQuery->where('name', 'like', '%'.$search.'%'));
            });
        }

        return view('pages.admin.configuration.arrondissement.index', [
            'arrondissements' => $query->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.configuration.arrondissement.create', [
            'cities' => City::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(StoreArrondissementRequest $request): RedirectResponse
    {
        Arrondissement::create($request->validated());

        return redirect()->route('admin.configuration.arrondissement.index')
            ->with('success', 'L\'arrondissement a été créé avec succès.');
    }

    public function edit(Arrondissement $arrondissement): View
    {
        return view('pages.admin.configuration.arrondissement.edit', [
            'arrondissement' => $arrondissement,
            'cities' => City::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(UpdateArrondissementRequest $request, Arrondissement $arrondissement): RedirectResponse
    {
        $arrondissement->update($request->validated());

        return redirect()->route('admin.configuration.arrondissement.index')
            ->with('success', 'L\'arrondissement a été mis à jour avec succès.');
    }

    public function destroy(Arrondissement $arrondissement): RedirectResponse
    {
        $arrondissement->delete();

        return redirect()->route('admin.configuration.arrondissement.index')
            ->with('success', 'L\'arrondissement a été supprimé avec succès.');
    }
}
