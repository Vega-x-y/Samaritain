<?php

use App\Http\Controllers\Admin\ArtisanController as AdminArtisanController;
use App\Http\Controllers\Admin\ArtisanProjectController as AdminArtisanProjectController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ParcelleController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\AgencyContactController;
use App\Http\Controllers\Artisan\ChantierController;
use App\Http\Controllers\Artisan\ClientController;
use App\Http\Controllers\Artisan\DocumentController;
use App\Http\Controllers\Artisan\EvenementController;
use App\Http\Controllers\Artisan\FinancesController;
use App\Http\Controllers\Artisan\MembreEquipeController;
use App\Http\Controllers\Artisan\MessagerieController;
use App\Http\Controllers\Artisan\StockController;
use App\Http\Controllers\ArtisanContactController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\ArtisanProjectController;
use App\Http\Controllers\ArtisanRequestController;
use App\Http\Controllers\ArtisanReviewController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\ClientDocumentController;
use App\Http\Controllers\ClientMessagerieController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParcelleWebController;
use App\Http\Controllers\PassController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\Socialite\ProviderCallbackController;
use App\Http\Controllers\Socialite\ProviderRedirectController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserVisitPassController;
use App\Http\Controllers\VisitRequestController;
use App\Http\Middleware\StaffMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('index');

// Routes publiques pour les biens (utilisateur)
Route::get('properties', [PropertyController::class, 'index'])->name('property.index');
Route::get('properties/search', [PropertyController::class, 'search'])->name('property.search');
Route::get('properties/city/{city}', [PropertyController::class, 'byCity'])->name('property.byCity');
Route::get('properties/category/{category}', [PropertyController::class, 'byCategory'])->name('property.byCategory');

// Routes publiques pour les hôtels
Route::get('hotels', [HotelController::class, 'index'])->name('hotel.index');
Route::get('hotels/search', [HotelController::class, 'search'])->name('hotel.search');
Route::get('hotels/city/{city}', [HotelController::class, 'byCity'])->name('hotel.byCity');
Route::get('hotels/category/{category}', [HotelController::class, 'byCategory'])->name('hotel.byCategory');

// Routes protégées pour les biens (CRUD utilisateur)
Route::middleware(['auth', 'verified'])->group(function () {
    // Profil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::put('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.update-photo');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard client
    Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

    // Messagerie client
    Route::get('/client/messagerie', [ClientMessagerieController::class, 'index'])->name('client.messagerie.index');
    Route::get('/client/messagerie/create', [ClientMessagerieController::class, 'create'])->name('client.messagerie.create');
    Route::post('/client/messagerie', [ClientMessagerieController::class, 'storeConversation'])->name('client.messagerie.store');
    Route::delete('/client/messagerie/all', [ClientMessagerieController::class, 'destroyAllConversations'])->name('client.messagerie.destroy-all');
    Route::get('/client/messagerie/{conversation}', [ClientMessagerieController::class, 'show'])->name('client.messagerie.show');
    Route::post('/client/messagerie/{conversation}/message', [ClientMessagerieController::class, 'storeMessage'])->name('client.messagerie.message');
    Route::delete('/client/messagerie/message/{message}', [ClientMessagerieController::class, 'destroyMessage'])->name('client.messagerie.message.destroy');
    Route::delete('/client/messagerie/{conversation}', [ClientMessagerieController::class, 'destroyConversation'])->name('client.messagerie.destroy');

    // Chantiers client
    Route::get('/client/chantiers', [ClientDashboardController::class, 'chantiers'])->name('client.chantiers.index');

    // Documents client
    Route::get('/client/documents', [ClientDocumentController::class, 'index'])->name('client.documents.index');
    Route::get('/client/documents/{document}', [ClientDocumentController::class, 'show'])->name('client.documents.show');
    Route::post('/client/documents/{document}/return', [ClientDocumentController::class, 'returnDevis'])->name('client.documents.return');

    Route::get('my-properties/dashboard', [PropertyController::class, 'dashboard'])->name('property.dashboard');
    Route::post('property/{property}/duplicate', [PropertyController::class, 'duplicate'])->name('property.duplicate');

    // CRUD utilisateur
    Route::get('property/create', [PropertyController::class, 'create'])->name('property.create');
    Route::post('property', [PropertyController::class, 'store'])->name('property.store');
    Route::get('property/{property}/edit', [PropertyController::class, 'edit'])->name('property.edit');
    Route::put('property/{property}', [PropertyController::class, 'update'])->name('property.update');
    Route::delete('property/{property}', [PropertyController::class, 'destroy'])->name('property.destroy');

    // CRUD hôtels
    Route::get('my-hotels/dashboard', [HotelController::class, 'dashboard'])->name('hotel.dashboard');
    Route::post('hotel/{hotel}/duplicate', [HotelController::class, 'duplicate'])->name('hotel.duplicate');
    Route::get('hotel/create', [HotelController::class, 'create'])->name('hotel.create');
    Route::post('hotel', [HotelController::class, 'store'])->name('hotel.store');
    Route::get('hotel/{hotel}/edit', [HotelController::class, 'edit'])->name('hotel.edit');
    Route::put('hotel/{hotel}', [HotelController::class, 'update'])->name('hotel.update');
    Route::delete('hotel/{hotel}', [HotelController::class, 'destroy'])->name('hotel.destroy');
});

