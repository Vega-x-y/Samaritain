<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtisanRequest;
use App\Http\Requests\UpdateArtisanRequest;
use App\Models\Arrondissement;
use App\Models\ArticleStock;
use App\Models\Artisan;
use App\Models\ArtisanCategory;
use App\Models\Message;
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
        if (auth()->check()) {
            $userReview = $artisan->reviews()->where('user_id', auth()->id())->first();
        }

        return view('pages.artisans.show', compact('artisan', 'userReview'));
    }

    public function create()
    {
        $categories = ArtisanCategory::orderBy('name')->get();
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

        $categories = ArtisanCategory::orderBy('name')->get();
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

    public function reviews()
    {
        $artisan = auth()->user()->artisan;

        if (! $artisan) {
            return redirect()->route('artisan.create');
        }

        $avis = $artisan->reviews()
            ->with('user:id,name,profile_image')
            ->latest()
            ->paginate(20);

        return view('pages.artisan.reviews.index', compact('artisan', 'avis'));
    }

    public function dashboard(Artisan $artisan = null)
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

        // 3. CA du mois courant
        $caMois = $artisan->chantiers()
            ->join('factures', 'artisan_chantiers.id', '=', 'factures.chantier_id')
            ->where('factures.statut', 'payee')
            ->whereMonth('factures.date_emission', now()->month)
            ->whereYear('factures.date_emission', now()->year)
            ->sum('factures.montant_ttc');

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
            'ca_mois' => $caMois,
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
