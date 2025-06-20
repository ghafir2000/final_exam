@extends('web.layout')

{{-- Pushing title to the layout's title section/stack if it exists --}}
@push('title')
    <title>{{ __('Dr.Pets - Our Products') }}</title>
@endpush

{{-- Pushing styles to a 'styles' stack in your layout --}}
@push('styles')
<style>
    .product-card img {
        height: 200px;
        object-fit: cover;
    }
    /* Add any other page-specific styles here */
</style>
@endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    <div class="container mt-4">
        <div class="card mx-auto">
            <div class="card-header text-center bg-primary text-white">
                <h2>{{ __('Our Products') }}</h2>
            </div>
            <div class="card-body">
                {{-- Filtering Form --}}
                <form method="GET" action="{{ route('product.index') }}" class="mb-4 p-3 border rounded">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search Products') }}</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="{{ __('e.g., Dog Food, Cat Toy') }}" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">{{ __('Category') }}</label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="">{{ __('All Categories') }}</option>
                                @if(isset($allCategories))
                                    @foreach ($allCategories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @else
                                    @php
                                        $uniqueCategories = collect();
                                        if ($Products->count() > 0) {
                                            foreach ($Products as $productItem) {
                                                if ($productItem->categories) {
                                                    foreach ($productItem->categories as $category) {
                                                        $uniqueCategories->put($category->id, $category);
                                                    }
                                                }
                                            }
                                        }
                                        $uniqueCategories = $uniqueCategories->sortBy('name');
                                    @endphp
                                    @foreach ($uniqueCategories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label for="productable_type" class="form-label">{{ __('Provider Type') }}</label>
                            <select name="productable_type" id="productable_type" class="form-control">
                                <option value="">{{ __('Any') }}</option>
                                @foreach ($Products->pluck('productable_type')->unique()->filter() as $type)
                                    <option value="{{ $type }}" {{ request('productable_type') == $type ? 'selected' : '' }}>{{ class_basename($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="wishable" class="form-label">{{ __('Wishlist') }}</label>
                            <select name="wishable" id="wishable" class="form-control">
                                <option value="">{{ __('Any') }}</option>
                                <option value="1" {{ request('wishable') == '1' ? 'selected' : '' }}>{{ __('In Wishlist') }}</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-12">
                            <label for="price" class="form-label">{{ __('Sort by Price') }}</label>
                            <select name="price" id="price" class="form-control">
                                <option value="">{{ __('Default') }}</option>
                                <option value="asc" {{ request('price') == 'asc' ? 'selected' : '' }}>{{ __('Ascending') }}</option>
                                <option value="desc" {{ request('price') == 'desc' ? 'selected' : '' }}>{{ __('Descending') }}</option>
                            </select>
                        </div>
                        <div class="col-lg-5 col-md-8 price-slider-group">
                            <label for="price_range_min" class="form-label">{{ __('Price Range') }}</label>
                            <div class="price-slider d-flex align-items-center">
                                @php
                                    $minPriceProduct = $Products->min('price') ?? 0;
                                    $maxPriceProduct = $Products->max('price') ?? 1000;
                                    $currentMin = request('price_range.min', $minPriceProduct);
                                    $currentMax = request('price_range.max', $maxPriceProduct);
                                @endphp
                                <output for="price_range_min" id="minPriceOutput" class="me-2">{{ intval($currentMin) }}</output>
                                <input type="range" name="price_range[min]" id="price_range_min"
                                       min="{{ intval($minPriceProduct) }}"
                                       max="{{ intval($maxPriceProduct) }}"
                                       value="{{ intval($currentMin) }}"
                                       class="form-range flex-grow-1" oninput="document.getElementById('minPriceOutput').value = this.value">
                                <span class="mx-2">-</span>
                                <input type="range" name="price_range[max]" id="price_range_max"
                                       min="{{ intval($minPriceProduct) }}"
                                       max="{{ intval($maxPriceProduct) }}"
                                       value="{{ intval($currentMax) }}"
                                       class="form-range flex-grow-1" oninput="document.getElementById('maxPriceOutput').value = this.value">
                                <output for="price_range_max" id="maxPriceOutput" class="ms-2">{{ intval($currentMax) }}</output>
                            </div>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                        </div>
                         <div class="col-md-1 align-self-end">
                            <a href="{{ route('product.index') }}" class="btn btn-outline-secondary w-100" title="{{ __('Clear Filters') }}">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>

                @if($Products->count() > 0)
                    <div class="row">
                        @foreach ($Products as $product)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
                            <div class="card h-100 product-card shadow-sm">
                                <a href="{{ route('product.show', $product->id) }}">
                                    <img src="{{ $product->getFirstMediaUrl('product_image') ?: asset('images/upload_default.jpg') }}"
                                         class="card-img-top" alt="{{ $product->productable->name ?? $product->name ?? __('Product Image') }}">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <a href="{{ route('product.show', $product->id) }}" class="text-decoration-none text-dark">
                                            {{ Str::limit($product->productable->name ?? $product->name ?? __('Unnamed Product'), 40) }}
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted small">
                                        @if($product->categories && $product->categories->isNotEmpty())
                                            {{ $product->categories->first()->name }}
                                        @else
                                            {{ __('General') }}
                                        @endif
                                    </p>
                                    <h6 class="card-subtitle mb-2 text-success">${{ number_format($product->productable->price ?? $product->price ?? 0, 2) }}</h6>
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('product.show', $product->id) }}" class="btn btn-sm btn-outline-primary mb-2 w-100">{{ __('View Details') }}</a>
                                            @auth
                                                @if (auth()->user() &&
                                                 $product->productable->userable->userable_id === auth()->user()->userable_id &&
                                                 $product->productable_type === get_class(auth()->user()->userable))
                                                    <form action="{{ route('product.edit',$product->id) }}" method="GET">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning w-100">{{ __('Edit Product') }}</button>
                                                    </form>
                                                @endif
                                                @if(Auth::user()->userable_type === 'App\\Models\\Customer')
                                                    <form action="{{ route('product.addToCart')}}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{$product->id}}">
                                                        <input type="hidden" name="quantity" value="1">
                                                        <button type="submit" class="btn btn-sm btn-warning w-100">
                                                            <i class="fas fa-cart-plus"></i> {{ __('Add to Cart') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if (method_exists($Products, 'links'))
                    <div class="d-flex justify-content-center mt-4">
                        {{ $Products->appends(request()->query())->links() }}
                    </div>
                    @endif
                @else
                    <p class="text-center">{{ __('No Products found matching your criteria.') }} <a href="{{ route('product.index') }}">{{ __('View All Products') }}</a></p>
                @endif
            </div>
        </div>
    </div>
@endsection

