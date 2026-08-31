<?php

namespace App\Http\Controllers\Artisan;

use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Http\Controllers\Controller;
use App\Models\ArtisanWalletEntry;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ArtisanWalletService;
use App\Services\PawapayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        protected ArtisanWalletService $walletService,
        protected PawapayService $pawapay,
    ) {}

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

    public function withdrawForm(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $wallet = $this->walletService->getWalletForArtisan($artisan);

        if ($wallet->available_balance < 1000) {
            return redirect()->route('artisan.wallet')
                ->with('error', 'Votre solde est insuffisant pour effectuer un retrait. Le montant minimum de retrait est de 1000.');
        }

        $providers_data = $this->getProvidersAvailable('PAYOUT');

        if (empty($providers_data) || empty($providers_data['countries'][0]['providers'])) {
            return redirect()->route('artisan.wallet')
                ->with('error', "Aucun fournisseur de transfert d'argent disponible pour votre pays.");
        }

        return view('transactions.withdraw-form', [
            'payment_config' => $providers_data['countries'][0],
            'branding' => $this->buildBranding($providers_data, 'PAYOUT'),
            'balance' => $wallet->available_balance,
            'action' => route('artisan.wallet.withdraw'),
        ]);
    }

    public function initWithdraw(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $request->validate([
            'amount' => 'required|numeric|min:2',
            'phone' => 'required|min:9',
            'provider' => 'required',
        ]);

        $wallet = $this->walletService->getWalletForArtisan($artisan);

        if ($wallet->available_balance < (int) $request->amount) {
            return redirect()->route('artisan.wallet.withdraw.form')
                ->with('error', 'Le montant demandé dépasse votre solde disponible.');
        }

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => TransactionType::PAYOUT,
            'amount' => (int) $request->amount,
            'status' => TransactionStatus::PENDING,
            'provider' => $request->provider,
            'currency' => config('services.pawapay.currency'),
            'payout_id' => Str::uuid()->toString(),
        ]);

        try {
            $phone = $this->pawapay->normalizePhoneNumber($request->phone);

            $response = $this->pawapay->initiatePayout(new PayoutRequest(
                payoutId: $transaction->payout_id,
                phoneNumber: $phone,
                provider: $transaction->provider,
                amount: (string) $transaction->amount,
                currency: $transaction->currency,
            ));

            if (($response['status'] ?? null) === 'ACCEPTED') {
                $this->walletService->reservePayout($transaction);

                return redirect()->route('transactions.withdraw.status', $transaction);
            }

            $transaction->update([
                'status' => TransactionStatus::REJECTED,
                'raw_response' => $response,
            ]);

            return redirect()->route('transactions.withdraw.status', $transaction);
        } catch (PawaPayException|\Throwable $th) {
            Log::warning('Artisan init withdraw failed', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $th->getMessage(),
            ]);

            return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayer !");
        }
    }

    protected function getProvidersAvailable(string $type = 'DEPOSIT'): array
    {
        try {
            return $this->pawapay->getActiveConfiguration(
                (string) config('services.pawapay.country'),
                $type
            );
        } catch (PawaPayException $th) {
            Log::warning('PawaPay active configuration failed', ['error' => $th->getMessage()]);

            return [];
        }
    }

    /**
     * Build the branding payload (company name, country flag, provider logos)
     * for the payment forms from the PawaPay active configuration.
     *
     * @param  array<string, mixed>  $providersData
     * @return array{companyName: ?string, countryName: ?string, flag: ?string, prefix: string, providers: array<int, array{provider: string, displayName: string, logo: ?string}>}
     */
    protected function buildBranding(array $providersData, string $type): array
    {
        $countryConfig = $providersData['countries'][0] ?? [];
        $displayName = $countryConfig['displayName'] ?? [];

        return [
            'companyName' => $providersData['companyName'] ?? null,
            'countryName' => $displayName['fr'] ?? $displayName['en'] ?? null,
            'flag' => isset($countryConfig['flag']) && is_string($countryConfig['flag']) ? $countryConfig['flag'] : null,
            'prefix' => (string) ($countryConfig['prefix'] ?? config('services.pawapay.dial_code', '242')),
            'providers' => $providersData['countries'][0]['providers'] ?? [],
        ];
    }
}
