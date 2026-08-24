# Exemples d'utilisation PawaPay

Ce document contient des exemples pratiques d'utilisation de l'intégration PawaPay dans différents contextes.

## Table des matières

1. [Paiement d'une commande](#paiement-dune-commande)
2. [Paiement de loyer](#paiement-de-loyer)
3. [Pass de visite payant](#pass-de-visite-payant)
4. [Remboursement d'une commande annulée](#remboursement-dune-commande-annulée)
5. [Paiement à un artisan](#paiement-à-un-artisan)
6. [Vérification manuelle du statut](#vérification-manuelle-du-statut)
7. [Bulk payouts (paiements groupés)](#bulk-payouts-paiements-groupés)

---

## Paiement d'une commande

**Contexte** : Un utilisateur passe une commande et doit payer avant livraison.

### Backend (Controller)

```php
namespace App\Http\Controllers;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderPaymentController extends Controller
{
    public function initiatePayment(Request $request, Order $order, PawapayService $pawapay)
    {
        // Authorization
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Validation
        $validated = $request->validate([
            'phone_number' => ['required', 'string'],
            'provider' => ['required', 'string', 'in:MTN_MOMO_COG,AIRTEL_COG'],
        ]);

        // Normaliser le numéro
        $phoneNumber = $pawapay->normalizePhoneNumber($validated['phone_number']);

        // Générer UUID
        $depositId = Str::uuid()->toString();

        // Créer transaction
        $transaction = Transaction::create([
            'transaction_id' => $depositId,
            'user_id' => auth()->id(),
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::PENDING,
            'amount' => $order->total_amount,
            'deposit_id' => $depositId,
            'provider' => $validated['provider'],
            'currency' => config('pawapay.default_currency'),
        ]);

        // Associer la transaction à la commande (ajoutez une colonne transaction_id dans orders)
        $order->update(['transaction_id' => $transaction->transaction_id]);

        try {
            // Initier le dépôt
            $response = $pawapay->initiateDeposit(new DepositRequest(
                depositId: $depositId,
                phoneNumber: $phoneNumber,
                provider: $validated['provider'],
                amount: (string) ($order->total_amount / 100),
                currency: config('pawapay.default_currency'),
                clientReferenceId: "ORDER-{$order->id}",
                customerMessage: "Commande #{$order->id}",
                metadata: ['order_id' => $order->id],
            ));

            // Mettre à jour le statut
            $status = TransactionStatus::tryFrom($response['status']) ?? TransactionStatus::PENDING;
            $transaction->update([
                'status' => $status,
                'raw_response' => $response,
            ]);

            if ($status === TransactionStatus::REJECTED) {
                return back()->with('error', 'Le paiement a été rejeté. Veuillez réessayer.');
            }

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Paiement en cours. Veuillez valider sur votre téléphone.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Une erreur est survenue. Veuillez réessayer.');
        }
    }
}
```

### Traitement du callback

Créer un listener qui réagit aux changements de statut :

```php
namespace App\Listeners;

use App\Events\TransactionStatusUpdated;
use App\Enums\TransactionStatus;
use App\Models\Order;

class HandleOrderPaymentCompleted
{
    public function handle(TransactionStatusUpdated $event): void
    {
        $transaction = $event->transaction;

        // Trouver la commande associée
        $order = Order::where('transaction_id', $transaction->transaction_id)->first();

        if (!$order) {
            return;
        }

        if ($transaction->status === TransactionStatus::COMPLETED) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Envoyer email de confirmation, etc.
        }

        if ($transaction->status->isFinal() && !$transaction->status->isSuccessful()) {
            $order->update(['status' => 'payment_failed']);
        }
    }
}
```

### Frontend (Livewire)

```blade
<!-- resources/views/orders/pay.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <flux:heading size="xl">Paiement de la commande #{{ $order->id }}</flux:heading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <flux:card>
                <flux:heading size="lg">Total à payer</flux:heading>
                <flux:text size="2xl" class="font-bold">
                    {{ number_format($order->total_amount / 100, 0) }} XAF
                </flux:text>

                <div class="mt-6">
                    <livewire:payment.initiate-deposit
                        :amount="$order->total_amount"
                        :purpose="'order'"
                        :reference-id="$order->id"
                        :return-url="route('orders.show', $order)"
                    />
                </div>
            </flux:card>
        </div>
    </div>
</x-app-layout>
```

---

## Paiement de loyer

**Contexte** : Un locataire paie son loyer mensuel.

### Controller

```php
namespace App\Http\Controllers\Tenant;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\RentPayment;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RentPaymentController extends Controller
{
    public function pay(RentPayment $rentPayment)
    {
        // Authorization
        if ($rentPayment->contract->tenant_id !== auth()->id()) {
            abort(403);
        }

        return view('tenant.rent-payments.pay', compact('rentPayment'));
    }

    public function initiate(Request $request, RentPayment $rentPayment, PawapayService $pawapay)
    {
        // Validation
        $validated = $request->validate([
            'phone_number' => ['required', 'string'],
            'provider' => ['required', 'string'],
        ]);

        $depositId = Str::uuid()->toString();

        $transaction = Transaction::create([
            'transaction_id' => $depositId,
            'user_id' => auth()->id(),
            'rent_payment_id' => $rentPayment->id,
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::PENDING,
            'amount' => $rentPayment->amount,
            'deposit_id' => $depositId,
            'provider' => $validated['provider'],
            'currency' => 'XAF',
        ]);

        try {
            $response = $pawapay->initiateDeposit(new DepositRequest(
                depositId: $depositId,
                phoneNumber: $pawapay->normalizePhoneNumber($validated['phone_number']),
                provider: $validated['provider'],
                amount: (string) ($rentPayment->amount / 100),
                currency: 'XAF',
                clientReferenceId: "RENT-{$rentPayment->id}",
                customerMessage: "Loyer {$rentPayment->month}",
                metadata: ['rent_payment_id' => $rentPayment->id],
            ));

            $transaction->update([
                'status' => TransactionStatus::tryFrom($response['status']) ?? TransactionStatus::PENDING,
                'raw_response' => $response,
            ]);

            return redirect()
                ->route('tenant.payments')
                ->with('success', 'Paiement en cours.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Erreur lors du paiement.');
        }
    }
}
```

---

## Pass de visite payant

**Contexte** : Un visiteur paie pour obtenir un pass de visite.

### Composant Livewire personnalisé

```php
namespace App\Livewire;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\VisitPass;
use App\Services\PawapayService;
use Illuminate\Support\Str;
use Livewire\Component;

class PayVisitPass extends Component
{
    public VisitPass $visitPass;
    public string $phoneNumber = '';
    public string $provider = '';
    public bool $processing = false;
    public ?string $errorMessage = null;

    public function mount(VisitPass $visitPass)
    {
        $this->visitPass = $visitPass;
    }

    public function pay(PawapayService $pawapay)
    {
        $this->validate([
            'phoneNumber' => ['required', 'string', 'min:9'],
            'provider' => ['required', 'string'],
        ]);

        $this->processing = true;
        $this->errorMessage = null;

        try {
            $depositId = Str::uuid()->toString();

            $transaction = Transaction::create([
                'transaction_id' => $depositId,
                'user_id' => auth()->id(),
                'visit_pass_id' => $this->visitPass->id,
                'type' => TransactionType::DEPOSIT,
                'status' => TransactionStatus::PENDING,
                'amount' => $this->visitPass->price,
                'deposit_id' => $depositId,
                'provider' => $this->provider,
                'currency' => 'XAF',
            ]);

            $response = $pawapay->initiateDeposit(new DepositRequest(
                depositId: $depositId,
                phoneNumber: $pawapay->normalizePhoneNumber($this->phoneNumber),
                provider: $this->provider,
                amount: (string) ($this->visitPass->price / 100),
                currency: 'XAF',
                clientReferenceId: "PASS-{$this->visitPass->id}",
                customerMessage: "Pass visite",
                metadata: ['visit_pass_id' => $this->visitPass->id],
            ));

            $transaction->update([
                'status' => TransactionStatus::tryFrom($response['status']) ?? TransactionStatus::PENDING,
                'raw_response' => $response,
            ]);

            if ($response['status'] === 'REJECTED') {
                $this->errorMessage = 'Paiement rejeté. Veuillez réessayer.';
                $this->processing = false;
                return;
            }

            return redirect()->route('my-visit-passes.show', $this->visitPass);

        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors du paiement.';
            $this->processing = false;
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.pay-visit-pass');
    }
}
```

---

## Remboursement d'une commande annulée

**Contexte** : Le client annule une commande déjà payée, il faut le rembourser.

### Controller

```php
namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\Pawapay\RefundRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Support\Str;

class OrderRefundController extends Controller
{
    public function refund(Order $order, PawapayService $pawapay)
    {
        // Vérifier que la commande est payée
        $originalTransaction = Transaction::where('transaction_id', $order->transaction_id)
            ->where('status', TransactionStatus::COMPLETED)
            ->firstOrFail();

        // Générer UUID pour le refund
        $refundId = Str::uuid()->toString();

        // Créer transaction de refund
        $refundTransaction = Transaction::create([
            'transaction_id' => $refundId,
            'user_id' => $order->user_id,
            'type' => TransactionType::REFUND,
            'status' => TransactionStatus::PENDING,
            'amount' => $originalTransaction->amount,
            'refund_id' => $refundId,
            'provider' => $originalTransaction->provider,
            'currency' => $originalTransaction->currency,
        ]);

        try {
            // Initier le remboursement
            $response = $pawapay->initiateRefund(new RefundRequest(
                refundId: $refundId,
                depositId: $originalTransaction->deposit_id,
                amount: (string) ($originalTransaction->amount / 100),
            ));

            $refundTransaction->update([
                'status' => TransactionStatus::tryFrom($response['status']) ?? TransactionStatus::PENDING,
                'raw_response' => $response,
            ]);

            // Mettre à jour la commande
            $order->update(['status' => 'refunded']);

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Remboursement initié.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Erreur lors du remboursement.');
        }
    }
}
```

---

## Paiement à un artisan

**Contexte** : Payer un artisan pour un travail effectué (payout).

### Controller

```php
namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Artisan;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtisanPayoutController extends Controller
{
    public function pay(Request $request, Artisan $artisan, PawapayService $pawapay)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:500'],
            'phone_number' => ['required', 'string'],
            'provider' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:100'],
        ]);

        $payoutId = Str::uuid()->toString();

        $transaction = Transaction::create([
            'transaction_id' => $payoutId,
            'user_id' => $artisan->user_id,
            'type' => TransactionType::PAYOUT,
            'status' => TransactionStatus::PENDING,
            'amount' => $validated['amount'],
            'payout_id' => $payoutId,
            'provider' => $validated['provider'],
            'currency' => 'XAF',
        ]);

        try {
            $response = $pawapay->initiatePayout(new PayoutRequest(
                payoutId: $payoutId,
                phoneNumber: $pawapay->normalizePhoneNumber($validated['phone_number']),
                provider: $validated['provider'],
                amount: (string) ($validated['amount'] / 100),
                currency: 'XAF',
                clientReferenceId: "ARTISAN-{$artisan->id}",
                customerMessage: $validated['reason'] ?? "Paiement artisan",
                metadata: ['artisan_id' => $artisan->id],
            ));

            $transaction->update([
                'status' => TransactionStatus::tryFrom($response['status']) ?? TransactionStatus::PENDING,
                'raw_response' => $response,
            ]);

            return back()->with('success', 'Paiement initié vers l\'artisan.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Erreur lors du paiement.');
        }
    }
}
```

---

## Vérification manuelle du statut

**Contexte** : Un paiement est bloqué en `SUBMITTED`, on veut forcer la vérification.

### Command Artisan

```php
namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Console\Command;

class ReconcilePendingTransactions extends Command
{
    protected $signature = 'pawapay:reconcile {--transaction=}';
    protected $description = 'Reconcile pending PawaPay transactions';

    public function handle(PawapayService $pawapay): int
    {
        $query = Transaction::pending();

        if ($transactionId = $this->option('transaction')) {
            $query->where('transaction_id', $transactionId);
        }

        $transactions = $query->get();

        $this->info("Reconciling {$transactions->count()} transactions...");

        foreach ($transactions as $transaction) {
            try {
                $response = match ($transaction->type->value) {
                    'DEPOSIT' => $pawapay->getDepositStatus($transaction->deposit_id),
                    'PAYOUT' => $pawapay->getPayoutStatus($transaction->payout_id),
                    'REFUND' => $pawapay->getRefundStatus($transaction->refund_id),
                };

                if ($response['status'] === 'FOUND') {
                    $newStatus = TransactionStatus::tryFrom($response['data']['status']) 
                        ?? TransactionStatus::PENDING;

                    if ($transaction->status !== $newStatus) {
                        $transaction->update([
                            'status' => $newStatus,
                            'raw_response' => array_merge($transaction->raw_response ?? [], $response['data']),
                        ]);

                        $this->info("Updated {$transaction->transaction_id}: {$newStatus->value}");
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error for {$transaction->transaction_id}: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
```

**Utilisation :**

```bash
# Réconcilier toutes les transactions pending
php artisan pawapay:reconcile

# Réconcilier une transaction spécifique
php artisan pawapay:reconcile --transaction=f4401bd2-1568-4140-bf2d-eb77d2b2b639
```

---

## Bulk payouts (paiements groupés)

**Contexte** : Payer plusieurs artisans ou vendeurs en une seule requête.

### Service

```php
namespace App\Services;

use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BulkPayoutService
{
    public function __construct(
        protected PawapayService $pawapay
    ) {}

    /**
     * @param Collection<array{user_id: int, amount: int, phone_number: string, provider: string, reference: string}> $payouts
     */
    public function processBulkPayouts(Collection $payouts): array
    {
        $requests = $payouts->map(function ($payout) {
            $payoutId = Str::uuid()->toString();

            // Créer transaction locale
            Transaction::create([
                'transaction_id' => $payoutId,
                'user_id' => $payout['user_id'],
                'type' => TransactionType::PAYOUT,
                'status' => TransactionStatus::PENDING,
                'amount' => $payout['amount'],
                'payout_id' => $payoutId,
                'provider' => $payout['provider'],
                'currency' => 'XAF',
            ]);

            return new PayoutRequest(
                payoutId: $payoutId,
                phoneNumber: $this->pawapay->normalizePhoneNumber($payout['phone_number']),
                provider: $payout['provider'],
                amount: (string) ($payout['amount'] / 100),
                currency: 'XAF',
                clientReferenceId: $payout['reference'],
            );
        })->toArray();

        // Appeler l'API bulk
        $response = $this->pawapay->initiateBulkPayout($requests);

        // Mettre à jour les statuts
        foreach ($response as $result) {
            $transaction = Transaction::where('payout_id', $result['payoutId'])->first();
            
            if ($transaction) {
                $transaction->update([
                    'status' => TransactionStatus::tryFrom($result['status']) ?? TransactionStatus::PENDING,
                    'raw_response' => $result,
                ]);
            }
        }

        return $response;
    }
}
```

**Utilisation :**

```php
$payouts = collect([
    [
        'user_id' => 1,
        'amount' => 50000,
        'phone_number' => '242064567890',
        'provider' => 'MTN_MOMO_COG',
        'reference' => 'ARTISAN-1-MONTHLY',
    ],
    [
        'user_id' => 2,
        'amount' => 75000,
        'phone_number' => '242065678901',
        'provider' => 'AIRTEL_COG',
        'reference' => 'ARTISAN-2-MONTHLY',
    ],
]);

$service = app(BulkPayoutService::class);
$results = $service->processBulkPayouts($payouts);
```

---

## Conclusion

Ces exemples couvrent les cas d'usage les plus courants. Pour plus de détails :

- Consultez `PAWAPAY_INTEGRATION.md` pour la documentation complète
- Consultez la skill `pawapay-integration` dans `.agents/skills/pawapay/`
- Consultez https://docs.pawapay.io/v2/docs/welcome pour l'API officielle
