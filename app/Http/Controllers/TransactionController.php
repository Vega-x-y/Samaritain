<?php

namespace App\Http\Controllers;

use App\Exceptions\PawaPayException;
use App\Jobs\ProcessPawaPayCallback;
use App\Models\Transaction;
use App\Services\PawapayService;
use App\Services\RentPaymentService;
use App\Services\VisitPassService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        protected PawapayService $pawapay,
        protected VisitPassService $visitPassService,
    ) {}

    /**
     * Show the in-app payment tracking screen after the hosted page.
     *
     * After the hosted payment page is opened, the page displays the live status
     * and refreshes via the transaction status
     * endpoint; the final result is also confirmed by the callback job and the
     * reconciliation command.
     */
    public function pending(Transaction $transaction)
    {
        $user = auth()->user();

        if ($transaction->user_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à consulter cette transaction.');
        }

        return view('transaction.pending', compact('transaction'));
    }

    /**
     * Handle the pawaPay browser redirect (returnUrl) after payment.
     *
     * The user's browser is redirected here after completing the payment on
     * pawaPay's hosted page. The actual status verification happens via
     * the server-to-server webhook callback. Here we show the user a
     * simple result page.
     */
    public function callback(Transaction $transaction)
    {
        $user = auth()->user();

        if ($transaction->user_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à consulter cette transaction.');
        }

        try {
            $this->syncStatus($transaction);
        } catch (PawaPayException $e) {
            return redirect()->route('transactions.pending', $transaction)
                ->with('error', 'Le statut du paiement sera vérifié automatiquement.');
        }

        $transaction->refresh();

        if ($transaction->status === 'completed') {
            if ($transaction->visit_pass_id && $transaction->visitPass) {
                return redirect()->route('my-visit-passes.show', $transaction->visitPass)
                    ->with('success', 'Paiement confirmé avec succès ! Votre pass visite est disponible.');
            }

            if ($transaction->rent_payment_id) {
                return redirect()->route('tenant.payments')
                    ->with('success', 'Votre paiement de loyer a bien été pris en compte.');
            }
        }

        return redirect()->route('transactions.pending', $transaction);
    }

    /**
     * Handle the pawaPay server-to-server webhook callback — POST.
     *
     * Verify the signature (if configured), then dispatch a queued job that
     * independently verifies the deposit status via the pawaPay API before
     * updating the local record. Respond 200 immediately — all heavy work
     * is offloaded to the queue.
     */
    public function handleCallback(Request $request, Transaction $transaction)
    {
        $payload = $request->getContent();
        if (! $this->pawapay->verifyCallbackRequest(
            payload: $payload,
            headers: $this->pawaPayHeaders($request),
            method: $request->method(),
            authority: $request->getHost(),
            path: $request->getPathInfo(),
        )) {
            \Log::warning('pawaPay callback signature verification failed', [
                'transaction_id' => $transaction->transaction_id,
                'signature' => $request->header('signature'),
            ]);

            return response('Invalid signature', 403);
        }

        $callbackData = $request->validate([
            'depositId' => ['nullable', 'string'],
            'payoutId' => ['nullable', 'string'],
            'refundId' => ['nullable', 'string'],
            'event' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        ProcessPawaPayCallback::dispatch($transaction, $callbackData);

        return response('OK', 200);
    }

    /**
     * Handle the pawaPay server-to-server generic webhook callback — POST.
     *
     * This endpoint is called without a dynamic transaction ID. It identifies the
     * transaction in the database using the depositId, payoutId, or refundId
     * from the webhook payload.
     */
    public function handleGenericCallback(Request $request)
    {
        $payload = $request->getContent();
        if (! $this->pawapay->verifyCallbackRequest(
            payload: $payload,
            headers: $this->pawaPayHeaders($request),
            method: $request->method(),
            authority: $request->getHost(),
            path: $request->getPathInfo(),
        )) {
            \Log::warning('pawaPay generic callback signature verification failed', [
                'signature' => $request->header('signature'),
            ]);

            return response('Invalid signature', 403);
        }

        $callbackData = $request->validate([
            'depositId' => ['nullable', 'string'],
            'payoutId' => ['nullable', 'string'],
            'refundId' => ['nullable', 'string'],
            'event' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $reference = $callbackData['depositId'] ?? $callbackData['payoutId'] ?? $callbackData['refundId'] ?? null;

        if (! $reference) {
            return response('Missing reference', 400);
        }

        $transaction = Transaction::where('deposit_id', $reference)
            ->orWhere('payout_id', $reference)
            ->first();

        if (! $transaction) {
            \Log::warning('pawaPay generic callback: transaction not found', [
                'reference' => $reference,
            ]);

            return response('Transaction not found', 404);
        }

        ProcessPawaPayCallback::dispatch($transaction, $callbackData);

        return response('OK', 200);
    }

    /**
     * Manually check the status of a deposit via the pawaPay API.
     */
    public function status(Transaction $transaction)
    {
        $user = auth()->user();

        if ($transaction->user_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à consulter cette transaction.');
        }

        if (! $transaction->deposit_id) {
            return redirect()->back()->with('error', 'Aucun identifiant de dépôt n\'est associé à cette transaction.');
        }

        try {
            $pawaPayStatus = $this->syncStatus($transaction);
        } catch (PawaPayException $e) {
            return redirect()->back()->with('error', 'Impossible de vérifier le statut du paiement auprès de pawaPay.');
        }

        return redirect()->back()->with('status', 'Statut du paiement: '.$pawaPayStatus);
    }

    /**
     * Synchronize a deposit status and apply the related business transition.
     */
    protected function syncStatus(Transaction $transaction): string
    {
        if (! $transaction->deposit_id) {
            throw new PawaPayException('Aucun identifiant de dépôt associé à la transaction.');
        }

        $statusResponse = $this->pawapay->getDepositStatus($transaction->deposit_id);
        $pawaPayStatus = strtoupper($statusResponse['status'] ?? 'UNKNOWN');

        $transaction->update([
            'raw_response' => $statusResponse,
            'status' => $this->mapPawaPayStatus($pawaPayStatus),
        ]);

        if ($pawaPayStatus === 'COMPLETED' && $transaction->visit_pass_id) {
            $visitPass = $transaction->visitPass;
            if ($visitPass && ! $visitPass->isPaid()) {
                $this->visitPassService->handleSuccessfulPayment($visitPass);
            }
        }

        if (in_array($pawaPayStatus, ['FAILED', 'REJECTED']) && $transaction->visit_pass_id) {
            $visitPass = $transaction->visitPass;
            if ($visitPass && ! $visitPass->isPaymentFailed()) {
                $this->visitPassService->handleFailedPayment($visitPass);
            }
        }

        if ($pawaPayStatus === 'COMPLETED' && $transaction->rent_payment_id && $transaction->rentPayment) {
            app(RentPaymentService::class)->handleSuccessfulPayment($transaction->rentPayment);
        }

        if (in_array($pawaPayStatus, ['FAILED', 'REJECTED']) && $transaction->rent_payment_id && $transaction->rentPayment) {
            app(RentPaymentService::class)->handleFailedPayment($transaction->rentPayment);
        }

        return $pawaPayStatus;
    }

    /**
     * Map pawaPay statuses to local transaction statuses.
     */
    protected function mapPawaPayStatus(string $status): string
    {
        return match ($status) {
            'COMPLETED' => 'completed',
            'FAILED', 'REJECTED' => 'failed',
            'ACCEPTED' => 'accepted',
            'PROCESSING' => 'processing',
            'PENDING', 'SUBMITTED' => 'pending',
            'IN_RECONCILIATION' => 'pending',
            default => 'pending',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function pawaPayHeaders(Request $request): array
    {
        return collect(['content-digest', 'signature', 'signature-input', 'signature-date', 'content-type'])
            ->mapWithKeys(fn (string $header): array => [$header => (string) $request->header($header, '')])
            ->all();
    }
}
