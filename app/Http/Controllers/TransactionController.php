<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\Transaction;
use App\Services\OwnerWalletService;
use App\Services\PawapayService;
use App\Services\RentPaymentService;
use App\Services\VisitPassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('pages.tenant.transactions.index', compact('transactions'));
    }

    public function show(Request $request, Transaction $transaction): View
    {
        $this->authorizeTransaction($request, $transaction);

        return view('transaction.pending', compact('transaction'));
    }

    public function pending(Request $request, Transaction $transaction): View
    {
        $this->authorizeTransaction($request, $transaction);

        return view('transaction.pending', compact('transaction'));
    }

    public function status(Request $request, Transaction $transaction, PawapayService $pawapay): RedirectResponse
    {
        $this->authorizeTransaction($request, $transaction);

        if ($transaction->status->isPending()) {
            try {
                $response = $transaction->type === TransactionType::PAYOUT
                    ? $pawapay->getPayoutStatus((string) $transaction->payout_id)
                    : $pawapay->getDepositStatus((string) $transaction->deposit_id);
                $status = TransactionStatus::tryFrom(strtoupper((string) ($response['status'] ?? '')));

                if ($status !== null) {
                    $transaction->update(['status' => $status, 'raw_response' => $response]);
                    if ($status->isFinal()) {
                        app(OwnerWalletService::class)->settle($transaction, $status);
                        if ($status === TransactionStatus::COMPLETED && $transaction->rentPayment) {
                            app(RentPaymentService::class)->handleSuccessfulPayment($transaction->rentPayment);
                        }
                        if ($status === TransactionStatus::COMPLETED && $transaction->visitPass) {
                            app(VisitPassService::class)->handleSuccessfulPayment($transaction->visitPass);
                        }
                    }
                }
            } catch (PawaPayException) {
                return back()->with('warning', 'Le statut sera vérifié ultérieurement.');
            }
        }

        return redirect()->route('tenant.transactions.show', $transaction);
    }

    private function authorizeTransaction(Request $request, Transaction $transaction): void
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);
    }
}