Route::get('property/{property}', [PropertyController::class, 'show'])->name('property.show');
Route::get('hotel/{hotel}', [HotelController::class, 'show'])->name('hotel.show');

// Agency contact
Route::get('property/{property}/contact', [AgencyContactController::class, 'propertyCreate'])->name('property.contact.create');
Route::post('property/{property}/contact', [AgencyContactController::class, 'propertyStore'])->middleware('throttle:5,1')->name('property.contact.store');
Route::get('parcelles/{parcelle}/contact', [AgencyContactController::class, 'parcelleCreate'])->name('parcelles.contact.create');
Route::post('parcelles/{parcelle}/contact', [AgencyContactController::class, 'parcelleStore'])->middleware('throttle:5,1')->name('parcelles.contact.store');

// Favorite system
Route::post('/properties/{property}/favorite', [FavoriteController::class, 'toggle'])
    ->middleware(['auth', 'verified'])
    ->name('property.favorite');

Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorite')->middleware(['auth', 'verified']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/favorites/property/{property}', [FavoriteController::class, 'toggleProperty'])->name('property.favorite.toggle');
    Route::post('/favorites/parcel/{parcel}', [FavoriteController::class, 'toggleParcel'])->name('parcel.favorite');
    Route::delete('/favorites/property/{property}', [FavoriteController::class, 'destroyProperty'])->name('property.favorite.destroy');
    Route::delete('/favorites/parcel/{parcel}', [FavoriteController::class, 'destroyParcel'])->name('parcel.favorite.destroy');
});

