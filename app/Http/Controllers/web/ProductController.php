<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Notifications\ProductAddedToCartNotification;

class ProductController extends Controller
{

    protected $ProductService;

    public function __construct(ProductService $ProductService)
    {
        $this->ProductService = $ProductService;
    }

    public function AddToCart(AddToCartRequest $request)
    {
        // dd($request->all());
        $data = $request->validated();
        $this->ProductService->addToCart($data);
        // Notify the user that the product has been added to the cart
        // $request->user()->notify(new \App\Notifications\ProductAddedToCartNotification());
        // Stay on the product page

        return back()->with('status', 'Product added to cart!');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request->all());
        $data = $request->only(['search','category_id', 'wishable','productable_id', 'productable_type','price', 'price_range']); // Extract filter parameters
        $Products = $this->ProductService->all($data,true,['productable']);
        // dd($Products);
        return view('web.auth.Products.index', compact('Products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('web.auth.products.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProductRequest $request)
    {
        $data = $request->validated();

        $this->ProductService->store($data);
        return redirect()->route('product.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = $this->ProductService->find($id,false,['productable','wishable','categories']);
        return view('web.auth.Products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = $this->ProductService->find($id);
        return view('web.auth.Products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $data = $request->validated();
        $this->ProductService->update($id, $data);
        return redirect()->route('product.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->ProductService->destroy($id);
        return redirect()->route('Product.index');
    }
}
