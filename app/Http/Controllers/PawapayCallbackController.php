<?php

namespace App\Http\Controllers;

use App\Services\PawapayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Handle incoming callbacks from PawaPay.
 *
 * PawaPay sends callbacks for deposit, payout, and refund status updates.
 * This controller processes those callbacks and updates the Transaction model.
 *
 * IMPORTANT:
 * - Always respond quickly (200 OK) to acknowledge receipt
 * - Process heavy work asynchronously if needed
 * - Implement idempotency (same callback can arrive multiple times)
 */
class PawapayCallbackController extends Controller
{
    public function __construct(
        protected PawapayService $pawapay
    ) {}

    /**
     * Handle all PawaPay callbacks (deposits, payouts, refunds).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('PawaPay callback received', [
            'payload' => $payload,
            'headers' => $request->headers->all(),
        ]);

        // TODO: If callback signature verification is enabled, verify here
        // if (config('pawapay.verify_callback_signature')) {
        //     $this->verifySignature($request);
        // }

        // Process the callback
        $transaction = $this->pawapay->handleCallback($payload);

        if (! $transaction) {
            Log::warning('PawaPay callback could not be processed', ['payload' => $payload]);

            // Still return 200 to acknowledge receipt
            return response()->json([
                'status' => 'received',
                'message' => 'Callback received but could not be processed',
            ]);
        }

        // TODO: Trigger any additional business logic here
        // For example:
        // - Send email notification to user
        // - Update related models (VisitPass, RentPayment, etc.)
        // - Trigger refund if payment failed
        // - etc.

        return response()->json([
            'status' => 'success',
            'message' => 'Callback processed successfully',
            'transaction_id' => $transaction->transaction_id,
        ]);
    }

    /**
     * Verify the signature of an incoming callback (RFC-9421).
     *
     * TODO: Implement signature verification if enabled.
     */
    protected function verifySignature(Request $request): void
    {
        // Implementation would go here
        // See: https://docs.pawapay.io/v2/docs/signatures
        // For now, this is a placeholder
    }
}
