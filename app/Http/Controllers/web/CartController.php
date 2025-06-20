<?php

namespace App\Http\Controllers\web;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\CartProduct;

class CartController extends Controller
{
    protected $cartService,$paymentService,$orderService;
    public function __construct(CartService $cartService,PaymentService $paymentService,OrderService $orderService)
    {
        $this->middleware('auth');
        
        $this->cartService = $cartService;
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('markAsRead')) {
            $notificationIdToMark = $request->query('markAsRead');
            if ($request->user()) {
                $notification = $request->user()->notifications()->where('id', $notificationIdToMark)->first();
                if ($notification) {
                    $notification->markAsRead();
                    // Optional: Redirect to the same URL without the markAsRead query param
                    // This cleans up the URL bar after marking as read.
                    // return redirect()->to($request->url()); // Redirects to current URL without query params
                }
            }
        }
        if(Auth()->user()->userable_type !== 'App\Models\Customer' &&
             Auth()->user()->userable_type !== 'App\Models\Admin'){
                throw new \Exception('unauthorized');
            }
        $cartProducts = $this->cartService->all(['customer_id' => auth()->user()->userable_id],false, ['product','product.productable']);

        return view('web.auth.carts.index',compact('cartProducts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartRequest $request, string $id)
    {
        $data = $request->validated();
        $this->cartService->update($id, $data);
        return redirect()->route('cart.index');
    }



    public function updateMany(Request $request)
    {
        $data = $request->validate([
            'cartProducts' => 'required|array',
            'order_id' => 'required|integer'
        ]);
        $order = $this->orderService->find($data['order_id']);
        $cartProducts = $this->cartService->updateMany($data['cartProducts']);
        $order = $this->orderService->updateMany(collect($cartProducts),$order);
        if(!$order){
            return redirect()->route('order.index')->with('success', 'Order updated successfully, if you have any refunds due, you will receive them shortly');
        }
        return redirect()->route('payment.create', [
            'payable_id' => $order->id,
            'payable_type' => Order::class
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */

    public function destroyMany(Request $request)
    {
        $ids = $request->ids;
        $this->cartService->destroyMany($ids);
    }

    public function destroy(string $id)
    {
        $this->cartService->destroy($id);
        return back()->with('status', 'cart deleted!');
    }


    public function placeOrder()
    {
        $cartProducts = $this->cartService->all(
            ['customer_id' => auth()->user()->userable_id],false, ['product','product.productable']);
        // dd($cartProducts);
        $order = $this->orderService->store($cartProducts);
        
        return redirect()->route('payment.create', [
            'payable_id' => $order->id,
            'payable_type' => Order::class
        ]);  
    }
}
