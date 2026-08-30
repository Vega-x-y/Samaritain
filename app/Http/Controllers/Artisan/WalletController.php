<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\ArtisanWalletEntry;
use App\Models\User;
use App\Services\ArtisanWalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(protected ArtisanWalletService $walletService) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $wallet = $this->walletService->getWalletForArtisan($artisan);

        $entries = ArtisanWalletEntry::where('artisan_wallet_id', $wallet->id)
            ->with('transaction')
            ->latest()
            ->paginate(15);

        return view('pages.artisan.wallet.index', compact('artisan', 'wallet', 'entries'));
    }
}