// Admin routes
Route::prefix('/admin/dashboard')->middleware(['auth', 'verified', StaffMiddleware::class])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::resource('property', AdminPropertyController::class);

    Route::post('/qr-code/generate', [QrCodeController::class, 'generate'])->name('qrcode.generate');
    Route::get('/qr-code/download', [QrCodeController::class, 'download'])->name('qrcode.download');
    Route::get('/qr-code', [QrCodeController::class, 'index'])->name('qrcode.index');

    // Routes pour la gestion des statuts
    Route::patch('property/{property}/verify', [AdminPropertyController::class, 'verify'])->name('property.verify');
    Route::patch('property/{property}/unverify', [AdminPropertyController::class, 'unverify'])->name('property.unverify');
    Route::patch('property/{property}/enable', [AdminPropertyController::class, 'enable'])->name('property.enable');
    Route::patch('property/{property}/disable', [AdminPropertyController::class, 'disable'])->name('property.disable');
    Route::patch('property/{property}/toggle-active', [AdminPropertyController::class, 'toggleActive'])->name('property.toggle-active');
    Route::patch('property/{property}/toggle-verify', [AdminPropertyController::class, 'toggleVerify'])->name('property.toggle-verify');

    Route::resource('parcelle', ParcelleController::class);

    // Hotel routes
    Route::resource('hotel', AdminHotelController::class);

    // Routes pour la gestion des statuts des hôtels
    Route::patch('hotel/{hotel}/verify', [AdminHotelController::class, 'verify'])->name('hotel.verify');
    Route::patch('hotel/{hotel}/unverify', [AdminHotelController::class, 'unverify'])->name('hotel.unverify');
    Route::patch('hotel/{hotel}/enable', [AdminHotelController::class, 'enable'])->name('hotel.enable');
    Route::patch('hotel/{hotel}/disable', [AdminHotelController::class, 'disable'])->name('hotel.disable');
    Route::patch('hotel/{hotel}/toggle-active', [AdminHotelController::class, 'toggleActive'])->name('hotel.toggle-active');
    Route::patch('hotel/{hotel}/toggle-verify', [AdminHotelController::class, 'toggleVerify'])->name('hotel.toggle-verify');

    // Artisans
    Route::get('/artisans', [AdminArtisanController::class, 'index'])->name('artisans.index');
    Route::get('/artisans/create', [AdminArtisanController::class, 'create'])->name('artisans.create');
    Route::post('/artisans', [AdminArtisanController::class, 'store'])->name('artisans.store');
    Route::get('/artisans/pending', [AdminArtisanController::class, 'pending'])->name('artisans.pending');
    Route::get('/artisans/{artisan}', [AdminArtisanController::class, 'show'])->name('artisans.show');
    Route::get('/artisans/{artisan}/edit', [AdminArtisanController::class, 'edit'])->name('artisans.edit');
    Route::put('/artisans/{artisan}', [AdminArtisanController::class, 'update'])->name('artisans.update');
    Route::post('/artisans/{artisan}/verify', [AdminArtisanController::class, 'verify'])->name('artisans.verify');
    Route::post('/artisans/{artisan}/suspend', [AdminArtisanController::class, 'suspend'])->name('artisans.suspend');
    Route::delete('/artisans/{artisan}', [AdminArtisanController::class, 'destroy'])->name('artisans.destroy');

    // Réalisations (admin)
    Route::get('/artisans/{artisan}/projects', [AdminArtisanProjectController::class, 'index'])->name('artisans.projects.index');
    Route::get('/artisans/{artisan}/projects/create', [AdminArtisanProjectController::class, 'create'])->name('artisans.projects.create');
    Route::post('/artisans/{artisan}/projects', [AdminArtisanProjectController::class, 'store'])->name('artisans.projects.store');
    Route::get('/artisans/{artisan}/projects/{project}/edit', [AdminArtisanProjectController::class, 'edit'])->name('artisans.projects.edit');
    Route::put('/artisans/{artisan}/projects/{project}', [AdminArtisanProjectController::class, 'update'])->name('artisans.projects.update');
    Route::delete('/artisans/{artisan}/projects/{project}', [AdminArtisanProjectController::class, 'destroy'])->name('artisans.projects.destroy');
    Route::delete('/artisans/{artisan}/projects/image/{image}', [AdminArtisanProjectController::class, 'destroyImage'])->name('artisans.projects.image.destroy');
});

// Socialite
Route::get('/auth/{provider}/redirect', ProviderRedirectController::class)->name('auth.redirect');
Route::get('/auth/{provider}/callback', ProviderCallbackController::class)->name('auth.callback');

// Parcelles
Route::get('/parcelles', [ParcelleWebController::class, 'index'])->name('parcelles.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/parcelles/create', [ParcelleWebController::class, 'create'])->name('parcelles.create');
    Route::post('/parcelles', [ParcelleWebController::class, 'store'])->name('parcelles.store');
    Route::get('/parcelles/{parcelle}/edit', [ParcelleWebController::class, 'edit'])->name('parcelles.edit');
    Route::put('/parcelles/{parcelle}', [ParcelleWebController::class, 'update'])->name('parcelles.update');
    Route::delete('/parcelles/{parcelle}', [ParcelleWebController::class, 'destroy'])->name('parcelles.destroy');
    Route::delete('/parcelles/images/{image}', [ParcelleWebController::class, 'deleteImage'])->name('parcelles.images.destroy');
    Route::get('mes-parcelles/dashboard', [ParcelleWebController::class, 'dashboard'])->name('parcelles.dashboard');
});

Route::get('/parcelles/{parcelle}', [ParcelleWebController::class, 'show'])->name('parcelles.show');

// Artisans (public)
Route::get('/artisans', [ArtisanController::class, 'index'])->name('artisans.index');
Route::get('/artisans/{artisan:slug}', [ArtisanController::class, 'show'])->name('artisans.show');
Route::get('/artisans/{artisan:slug}/projects/{project}', [ArtisanProjectController::class, 'show'])->name('artisans.projects.show');

// Contact
Route::post('/artisans/{artisan:slug}/contact', [ArtisanContactController::class, 'store'])->middleware('throttle:5,1')->name('artisans.contact.store');

