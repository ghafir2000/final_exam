<?php

namespace App\Services;

use Stripe\Stripe;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Payment;
use App\Enums\PaymentEnums;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;


class PaymentService
{


    public function find($id, $withTrashed = false, $withes = [])
    {
        $payment = Payment::with($withes)->withTrashed($withTrashed)->find($id);
        if(isset($payment->payable)) {
            $payment->payable instanceof Booking ? $payment->payable->load('service.servicable') : null ;
            $payment->payable instanceof Order ? $payment->payable->load('cart.products.productable') : null;
        }
        return $payment;
    }

    public function create($payable)
    {

        Stripe::setApiKey(config('services.stripe.secret'));
        // dd($payable);
        if ($payable instanceof Booking) {
            $this->createBookingPayment($payable);
        }
        if ($payable instanceof Order) {
            $this->createOrderPayment($payable);
        }
    }

    public function refund(Payment $payment, string $refundAmount) // Ensure $refundAmount is in CENTS
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            // Ensure $refundAmount is an integer representing cents
            $amountInCents = (int) $refundAmount *100;
            if ($amountInCents <= 0) {
                throw new \InvalidArgumentException('Refund amount must be a positive integer in cents.');
            }

            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->stripe_payment_intent_id,
                'amount' => $amountInCents, // Stripe expects amount in cents
                // Optional: Add a reason and metadata for better tracking
                // 'reason' => 'requested_by_customer',
                // 'metadata' => [
                //     'internal_payment_id' => $payment->id,
                //     'order_id' => $payment->order_id, // If applicable
                // ]
            ]);

            Log::info('Stripe Refund object created.', [
                'payment_id' => $payment->id,
                'stripe_refund_id' => $refund->id,
                'stripe_refund_status' => $refund->status, // Log the initial status
                'stripe_refund_amount' => $refund->amount,
                'stripe_payment_intent_id' => $refund->payment_intent
            ]);

            // --- Check the refund status ---
            if ($refund->status == 'succeeded') {
                $payment->update(['status' => PaymentEnums::REFUNDED]);
                Log::info('Refund processed and succeeded immediately by Stripe.', ['payment_id' => $payment->id, 'refund_id' => $refund->id]);
            } elseif ($refund->status == 'pending') {
                // The refund is accepted but still processing.
                // You might have a specific status like PaymentEnums::REFUND_PENDING
                // $payment->update(['status' => PaymentEnums::REFUND_PENDING]);
                Log::info('Refund is pending with Stripe.', ['payment_id' => $payment->id, 'refund_id' => $refund->id]);
                // You'll rely on webhooks (see below) for final confirmation of 'succeeded' or 'failed'.
            } elseif ($refund->status == 'failed') {
                // The refund failed immediately according to Stripe.
                Log::error('Stripe refund creation reported as FAILED immediately.', [
                    'payment_id' => $payment->id,
                    'refund_id' => $refund->id,
                    'failure_reason' => $refund->failure_reason, // Important for debugging
                    'stripe_refund_object' => $refund->toArray() // Log the whole object for details
                ]);
                // Do NOT mark your local payment as refunded.
                // You might throw an exception here to indicate the failure clearly.
                throw new \Exception('Stripe reported refund as failed: ' . ($refund->failure_reason ?? 'Unknown reason'));
            } else {
                // Unexpected status, should be investigated
                Log::warning('Stripe refund created with unexpected status.', [
                    'payment_id' => $payment->id,
                    'refund_id' => $refund->id,
                    'status' => $refund->status,
                    'stripe_refund_object' => $refund->toArray()
                ]);
                // Handle as an error or a pending state needing manual review.
                // For safety, might treat as an error.
                throw new \Exception('Stripe refund created with unexpected status: ' . $refund->status);
            }



        } catch (\Stripe\Exception\ApiErrorException $e) { // Catch specific Stripe API errors
            Log::error('Stripe API error during refund', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
            ]);
            // If the error is card_declined or similar, $e->getStripeCode() can be useful.
            throw new \Exception('Refund API call failed: ' . $e->getMessage());
        } catch (\InvalidArgumentException $e) { // Catch your own validation errors
            Log::error('Invalid argument for refund', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            throw $e; // Re-throw
        } catch (\Exception $e) { // Catch other general exceptions
            Log::error('Generic error during refund', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            throw new \Exception('Refund failed: ' . $e->getMessage());
        }
    }
    public function createBookingPayment(Booking $booking)
    {
        // Create Payment entry
        $payment = Payment::create([
            'payable_id' => $booking->id,
            'payable_type' => Booking::class,
            'total' => $booking->service->price, // Ensure price exists
            'status' => PaymentEnums::PENDING 
        ]);

        // Initiate Stripe payment
        Log::info('Creating Stripe payment session');
        $session = Session::create([
            'payment_method_types' => ['card'],
            'metadata' => ['payment_id' => $payment->id],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Booking Payment'],
                    'unit_amount' => $payment->total * 100, // Stripe uses cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}&payment=' . $payment->id,
            'cancel_url' => route('payment.cancel', ['payment' => $payment->id]),
        ]);

        Log::info('Stripe payment session created', ['session_id' => $session->id]);
        $payment->update(['stripe_session_id' => $session->id]);
        Log::info('Redirecting to Stripe: ' . $session->url);
        exit(header("Location: {$session->url}"));

    }

    public function createOrderPayment(Order $order)
    {
        // Calculate total price from all carts in the order

        // Create Payment entry
        $payment = Payment::create([
            'payable_id' => $order->id,
            'payable_type' => get_class($order),
            'total' => $order->total, // Ensure total price is calculated
            'status' => PaymentEnums::PENDING
        ]);

        // Prepare line items for Stripe
        $lineItems = [];
        foreach ($order->cart->products as $product) {
            try {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $product->name],
                        'unit_amount' => $product->price * 100, // Stripe uses cents
                    ],
                    'quantity' => $product->pivot->quantity,
                ];
            } catch (\Exception $e) {
                Log::error('Error creating Stripe payment line item for product: ' . $product->id, ['error' => $e->getMessage()]);
            }
        }
        if (empty($lineItems)) {
            Log::error('No line items created, processing as a follow up payment instead');
            $lineItems = [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Follow-up Payment'],
                    'unit_amount' => $payment->total * 100, // Stripe uses cents
                ],
                'quantity' => 1,
            ]];
        }

        // Initiate Stripe payment
        Log::info('Creating Stripe payment session');
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'metadata' => ['payment_id' => $payment->id],
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', ['payment' => $payment->id]),
        ]);

        Log::info('Stripe payment session created', ['session_id' => $session->id]);
        $payment->update(['stripe_session_id' => $session->id]); 
        Log::info('Redirecting to Stripe: ' . $session->url);
        exit(header("Location: {$session->url}"));
    }
}
