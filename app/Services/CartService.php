<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function all($data = [], $paginated = true, $withes = [])
    {

        $query = CartProduct::query()
            ->with($withes)
            ->whereNull('deleted_at')
            ->whereHas('cart', function ($cartQuery) use ($data) {
                $cartQuery->when(isset($data['customer_id']), function ($query) use ($data) {
                    return $query->where('customer_id', $data['customer_id']);
                })
                ->when(isset($data['order_id']), function ($query) use ($data) {
                    return $query->where('order_id', $data['order_id']);
                });
            })
            ->latest();

        if ($paginated) {
            return $query->paginate();
        }
        return $query->get();
    }

    public function find($id, $withTrashed = false, $withes = [])
    {
        $cartProduct = CartProduct::with($withes)
            ->withTrashed($withTrashed)
            ->find($id);
        if (!$cartProduct) {
            throw new \Exception("Cart not found");
        }
        if (auth()->user()->userable_type != \App\Models\Customer::class ||
         auth()->user()->userable_id != $cartProduct->cart->customer_id) {
            throw new \Exception("Unauthorized action");
        }

        return $cartProduct;
    }


    public function update($id, $data)
    {
        $cart = $this->find($id,true);
        if (!$cart) {
            throw new \Exception("Cart not found");
        }
        $cart->order_id == null ? $cart->update($data) :
         throw new \Exception("You can't update this cart, order is already placed");
        return $cart;
    }

    
    public function updateMany($cartProducts)
    {
        $updatedCarts = [];
        // dd($carts);
        foreach ($cartProducts as $cartProduct) {
            $cartInDB = $this->find($cartProduct['id'], true);
            $updatedCart = $this->update($cartProduct['id'], $cartProduct);
            if ($cartProduct['quantity'] > 0) {
                if ($cartInDB && $cartInDB->deleted_at) {
                    $cartInDB->restore();
                }
                $updatedCarts[] = $updatedCart;
            } else {
                if ($cartInDB && !$cartInDB->deleted_at) {
                    $cartInDB->delete();
                }
            }
        }
        return $updatedCarts;
    }
    


    public function destroy($id)
    {
        $cartProduct = CartProduct::where('id', $id)->first();
        if (!$cartProduct) {
            throw new \Exception("Cart product not found");
        }
        $cartProduct->forceDelete();
        return $cartProduct;
    }

    public function destroyMany($cart)
    {
        $cartProducts = [];
            // dd($carts);
            $cartProducts = CartProduct::where('cart_id', $cart['id'])->get();
            foreach ($cartProducts as $cartProduct) {
                // dd($cartProduct);
                $cartProduct->delete();
            }
        return $cartProducts;
    }

    public function restore($id)
    {
        $cartProduct = CartProduct::withTrashed()->where('id', $id)->first();

        if (!$cartProduct) {
            throw new \Exception("Cart product not found");
        }

        $cartProduct->restore();
    }
}