// Demandes aux artisans (public)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/artisans/{artisan:slug}/demande', [ArtisanRequestController::class, 'store'])->middleware('throttle:5,1')->name('artisans.request.store');
});

// Routes authentifiées pour artisans
Route::middleware(['auth', 'verified'])->group(function () {
    // Devenir artisan
    Route::get('/devenir-artisan', [ArtisanController::class, 'create'])->name('artisan.create');
    Route::post('/devenir-artisan', [ArtisanController::class, 'store'])->name('artisan.store');
});

// Route group /artisan/* protégée par auth + role:artisan
Route::middleware(['auth', 'verified', 'role:artisan'])->group(function () {
    // Dashboard artisan
    Route::get('/artisan/dashboard', [ArtisanController::class, 'dashboard'])->name('artisan.dashboard');

    // Profil artisan
    Route::get('/artisan/profile', [ArtisanController::class, 'profile'])->name('artisan.profile');
});

// Route pour admin permettant d'accéder au dashboard d'un artisan
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/artisan/dashboard/{artisan}', [ArtisanController::class, 'dashboard'])->name('artisan.dashboard.admin');

    // Avis reçus
    Route::get('/artisan/avis-recus', [ArtisanController::class, 'reviews'])->name('artisan.reviews');

    // Demandes reçues
    Route::get('/artisan/demandes-recues', [ArtisanRequestController::class, 'index'])->name('artisan.requests');
    Route::patch('/artisan/demandes-recues/{demande}', [ArtisanRequestController::class, 'update'])->name('artisan.requests.update');
    Route::delete('/artisan/demandes-recues/{demande}', [ArtisanRequestController::class, 'destroy'])->name('artisan.requests.destroy');

    // Édition profil artisan
    Route::get('/artisan/{artisan}/edit', [ArtisanController::class, 'edit'])->name('artisan.edit');
    Route::put('/artisan/{artisan}', [ArtisanController::class, 'update'])->name('artisan.update');

    // Gestion des clients
    Route::get('/artisan/clients', [ClientController::class, 'index'])->name('artisan.clients.index');
    Route::get('/artisan/clients/create', [ClientController::class, 'create'])->name('artisan.clients.create');
    Route::post('/artisan/clients', [ClientController::class, 'store'])->name('artisan.clients.store');
    Route::get('/artisan/clients/{client}', [ClientController::class, 'show'])->name('artisan.clients.show');
    Route::get('/artisan/clients/{client}/edit', [ClientController::class, 'edit'])->name('artisan.clients.edit');
    Route::put('/artisan/clients/{client}', [ClientController::class, 'update'])->name('artisan.clients.update');
    Route::delete('/artisan/clients/{client}', [ClientController::class, 'destroy'])->name('artisan.clients.destroy');

    // Gestion de l'équipe
    Route::get('/artisan/equipe', [MembreEquipeController::class, 'index'])->name('artisan.equipe.index');
    Route::get('/artisan/equipe/create', [MembreEquipeController::class, 'create'])->name('artisan.equipe.create');
    Route::post('/artisan/equipe', [MembreEquipeController::class, 'store'])->name('artisan.equipe.store');
    Route::get('/artisan/equipe/{membre}', [MembreEquipeController::class, 'show'])->name('artisan.equipe.show');
    Route::get('/artisan/equipe/{membre}/edit', [MembreEquipeController::class, 'edit'])->name('artisan.equipe.edit');
    Route::put('/artisan/equipe/{membre}', [MembreEquipeController::class, 'update'])->name('artisan.equipe.update');
    Route::delete('/artisan/equipe/{membre}', [MembreEquipeController::class, 'destroy'])->name('artisan.equipe.destroy');

    // Gestion du stock
    Route::get('/artisan/stock', [StockController::class, 'index'])->name('artisan.stock.index');
    Route::get('/artisan/stock/create', [StockController::class, 'create'])->name('artisan.stock.create');
    Route::post('/artisan/stock', [StockController::class, 'store'])->name('artisan.stock.store');
    Route::get('/artisan/stock/{article}', [StockController::class, 'show'])->name('artisan.stock.show');
    Route::get('/artisan/stock/{article}/edit', [StockController::class, 'edit'])->name('artisan.stock.edit');
    Route::put('/artisan/stock/{article}', [StockController::class, 'update'])->name('artisan.stock.update');
    Route::delete('/artisan/stock/{article}', [StockController::class, 'destroy'])->name('artisan.stock.destroy');
    Route::post('/artisan/stock/{article}/mouvement', [StockController::class, 'mouvement'])->name('artisan.stock.mouvement');

    // Gestion du planning
    Route::get('/artisan/planning', [EvenementController::class, 'index'])->name('artisan.planning.index');
    Route::get('/artisan/planning/create', [EvenementController::class, 'create'])->name('artisan.planning.create');
    Route::post('/artisan/planning', [EvenementController::class, 'store'])->name('artisan.planning.store');
    Route::get('/artisan/planning/{evenement}', [EvenementController::class, 'show'])->name('artisan.planning.show');
    Route::get('/artisan/planning/{evenement}/edit', [EvenementController::class, 'edit'])->name('artisan.planning.edit');
    Route::put('/artisan/planning/{evenement}', [EvenementController::class, 'update'])->name('artisan.planning.update');
    Route::delete('/artisan/planning/{evenement}', [EvenementController::class, 'destroy'])->name('artisan.planning.destroy');

    // Gestion de la messagerie
    Route::get('/artisan/messagerie', [MessagerieController::class, 'index'])->name('artisan.messagerie.index');
    Route::get('/artisan/messagerie/conversation/create', [MessagerieController::class, 'createConversation'])->name('artisan.messagerie.conversation.create');
    Route::post('/artisan/messagerie/conversation', [MessagerieController::class, 'storeConversation'])->name('artisan.messagerie.conversation.store');
    Route::delete('/artisan/messagerie/conversation/all', [MessagerieController::class, 'destroyAllConversations'])->name('artisan.messagerie.conversation.destroy-all');
    Route::get('/artisan/messagerie/conversation/{conversation}', [MessagerieController::class, 'conversation'])->name('artisan.messagerie.conversation');
    Route::post('/artisan/messagerie/conversation/{conversation}/message', [MessagerieController::class, 'storeMessage'])->name('artisan.messagerie.message');
    Route::delete('/artisan/messagerie/message/{message}', [MessagerieController::class, 'destroyMessage'])->name('artisan.messagerie.message.destroy');
    Route::delete('/artisan/messagerie/conversation/{conversation}', [MessagerieController::class, 'destroyConversation'])->name('artisan.messagerie.conversation.destroy');

    // CRUD Groupes
    Route::get('/artisan/messagerie/groupes/create', [MessagerieController::class, 'createGroupe'])->name('artisan.messagerie.groupes.create');
    Route::post('/artisan/messagerie/groupes', [MessagerieController::class, 'storeGroupe'])->name('artisan.messagerie.groupes.store');
    Route::get('/artisan/messagerie/groupes/{groupe}', [MessagerieController::class, 'showGroupe'])->name('artisan.messagerie.groupes.show');
    Route::get('/artisan/messagerie/groupes/{groupe}/edit', [MessagerieController::class, 'editGroupe'])->name('artisan.messagerie.groupes.edit');
    Route::put('/artisan/messagerie/groupes/{groupe}', [MessagerieController::class, 'updateGroupe'])->name('artisan.messagerie.groupes.update');
    Route::delete('/artisan/messagerie/groupes/{groupe}', [MessagerieController::class, 'destroyGroupe'])->name('artisan.messagerie.groupes.destroy');
    Route::post('/artisan/messagerie/groupes/{groupe}/message', [MessagerieController::class, 'storeGroupeMessage'])->name('artisan.messagerie.groupes.message');

    // Gestion des chantiers
    Route::get('/artisan/chantiers', [ChantierController::class, 'index'])->name('artisan.chantiers.index');
    Route::get('/artisan/chantiers/create', [ChantierController::class, 'create'])->name('artisan.chantiers.create');
    Route::post('/artisan/chantiers', [ChantierController::class, 'store'])->name('artisan.chantiers.store');
    Route::get('/artisan/chantiers/{chantier}', [ChantierController::class, 'show'])->name('artisan.chantiers.show');
    Route::get('/artisan/chantiers/{chantier}/edit', [ChantierController::class, 'edit'])->name('artisan.chantiers.edit');
    Route::put('/artisan/chantiers/{chantier}', [ChantierController::class, 'update'])->name('artisan.chantiers.update');
    Route::patch('/artisan/chantiers/{chantier}/statut', [ChantierController::class, 'updateStatut'])->name('artisan.chantiers.statut');
    Route::delete('/artisan/chantiers/{chantier}', [ChantierController::class, 'destroy'])->name('artisan.chantiers.destroy');

    // Gestion des finances
    Route::get('/artisan/finances', [FinancesController::class, 'index'])->name('artisan.finances.index');
    Route::get('/artisan/finances/{chantier}', [FinancesController::class, 'show'])->name('artisan.finances.show');

    // Devis
    Route::post('/artisan/finances/{chantier}/devis', [FinancesController::class, 'storeDevis'])->name('artisan.finances.store-devis');
    Route::put('/artisan/finances/devis/{devis}', [FinancesController::class, 'updateDevis'])->name('artisan.finances.update-devis');

    // Factures
    Route::post('/artisan/finances/{chantier}/factures', [FinancesController::class, 'storeFacture'])->name('artisan.finances.store-facture');
    Route::put('/artisan/finances/factures/{facture}', [FinancesController::class, 'updateFacture'])->name('artisan.finances.update-facture');

    // Dépenses
    Route::post('/artisan/finances/{chantier}/depenses', [FinancesController::class, 'storeDepense'])->name('artisan.finances.store-depense');
    Route::put('/artisan/finances/depenses/{depense}', [FinancesController::class, 'updateDepense'])->name('artisan.finances.update-depense');
    Route::delete('/artisan/finances/depenses/{depense}', [FinancesController::class, 'destroyDepense'])->name('artisan.finances.destroy-depense');

    // Transactions
    Route::post('/artisan/finances/{chantier}/transactions', [FinancesController::class, 'storeTransaction'])->name('artisan.finances.store-transaction');
    Route::put('/artisan/finances/transactions/{transaction}', [FinancesController::class, 'updateTransaction'])->name('artisan.finances.update-transaction');

    // Gestion des documents
    Route::get('/artisan/documents', [DocumentController::class, 'index'])->name('artisan.documents.index');
    Route::get('/artisan/documents/create/{type?}', [DocumentController::class, 'create'])->name('artisan.documents.create');
    Route::post('/artisan/documents', [DocumentController::class, 'store'])->name('artisan.documents.store');
    Route::get('/artisan/documents/{document}', [DocumentController::class, 'show'])->name('artisan.documents.show');
    Route::get('/artisan/documents/{document}/edit', [DocumentController::class, 'edit'])->name('artisan.documents.edit');
    Route::put('/artisan/documents/{document}', [DocumentController::class, 'update'])->name('artisan.documents.update');
    Route::delete('/artisan/documents/{document}', [DocumentController::class, 'destroy'])->name('artisan.documents.destroy');
    Route::get('/artisan/documents/{document}/export-pdf', [DocumentController::class, 'exportPdf'])->name('artisan.documents.export-pdf');
    Route::post('/artisan/documents/{document}/send-to-client', [DocumentController::class, 'sendToClient'])->name('artisan.documents.send-to-client');

    // Gestion des réalisations
    Route::get('/artisan/{artisan}/projects', [ArtisanProjectController::class, 'index'])->name('artisan.projects.index');
    Route::get('/artisan/{artisan}/projects/create', [ArtisanProjectController::class, 'create'])->name('artisan.projects.create');
    Route::post('/artisan/{artisan}/projects', [ArtisanProjectController::class, 'store'])->name('artisan.projects.store');
    Route::get('/artisan/{artisan}/projects/{project}/edit', [ArtisanProjectController::class, 'edit'])->name('artisan.projects.edit');
    Route::put('/artisan/{artisan}/projects/{project}', [ArtisanProjectController::class, 'update'])->name('artisan.projects.update');
    Route::delete('/artisan/{artisan}/projects/{project}', [ArtisanProjectController::class, 'destroy'])->name('artisan.projects.destroy');
    Route::delete('/artisan/{artisan}/projects/image/{image}', [ArtisanProjectController::class, 'destroyImage'])->name('artisan.projects.image.destroy');
});

