<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;

class OrderController extends Controller
{
    
    protected $orderService;
    protected $paymentService;
    
    public function __construct(OrderService $orderService, PaymentService $paymentService) {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = $this->orderService->all(['customer_id' => auth()->user()->userable_id],true, ['cart','cart.products','cart.products.productable']);
        $success = $request->session()->get('success');
        // dd($orders);
        return view('web.auth.orders.index',compact('orders','success'));
    }
    public function edit(string $id)
    {
        $order = $this->orderService->find($id,true,['cart', 'cart.products', 'cart.products.productable']);
        return view('web.auth.orders.edit',compact('order'));

    }


    /**
     * Update the specified resource in storage.
     */    
    public function update(UpdateOrderRequest $request, $id)
    {
        $data = $request->validated();
        $this->orderService->update($id, $data);
        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->orderService->destroy($id);
        return redirect()->route('order.index');
    }

    public function restore(string $id)
    {
        $this->orderService->restore($id);
        return redirect()->route('order.index');
    }
}
