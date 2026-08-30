<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Document;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VisitPass;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $clientIds = $user->clients()->pluck('id');

        $chantiers = Chantier::query()
            ->whereIn('client_id', $clientIds)
            ->with(['artisan', 'client'])
            ->latest()
            ->take(5)
            ->get();

        $documents = Document::query()
            ->whereIn('client_id', $clientIds)
            ->latest()
            ->take(5)
            ->get();

        $visitPasses = VisitPass::query()
            ->where('user_id', $user->id)
            ->with('visitPassable')
            ->latest()
            ->take(3)
            ->get();

        $stats = [
            'total_chantiers' => Chantier::whereIn('client_id', $clientIds)->count(),
            'total_documents' => Document::whereIn('client_id', $clientIds)->count(),
            'chantiers_en_cours' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'en_cours')->count(),
            'chantiers_termines' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'termine')->count(),
            'chantiers_en_attente' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'attente')->count(),
            'chantiers_en_arret' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'arret')->count(),
        ];

        $messagesNonLus = Message::whereHas('conversation', fn ($q) => $q->where('client_id', $user->id))
            ->where('lu', false)
            ->where('expediteur_type', '!=', 'client')
            ->count();

        return view('pages.client.dashboard', compact('chantiers', 'documents', 'stats', 'messagesNonLus', 'visitPasses'));
    }

    public function chantiers(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $clientIds = $user->clients()->pluck('id');

        $chantiers = Chantier::query()
            ->whereIn('client_id', $clientIds)
            ->with(['artisan', 'client'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total_chantiers' => Chantier::whereIn('client_id', $clientIds)->count(),
            'chantiers_en_cours' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'en_cours')->count(),
            'chantiers_termines' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'termine')->count(),
            'chantiers_en_attente' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'attente')->count(),
            'chantiers_en_arret' => Chantier::whereIn('client_id', $clientIds)->where('statut', 'arret')->count(),
        ];

        return view('pages.client.chantiers.index', compact('chantiers', 'stats'));
    }

    public function transactions(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $transactions = Transaction::query()
            ->where('user_id', $user->id)
            ->with(['artisanRequest', 'visitPass', 'rentPayment'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_spent' => Transaction::where('user_id', $user->id)->where('status', 'COMPLETED')->sum('amount'),
            'total_count' => Transaction::where('user_id', $user->id)->count(),
            'completed_count' => Transaction::where('user_id', $user->id)->where('status', 'COMPLETED')->count(),
            'pending_count' => Transaction::where('user_id', $user->id)->where('status', 'PENDING')->count(),
        ];

        return view('pages.client.transactions.index', compact('transactions', 'stats'));
    }
}