// Gestion des avis (public-facing)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/artisans/{artisan:slug}/reviews', [ArtisanReviewController::class, 'store'])->middleware('throttle:10,1')->name('artisans.reviews.store');
    Route::put('/artisans/{artisan:slug}/reviews/{review}', [ArtisanReviewController::class, 'update'])->name('artisans.reviews.update');
    Route::delete('/artisans/{artisan:slug}/reviews/{review}', [ArtisanReviewController::class, 'destroy'])->name('artisans.reviews.destroy');
});

// Routes d'authentification email
Route::middleware(['auth', 'verified'])->group(function () {
    // Pass management
    Route::resource('passes', PassController::class);
    Route::get('passes/{pass}/export', [PassController::class, 'export'])->name('passes.export');

    // Scan management
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::get('/scan/{uuid}', [ScanController::class, 'show'])->name('scan.show');
    Route::post('/scan/process', [ScanController::class, 'process'])->name('scan.process');

    // Statistics
    Route::get('/statistics', [PassController::class, 'statistics'])->name('statistics');
});

// Admin routes with staff middleware
Route::middleware(['auth', 'verified', StaffMiddleware::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Membres
        Route::resource('members', MemberController::class)->except(['create', 'store']);
        Route::patch('members/{member}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');
        Route::patch('members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');

        // Invitations
        Route::resource('invitations', InvitationController::class)->except(['show', 'edit', 'update']);
        Route::post('invitations/{invitation}/resend', [InvitationController::class, 'resend'])->name('invitations.resend');
        Route::get('invitations/accept', [InvitationController::class, 'acceptForm'])->name('invitations.accept.form');
        Route::post('invitations/{invitation}/accept', [InvitationController::class, 'accept'])
            ->middleware('throttle:10,60')
            ->name('invitations.accept');
        Route::post('invitations/{invitation}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');

        // Rôles et permissions
        Route::resource('roles', RoleController::class);
    });

