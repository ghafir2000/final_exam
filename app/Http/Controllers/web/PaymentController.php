<?php

namespace App\Http\Controllers\web;

use Stripe\Stripe;
use App\Models\Order;
use App\Enums\PaymentEnums;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Services\CartService;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;


class PaymentController extends Controller
{
    protected $bookingService, $paymentService, $cartService;
    
    public function __construct(BookingService $bookingService, PaymentService $paymentService, CartService $cartService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->cartService = $cartService;
    }

    public function show($id) 
    {
        $payment = $this->paymentService->find($id, false, ['payable']);
        return view('web.payment', compact('payment'));
    }
    
    public function create(Request $request)
    {
        // dd($request->all());
        $payableType = $request->input('payable_type');
        $payableId = $request->input('payable_id');
    
        // Validate that the type exists
        if (!class_exists($payableType)) {
            abort(404, 'Invalid payment type');
        }
    
        // Retrieve the correct model instance
        $payable = (new $payableType)->findOrFail($payableId);
        // dd($payable);
    
        // Create the payment
        $this->paymentService->create($payable);
    
    }

    public function refund(Request $request)
    {
        $payment = $this->paymentService->find(request('payment'));
        $this->paymentService->refund($payment,$payment->total);
        $payment->update(['status' => PaymentEnums::REFUNDED]);
        return view('web.Response', compact('payment'));
    }

    public function success(Request $request)
    {
        $stripeSessionId = request('session_id');
        $isFollowUp = session()->get('follow_up_payment');
        session()->forget('follow_up_payment');
        Stripe::setApiKey(config('services.stripe.secret'));
        $stripeCheckoutSession = Session::retrieve($stripeSessionId);
        $payment = $this->paymentService->find($stripeCheckoutSession->metadata['payment_id']);
        $payment->update(['status' => PaymentEnums::SUCCESS,
                        'stripe_payment_intent_id' => $stripeCheckoutSession->payment_intent]);
        if ($payment->payable instanceof \App\Models\Order) {
            $payment->payable->update(['status' => \App\Enums\OrderEnums::ORDERED]);
            $this->cartService->destroyMany($payment->payable->cart);
            $payment->payable->cart->delete();
            if($isFollowUp)
            {
                $oldOrderId = session()->get('oldOrderId');
                $oldOrder = Order::findOrFail($oldOrderId);
                $this->cartService->destroyMany( $payment->payable->cart);
                $oldOrder->update(['total' => $oldOrder->total + $payment->payable->total]);
                $payment->payable->forceDelete();
            }
        }
        if ($payment->payable instanceof \App\Models\Booking) {
            $payment->payable->update(['status' => \App\Enums\BookingEnums::BOOKED]);
        }
        
        return view('web.Response', compact('payment'));
    }

    public function cancel()
    {
        $payment = $this->paymentService->find(request('payment'));
        $payment->update(['status' => PaymentEnums::CANCELLED]);
        return view('web.Response', compact('payment'));
    }
}

