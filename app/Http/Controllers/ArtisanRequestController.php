<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\ArtisanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArtisanRequestController extends Controller
{
    public function index(Request $request)
    {
        $artisan = auth()->user()->artisan;

        if (! $artisan) {
            return redirect()->route('artisan.create');
        }

        $demandes = $artisan->demandesRecues()
            ->with('user')
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('message', 'like', '%'.$request->search.'%')
                    ->orWhere('type', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', fn ($sq) => $sq->where('name', 'like', '%'.$request->search.'%'));
            }))
            ->latest()
            ->paginate(20);

        return view('pages.artisan.requests.index', compact('artisan', 'demandes'));
    }

    public function store(Request $request, Artisan $artisan)
    {
        $request->validate([
            'type' => 'required|in:information,devis,rendez-vous',
            'message' => 'required|string|max:1000',
        ]);

        // Vérifier si l'utilisateur a déjà fait une demande récemment
        $existingRequest = ArtisanRequest::where('artisan_id', $artisan->id)
            ->where('user_id', auth()->id())
            ->where('created_at', '>', now()->subDays(7))
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'Vous avez déjà fait une demande récemment. Veuillez attendre 7 jours avant de faire une nouvelle demande.');
        }

        ArtisanRequest::create([
            'artisan_id' => $artisan->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'message' => $request->message,
            'statut' => 'en_attente',
        ]);

        return back()->with('success', 'Votre demande a été envoyée avec succès. L\'artisan vous répondra bientôt.');
    }

    public function update(Request $request, ArtisanRequest $demande)
    {
        Gate::authorize('update', $demande->artisan);

        $request->validate([
            'statut' => 'required|in:acceptee,refusee',
            'reponse' => 'required|string|max:1000',
        ]);

        $demande->update([
            'statut' => $request->statut,
            'reponse' => $request->reponse,
            'date_reponse' => now(),
        ]);

        return back()->with('success', 'La demande a été '.($request->statut === 'acceptee' ? 'acceptée' : 'refusée').' avec succès.');
    }

    public function destroy(ArtisanRequest $demande)
    {
        Gate::authorize('delete', $demande->artisan);

        $demande->delete();

        return back()->with('success', 'La demande a été supprimée.');
    }
}