Route::post('/visit-requests', [VisitRequestController::class, 'store'])->middleware('throttle:5,1')->name('visit-requests.store');

Route::middleware(['auth', 'verified'])->group(function () {
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.api');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::delete('/notifications/destroy-read', [NotificationController::class, 'destroyRead'])->name('notifications.destroy-read');
    Route::get('/notifications/all', [NotificationController::class, 'showAll'])->name('notifications.all');

    // API Notifications (pour le polling JavaScript)
    Route::get('/api/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index'])->name('api.notifications.index');
    Route::patch('/api/notifications/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
    Route::patch('/api/notifications/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
});

Route::middleware('auth')->group(function () {
    Route::post('/avis', [AvisController::class, 'store'])->name('avis.store');
    Route::get('/avis', [AvisController::class, 'index'])->name('avis.index');
    Route::delete('/avis/{avis}', [AvisController::class, 'destroy'])->name('avis.destroy');
});

Route::get('/pay', [TransactionController::class, 'depositForm'])
    ->middleware('auth')
    ->name('transactions.pay');

Route::get('/transactions/{transaction}/callback', [TransactionController::class, 'callback'])
    ->name('transactions.callback');

// User visit passes routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/visit-pass/create/{property}', [UserVisitPassController::class, 'create'])
        ->name('my-visit-passes.create');
    Route::post('/visit-pass', [UserVisitPassController::class, 'store'])
        ->name('my-visit-passes.store');
    Route::get('/my-visit-passes', [UserVisitPassController::class, 'index'])
        ->name('my-visit-passes.index');
    Route::get('/my-visit-passes/{visitPass}', [UserVisitPassController::class, 'show'])
        ->name('my-visit-passes.show');
    Route::get('/my-visit-passes/{visitPass}/download', [UserVisitPassController::class, 'download'])
        ->name('my-visit-passes.download');
    Route::post('/my-visit-passes/{visitPass}/retry-payment', [UserVisitPassController::class, 'retryPayment'])
        ->name('my-visit-passes.retry-payment');
    Route::delete('/my-visit-passes/{visitPass}', [UserVisitPassController::class, 'destroy'])
        ->name('my-visit-passes.destroy');
});

Route::livewire('chat', 'pages::chat.index');

// Route::get('/debug-signature', function (Request $request) {
//     return response()->json([
//         'full_url' => $request->fullUrl(),
//         'has_valid_signature' => $request->hasValidSignature(),
//         'scheme' => $request->getScheme(),
//         'host' => $request->getHost(),
//         'forwarded_proto' => $request->header('X-Forwarded-Proto'),
//         'forwarded_host' => $request->header('X-Forwarded-Host'),
//     ]);
// });

Route::get('/propos', function () {
    return view('apropos');
})->name('propos');

Route::get('/politique', function () {
    return view('politique');
})->name('politique');

Route::get('/conditions', function () {
    return view('conditions');
})->name('conditions');

Route::get('/apropo_S', function () {
    return view('apropo_S');
})->name('apropos_S');