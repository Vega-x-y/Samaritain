<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;

class HotelController extends Controller
{
    /**
     * Display a listing of hotels.
     */
    public function index()
    {
        $hotels = Hotel::paginate(10);

        return view('pages.admin.hotel.index', [
            'hotels' => $hotels,
        ]);
    }

    /**
     * Display the specified hotel.
     */
    public function show(Hotel $hotel)
    {
        $hotel->load(['amenities', 'images', 'city', 'arrondissement', 'creator']);

        return view('pages.admin.hotel.show', [
            'hotel' => $hotel,
        ]);
    }

    /**
     * Verify a hotel (is_verify = true)
     */
    public function verify(Hotel $hotel)
    {
        $hotel->update([
            'is_verify' => true,
        ]);

        return redirect()->route('admin.hotel.index')->with('success', 'L\'hôtel a été vérifié avec succès.');
    }

    /**
     * Unverify a hotel (is_verify = false)
     */
    public function unverify(Hotel $hotel)
    {
        $hotel->update([
            'is_verify' => false,
        ]);

        return redirect()->route('admin.hotel.index')->with('success', 'La vérification de l\'hôtel a été annulée.');
    }

    /**
     * Enable a hotel (is_active = true)
     */
    public function enable(Hotel $hotel)
    {
        $hotel->update([
            'is_active' => true,
        ]);

        return redirect()->route('admin.hotel.index')->with('success', 'L\'hôtel a été activé avec succès.');
    }

    /**
     * Disable a hotel (is_active = false)
     */
    public function disable(Hotel $hotel)
    {
        $hotel->update([
            'is_active' => false,
        ]);

        return redirect()->route('admin.hotel.index')->with('success', 'L\'hôtel a été désactivé avec succès.');
    }

    /**
     * Toggle active status via AJAX
     */
    public function toggleActive(Hotel $hotel)
    {
        $hotel->update([
            'is_active' => ! $hotel->is_active,
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $hotel->is_active,
                'message' => 'Le statut a été modifié avec succès.',
            ]);
        }

        return redirect()->route('admin.hotel.index')->with('success', 'Le statut de l\'hôtel a été modifié.');
    }

    /**
     * Toggle verify status via AJAX
     */
    public function toggleVerify(Hotel $hotel)
    {
        $hotel->update([
            'is_verify' => ! $hotel->is_verify,
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_verify' => $hotel->is_verify,
                'message' => 'La vérification a été modifiée avec succès.',
            ]);
        }

        return redirect()->route('admin.hotel.index')->with('success', 'Le statut de vérification a été modifié.');
    }
}
