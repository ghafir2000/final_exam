<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Enums\OrderEnums;
use App\Enums\PaymentEnums;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class OrderService
{
    protected $paymentService;
    
    public function __construct( PaymentService $paymentService) {
        $this->paymentService = $paymentService;
    }
    
    public function all($data = [], $paginated = true, $withes = [])
    {
        $query = Order::query()
            ->with($withes)->withTrashed()
            ->whereHas('cart', function ($query) use ($data, $withes) {
                if (in_array('cart', $withes)) {
                    $query->withTrashed();
                }
                if (isset($data['customer_id'])) {
                    $query->where('customer_id', $data['customer_id']);
                }
            })
            ->when(isset($data['payable_id']), function ($query) use ($data) {
                return $query->whereHas('payable', function ($query) use ($data) {
                    $query->where('id', $data['payable_id']);
                });
            })
            ->when(isset($data['search']), function ($query) use ($data) {
                return $query->where('id', 'like', "%{$data['search']}%");
            })
            ->latest();

        if ($paginated) {
            return $query->paginate();
        }
        return $query->get();
    }
    public function find($id, $withTrashed = false, $withes = [])
    {
        return Order::with($withes)->withTrashed($withTrashed)->find($id);
    }



    public function updateMany(Collection $cartProducts, $oldOrder)
    {
        $cart = $oldOrder->cart;
        // dd($oldOrder);
        if ($oldOrder) {
            Log::info('OrderService:store - Found old order with id: ' . $oldOrder->id);
            $additionalAmount = round(
                $cartProducts->sum(function($cartProduct) {
                    return $cartProduct->product->price * $cartProduct->quantity;
                }) - $oldOrder->total, 2
            );

            Log::info('OrderService:store - Additional amount: ' . $additionalAmount);
            
            if ($additionalAmount < 0) {
                // Call refund method
                $oldPayment = $oldOrder->payable->where('status', PaymentEnums::SUCCESS)
                ->orWhere('status', PaymentEnums::REFUNDED)
                ->latest()->first();

                $this->paymentService->refund($oldPayment, abs($additionalAmount));
                Log::info('OrderService:store - Refund ' . abs($additionalAmount) . ' for order: ' . $oldOrder->id);
                $oldOrder->update(['total' => $oldOrder->total + $additionalAmount]);
                Log::info('OrderService:store - Updated old order total to: ' . $oldOrder->total);
                return 0;
            }
            if ($additionalAmount == 0) {
                Log::info('OrderService:store - No need to refund, redirect to order.index');
                return 0;
            }

            $newCart = Cart::create(['customer_id' => $cart->customer_id]);

            Log::info('OrderService:store - New cart created with id: ' . $newCart->id);
            
            session()->put('oldOrderId', $oldOrder->id);
            session()->put('follow_up_payment', true);

            
            $order = Order::create(
                [
                    'cart_id' => $newCart->id,
                    'status' => OrderEnums::PENDING,
                    'total' => $additionalAmount
                    ]
                );
                // dd($order->cart->products,$oldOrder->cart->products);
                Log::info('OrderService:store - New order created with id: ' . $order->id);
                return $order;
            }
                
    }

    public function store(Collection $cartProducts) 
    {
        // Create Order
        $cart_id = $cartProducts[0]->cart->id;
        $order = Order::create(
            [
                'cart_id' => $cart_id,
                'status' => OrderEnums::PENDING,
                'total' => $cartProducts->sum(function($cartProduct) {
                    return $cartProduct->product->price * $cartProduct->quantity;
                    })
                ]
            );
        session()->put('follow_up_payment', false);
        
        // dd($cartProducts);

        return $order;
    }



    public function update($order)
    {
        if ($order['status'] == OrderEnums::PENDING) {
            if (!$order) {
                throw new \Exception("Order not found");
            }
            if (($order->cart->customer_id != auth()->user()->userable_id ||
            "App\Models\Customer" != auth()->user()->userable_type) && auth()->user()->userable_type != 'App\Models/Admin') {
            throw new \Exception("You don't have permissions to update this order");
        }
            

            $order->update(Arr::except($order, 'id'));

            return $order;
        }

    }
    
    public function destroy($id)
    {
        // Find Order with `withTrashed` to handle soft deletes
        $order = Order::find($id);
        if (!$order) {
            throw new \Exception("Order not found");
        }

         if (($order->cart->customer_id != auth()->user()->userable_id ||
            "App\Models\Customer" != auth()->user()->userable_type) && auth()->user()->userable_type != 'App\Models/Admin') {
            throw new \Exception("You don't have permissions to delete this order");
        }
        $order->delete();
        return $order;
    }

    public function restore($id)
    {
        // Find the Order with soft-deleted records
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            throw new \Exception("order not found");
        }

        if (($order->cart->customer_id != auth()->user()->userable_id ||
            "App\Models\Customer" != auth()->user()->userable_type) && auth()->user()->userable_type != 'App\Models/Admin') {
            throw new \Exception("You don't have permissions to delete this order");
        }

        // Restore the order
        $order->restore();
    }
    
}

