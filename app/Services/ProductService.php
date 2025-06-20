<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ProductAddedToCartNotification;

class ProductService
{
    protected $imageService;
    public function __construct(ImageService $imageService) {
        $this->imageService = $imageService;
    }

    public function all($data = [], $paginated = false, $withes = [])
    {

        $query = Product::query()
            ->with($withes)
            ->when(isset($data['category_id']), function ($query) use ($data) {
                return $query->whereHas('categories', function ($subQuery) use ($data) {
                    $subQuery->where('categories.id', $data['category_id']); // Corrected line
                });
            })
            ->when(isset($data['productable_id']), function ($query) use ($data) {
                // This assumes 'productable' is the morphTo relation name on the Service model.
                $query->whereHas('productable', function ($query_productable) use ($data) {
                    $query_productable->where('id', $data['productable_id']);
                });
            })
            ->when(isset($data['wishable']), function ($query) {
                return $query->whereHas('wishable',function ($query) {
                    $query->where('user_id', Auth()->user()->id);
                });
            })
            ->when(isset($data['productable_type']), function ($query) use ($data) {
                return $query->where('productable_type', $data['productable_type']);
            })
            ->when(isset($data['search']), function ($query) use ($data) {
                return $query->where('name', 'like', "%{$data['search']}%");
            })

        ->when(!empty($data['price_range']) && is_array($data['price_range']), function ($q) use ($data) {
            if (isset($data['price_range']['min']) && is_numeric($data['price_range']['min'])) {
                $q->where('price', '>=',(int)  $data['price_range']['min']);
            }
            if (isset($data['price_range']['max']) && is_numeric($data['price_range']['max'])) {
                $q->where('price', '<=', (int) $data['price_range']['max']);
            }
        });

        // Order by Price or default to latest
        if (isset($data['price'])) {
            // dd($data['price']);
            $query->orderBy('price', $data['price']);
            // dd($query->get());
        } else {
            $query->latest(); // Default order (created_at desc) if no price sort specified
        }

        if ($paginated) {
            $paginator = $query->paginate(10); // Or your preferred number, e.g., config('pagination.default', 15)
            $paginator->appends($data);

            return $paginator;
        }

        return $query->get();
    }

    public function find($id, $withTrashed = false, $withes = [])
    {

        return Product::with($withes)
            ->withTrashed($withTrashed)
            ->find($id);
    }

    public function store($data)
    {
        $data['productable_id'] = auth()->user()->userable_id;
        $data['productable_type'] = get_class(auth()->user()->userable);
        $product = Product::create(Arr::except($data, 'product_picture'));
        
        if (isset($data['product_picture'])) {
            $this->imageService->store($product, $data['product_picture'], 'product_picture');
        }
        
        $product->categories()->sync($data['categories']);
        return $product;
    }

    public function update($id, $data)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new \Exception("Product not found");
        }
        if (auth()->user() && $product->productable->userable->userable_id === auth()->user()->userable_id &&
             $product->productable_type === get_class(auth()->user()->userable))
        {
            
            $product->update($data);
            return $product;
        }
        else
        {
            throw new \Exception("You are not authorized to update this product");
        }
    }

    public function destroy($id)
    {
        $product = Product::withTrashed()->find($id);
        if (!$product) {
            throw new \Exception("Product not found");
        }
        if (auth()->user() && $product->productable->userable->userable_id === auth()->user()->userable_id &&
             $product->productable_type === get_class(auth()->user()->userable))
        {
            $product->delete();
            return $product;
        }
        else
        {
            throw new \Exception("You are not authorized to delete this product");
        }
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->find($id);

        if (!$product) {
            throw new \Exception("Product not found");
        }
        if (auth()->user() && $product->productable->userable->userable_id === auth()->user()->userable_id &&
             $product->productable_type === get_class(auth()->user()->userable))
        {
            $product->restore();
            return $product;
        }
        else
        {
            throw new \Exception("You are not authorized to restore this product");
        }

    }

    public function AddToCart($data)
    {
        $customer = Auth()->user()->userable;
        if (!$customer instanceof \App\Models\Customer) {
            throw new \Exception("Only customer can add product to cart");
        }
        $product = Product::find($data['product_id']);
        if (!$product) {
            throw new \Exception("Product not found");
        }
        // dd($data);
        $cart = $customer->cart()->whereNull('deleted_at')->firstOrCreate();
        $cart->products()->attach($product->id, ['quantity' => $data['quantity']]);

        $customer->userable->notify(new ProductAddedToCartNotification($product));

    }
}

