<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $chantiers = Chantier::query()
            ->where('client_id', $user->id)
            ->with(['artisan', 'client'])
            ->latest()
            ->take(5)
            ->get();

        $documents = Document::query()
            ->where('client_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total_chantiers' => Chantier::where('client_id', $user->id)->count(),
            'total_documents' => Document::where('client_id', $user->id)->count(),
            'chantiers_en_cours' => Chantier::where('client_id', $user->id)->where('statut', 'en_cours')->count(),
            'chantiers_termines' => Chantier::where('client_id', $user->id)->where('statut', 'termine')->count(),
            'chantiers_en_attente' => Chantier::where('client_id', $user->id)->where('statut', 'attente')->count(),
            'chantiers_en_arret' => Chantier::where('client_id', $user->id)->where('statut', 'arret')->count(),
        ];

        $messagesNonLus = Message::whereHas('conversation', fn ($q) => $q->where('client_id', $user->id))
            ->where('lu', false)
            ->where('expediteur_type', '!=', 'client')
            ->count();

        return view('pages.client.dashboard', compact('chantiers', 'documents', 'stats', 'messagesNonLus'));
    }

    public function chantiers(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $chantiers = Chantier::query()
            ->where('client_id', $user->id)
            ->with(['artisan', 'client'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total_chantiers' => Chantier::where('client_id', $user->id)->count(),
            'chantiers_en_cours' => Chantier::where('client_id', $user->id)->where('statut', 'en_cours')->count(),
            'chantiers_termines' => Chantier::where('client_id', $user->id)->where('statut', 'termine')->count(),
            'chantiers_en_attente' => Chantier::where('client_id', $user->id)->where('statut', 'attente')->count(),
            'chantiers_en_arret' => Chantier::where('client_id', $user->id)->where('statut', 'arret')->count(),
        ];

        return view('pages.client.chantiers.index', compact('chantiers', 'stats'));
    }
}
