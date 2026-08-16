<?php

namespace App\Http\Controllers;

use App\Enums\ClientType;
use App\Http\Requests\StoreArtisanRequest;
use App\Http\Requests\UpdateArtisanRequest;
use App\Models\Arrondissement;
use App\Models\ArticleStock;
use App\Models\Artisan;
use App\Models\ArtisanCategory;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtisanController extends Controller
{
    public function index(Request $request)
    {
        $query = Artisan::query()->verified()->active()
            ->with('categories:id,name,slug')
            ->withCount('reviews')
            ->withAvg('reviews as average_rating', 'rating');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        if ($request->filled('arrondissement_id')) {
            $query->where('arrondissement_id', $request->arrondissement_id);
        }

        if ($request->filled('rating')) {
            $query->having('average_rating', '>=', $request->rating);
        }

        $artisans = $query->orderBy('average_rating', 'desc')
            ->paginate(12)
            ->withQueryString();

        $categories = ArtisanCategory::orderBy('id')->get();
        $cities = Artisan::verified()->active()->distinct()->pluck('city')->filter();
        $arrondissements = Arrondissement::with('city')->orderBy('name')->get();
        $count = $artisans->count();

        return view('pages.artisans.index', [
            'artisans' => $artisans,
            'categories' => $categories,
            'cities' => $cities,
            'arrondissements' => $arrondissements,
            'count' => $count,
        ]);
    }

    public function show(Artisan $artisan)
    {
        $artisan->load(['categories', 'arrondissement', 'projects' => function ($query) {
            $query->with('images')->latest()->limit(12);
        }, 'reviews' => function ($query) {
            $query->with('user:id,name,profile_image')->latest();
        }]);

        if (! auth()->check() || ! (auth()->user()->isAdmin() || auth()->id() === $artisan->user_id)) {
            $artisan->increment('views');
        }

        $userReview = null;
        $conversation = null;
        $messagesNonLus = 0;

        if (auth()->check()) {
            $userReview = $artisan->reviews()->where('user_id', auth()->id())->first();

            if (auth()->id() !== $artisan->user_id) {
                $conversation = $this->resolveConversation(auth()->user(), $artisan);

                if ($conversation) {
                    $messagesNonLus = $conversation->messages()
                        ->where('lu', false)
                        ->where('expediteur_type', '!=', 'client')
                        ->count();

                    $conversation->messages()
                        ->where('lu', false)
                        ->where('expediteur_type', '!=', 'client')
                        ->update(['lu' => true]);
                }
            }
        }

        return view('pages.artisans.show', compact('artisan', 'userReview', 'conversation', 'messagesNonLus'));
    }

    public function profileMessages(Request $request, Artisan $artisan)
    {
        abort_unless(auth()->check() && auth()->id() !== $artisan->user_id, 403);

        $conversation = $this->resolveConversation($request->user(), $artisan);

        if ($conversation) {
            $conversation->messages()
                ->where('lu', false)
                ->where('expediteur_type', '!=', 'client')
                ->update(['lu' => true]);

            $conversation->load('messages');
        }

        return view('pages.artisans.partials.profile-messages', compact('conversation'));
    }

    public function storeProfileMessage(Request $request, Artisan $artisan): RedirectResponse
    {
        abort_unless(auth()->check() && auth()->id() !== $artisan->user_id, 403);

        $validated = $request->validate([
            'contenu' => ['nullable', 'string', 'max:2000'],
            'fichier' => ['nullable', 'file', 'max:10240'],
            'document_id' => ['nullable', 'exists:documents,id'],
        ]);

        $client = $this->resolveClient($request->user(), $artisan);

        $conversation = Conversation::where('artisan_id', $artisan->id)
            ->where('client_id', $client->id)
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'artisan_id' => $artisan->id,
                'client_id' => $client->id,
                'lu' => false,
                'dernier_message_at' => now(),
            ]);
        }

        $data = [
            'expediteur_type' => 'client',
            'expediteur_id' => $client->id,
            'lu' => false,
            'document_id' => $validated['document_id'] ?? null,
        ];

        if (! empty($validated['contenu'])) {
            $data['contenu'] = $validated['contenu'];
        }

        if ($request->hasFile('fichier')) {
            $fichier = $request->file('fichier');
            $path = $fichier->store('messages/'.$conversation->id, 'r2');

            $data['fichier_path'] = $path;
            $data['fichier_nom'] = $fichier->getClientOriginalName();
            $data['fichier_mime'] = $fichier->getClientMimeType();
            $data['fichier_taille'] = $fichier->getSize();
        }

        if (empty($data['contenu']) && empty($data['fichier_path'])) {
            return back()->withErrors(['contenu' => 'Veuillez saisir un message ou joindre un fichier.']);
        }

        $conversation->messages()->create($data);
        $conversation->update(['dernier_message_at' => now(), 'lu' => false]);

        return back()->with('success', 'Message envoyé.');
    }

    /**
     * Retrouve la conversation liant un utilisateur connecté à un artisan (via son Client).
     */
    private function resolveConversation(User $user, Artisan $artisan): ?Conversation
    {
        $client = Client::where('user_id', $user->id)
            ->where('artisan_id', $artisan->id)
            ->first();

        if (! $client) {
            return null;
        }

        return Conversation::where('artisan_id', $artisan->id)
            ->where('client_id', $client->id)
            ->with(['messages' => fn ($q) => $q->latest()])
            ->first();
    }

    /**
     * Retrouve ou crée le Client liant un utilisateur connecté à un artisan.
     */
    private function resolveClient(User $user, Artisan $artisan): Client
    {
        return Client::firstOrCreate(
            ['user_id' => $user->id, 'artisan_id' => $artisan->id],
            ['nom' => $user->name, 'telephone' => '', 'email' => $user->email, 'type' => ClientType::PARTICULIER]
        );
    }

    public function create()
    {
        $categories = ArtisanCategory::active()->orderBy('sort_order')->orderBy('name')->get();
        $arrondissements = Arrondissement::with('city')->orderBy('name')->get();

        return view('pages.artisans.create', compact('categories', 'arrondissements'));
    }

    public function store(StoreArtisanRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($data['business_name']).'-'.Str::random(6);
        $data['verified'] = false;
        $data['is_active'] = false;

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('artisans/avatars');
        }

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('artisans/covers');
        }

        $categories = $data['categories'];
        unset($data['categories']);

        $artisan = Artisan::create($data);
        $artisan->categories()->sync($categories);

        return redirect()->route('artisan.dashboard')
            ->with('success', 'Votre profil artisan a été créé et est en attente de validation.');
    }

    public function edit(Artisan $artisan)
    {
        Gate::authorize('update', $artisan);

        $categories = ArtisanCategory::active()->orderBy('sort_order')->orderBy('name')->get();
        $selectedCategories = $artisan->categories->pluck('id')->toArray();
        $arrondissements = Arrondissement::with('city')->orderBy('name')->get();

        return view('pages.artisans.edit', compact('artisan', 'categories', 'selectedCategories', 'arrondissements'));
    }

    public function update(UpdateArtisanRequest $request, Artisan $artisan)
    {
        Gate::authorize('update', $artisan);

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($artisan->avatar) {
                Storage::delete($artisan->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('artisans/avatars');
        }

        if ($request->hasFile('cover')) {
            if ($artisan->cover) {
                Storage::delete($artisan->cover);
            }
            $data['cover'] = $request->file('cover')->store('artisans/covers');
        }

        $categories = $data['categories'];
        unset($data['categories']);

        $artisan->update($data);
        $artisan->categories()->sync($categories);

        return redirect()->route('artisan.dashboard')
            ->with('success', 'Votre profil a été mis à jour.');
    }

    public function profile()
    {
        $artisan = auth()->user()->artisan;

        if (! $artisan) {
            return redirect()->route('artisan.create');
        }

        $artisan->load(['categories', 'arrondissement.city']);

        return view('pages.artisan.profile.show', compact('artisan'));
    }

    public function reviews(Request $request)
    {
        $artisan = auth()->user()->artisan;

        if (! $artisan) {
            return redirect()->route('artisan.create');
        }

        $avis = $artisan->reviews()
            ->with('user:id,name,profile_image')
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('comment', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', fn ($sq) => $sq->where('name', 'like', '%'.$request->search.'%'));
            }))
            ->latest()
            ->paginate(20);

        return view('pages.artisan.reviews.index', compact('artisan', 'avis'));
    }

    public function dashboard(?Artisan $artisan = null)
    {
        // Si un artisan est passé en paramètre, l'utiliser
        // Sinon, utiliser l'artisan de l'utilisateur connecté
        if ($artisan) {
            // Admin viewing another artisan's dashboard - pas de vérification supplémentaire
        } else {
            $artisan = auth()->user()->artisan;

            if (! $artisan) {
                return redirect()->route('artisan.create');
            }
        }

        // Charger les relations nécessaires
        $artisan->load(['chantiers' => function ($query) {
            $query->with(['factures', 'depenses', 'documents']);
        }]);

        // 1. Projets en cours
        $projetsEnCours = $artisan->chantiers()
            ->where('statut', 'en_cours')
            ->count();

        // 2. Nombre total de clients enregistrés
        $clientsActifs = $artisan->clients()->count();

        // 3. CA Total = somme des budgets (FCFA HT) de tous les chantiers de l'artisan
        $caTotal = round((float) $artisan->chantiers()->sum('budget'), 2);

        // 4. Satisfaction (moyenne des avis)
        $satisfaction = $artisan->reviews()
            ->avg('rating');
        $satisfaction = $satisfaction ? round($satisfaction, 1) : 0;

        // 5. Stock critique (articles sous seuil minimum)
        $stockCritique = ArticleStock::where('artisan_id', $artisan->id)
            ->whereColumn('quantite', '<=', 'seuil_alerte')
            ->count();

        // 6. Messages non lus (toutes conversations confondues)
        $messagesNonLus = Message::whereHas('conversation', fn ($q) => $q->where('artisan_id', $artisan->id))
            ->where('lu', false)
            ->where('expediteur_type', '!=', 'artisan')
            ->count();

        $stats = [
            'projets_en_cours' => $projetsEnCours,
            'clients_actifs' => $clientsActifs,
            'ca_total' => $caTotal,
            'satisfaction' => $satisfaction,
            'stock_critique' => $stockCritique,
            'messages_non_lus' => $messagesNonLus,
        ];

        // Graphique CA sur 6 mois
        $ca6Mois = [];
        $labels6Mois = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels6Mois[] = $date->format('M Y');

            $ca = $artisan->chantiers()
                ->join('factures', 'artisan_chantiers.id', '=', 'factures.chantier_id')
                ->where('factures.statut', 'payee')
                ->whereMonth('factures.date_emission', $date->month)
                ->whereYear('factures.date_emission', $date->year)
                ->sum('factures.montant_ttc');

            $ca6Mois[] = $ca;
        }

        $recentReviews = $artisan->reviews()->with('user:id,name,profile_image')->latest()->limit(5)->get();
        $recentContacts = $artisan->contacts()->latest()->limit(5)->get();

        return view('pages.artisan.dashboard', compact(
            'artisan',
            'stats',
            'recentReviews',
            'recentContacts',
            'ca6Mois',
            'labels6Mois',
            'messagesNonLus'
        ));
    }
}
