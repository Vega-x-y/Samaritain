<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class ProfileController extends Controller
{
    /**
     * Afficher le dashboard utilisateur
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Statistiques des biens
        $propertiesStats = [
            'total' => Property::where('created_by', $user->id)->count(),
            'active' => Property::where('created_by', $user->id)->where('is_active', true)->count(),
            'pending' => Property::where('created_by', $user->id)->where('is_verify', false)->count(),
            'sold' => Property::where('created_by', $user->id)->where('status', 'sold')->count(),
        ];
        
        // Derniers biens
        $recentProperties = Property::where('created_by', $user->id)
            ->with(['city', 'category'])
            ->latest()
            ->take(5)
            ->get();
        
        // Profil artisan si existe
        $artisan = Artisan::where('user_id', $user->id)->first();
        
        // Statistiques artisan si applicable
        $artisanStats = null;
        if ($artisan) {
            $artisanStats = [
                'reviews_count' => $artisan->reviews()->count(),
                'average_rating' => $artisan->average_rating ?? 0,
                'projects_count' => $artisan->projects()->count(),
                'verified' => $artisan->verified,
            ];
        }
        
        return view('profile.dashboard', compact('user', 'propertiesStats', 'recentProperties', 'artisan', 'artisanStats'));
    }
    
    /**
     * Afficher le formulaire d'édition du profil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }
    
    /**
     * Mettre à jour le profil utilisateur
     */
    public function update(Request $request, UpdatesUserProfileInformation $updater)
    {
        $updater->update(Auth::user(), $request->all());
        
        return redirect()->route('profile.edit')->with('success', 'Profil mis à jour avec succès.');
    }
    
    /**
     * Mettre à jour l'avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        
        $user = Auth::user();
        
        // Supprimer l'ancien avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // Upload du nouvel avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();
        
        return redirect()->route('profile.edit')->with('success', 'Avatar mis à jour avec succès.');
    }
    
    /**
     * Supprimer le compte utilisateur
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        
        // Supprimer l'avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // Supprimer l'utilisateur
        $user->delete();
        
        Auth::logout();
        
        return redirect('/')->with('success', 'Votre compte a été supprimé avec succès.');
    }
}