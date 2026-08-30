<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $commissionPercent = \App\Models\Setting::getValue('artisan_commission_percent', 5);
        
        return view('pages.admin.settings.index', compact('commissionPercent'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'artisan_commission_percent' => 'required|integer|min:0|max:100',
        ]);

        \App\Models\Setting::setValue(
            'artisan_commission_percent', 
            $request->artisan_commission_percent, 
            'integer', 
            'Pourcentage de commission prélevé sur les acomptes des artisans'
        );

        return redirect()->back()->with('success', 'Configuration mise à jour avec succès.');
    }
}
