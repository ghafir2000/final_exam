@extends('web.layout') 

<title>@lang('Dr.Pets -') {{ $product->name }}</title>


@section('content') {{-- Define the content section --}}
<head>
    <style>
        .product-image-main {
            max-height: 230px;
            object-fit: contain;
        }
    </style>
</head>

<body>
           
        @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    <div class="container mt-5">
        <div class="card">
            <div class="row g-0">
                <div class="col-md-5 text-center p-3">
                    <img src="{{ $product->getFirstMediaUrl('product_image') ? asset($product->getFirstMediaUrl('product_image')): asset('images/upload_default.jpg') }}"
                         class="img-fluid rounded product-image-main" alt="{{ __(':name\'s product image', ['name' => $product->name]) }}">
                    {{-- Add gallery for multiple images if available --}}
                </div>
                <div class="col-md-7">
                    <div class="card-body ">
                        <h2 class="card-title">{{ $product->name }}</h2>

                        @if($product->categories->isNotEmpty())
                        <p class="text-black-muted">
                            @lang('Categories'):
                            @foreach($product->categories as $category)
                                <span class="badge bg-secondary">{{ $category->name }}</span>
                            @endforeach
                        </p>
                        @endif

                        <p class="card-text fs-4 text-success fw-bold">{{ __('Price: :price', ['price' => number_format($product->price, 0, 2)]) }}</p>

                        <p class="card-text">{{ $product->description ?? __('No description available.') }}</p>
                        
                        {{-- Display other productable-specific details if any --}}
                        <p><strong>@lang('Provider'):</strong> {{ $product->productable->userable->name ?? __('N/A') }}</p>
                        
                        
                        <div class="d-flex align-items-center ">
                        <form action="{{ route('user.show',$product->productable->userable->id) }}" method="GET">
                            @csrf
                            <button type="submit" class="btn btn-success me-2 ml-2">@lang('Show Profile')</button>
                        </form>
                        @auth
                            @if(auth()->user()->id !== $product->productable->userable->id)
                            <form action="{{ route('chat.store', ['chatable_id' => $product->productable->userable->id, 'chatable_type' => get_class($product->productable->userable)]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-info me-2 ml-2">@lang('Chat')</button>
                            </form>
                            @endif
                            @if (auth()->user() && $product->productable->userable->userable_id === auth()->user()->userable_id && $product->productable_type === get_class(auth()->user()->userable))
                            <form action="{{ route('product.edit',$product->id) }}" method="GET">
                                @csrf
                                <button type="submit" class="btn btn-warning me-2 ml-2">@lang('Edit Product')</button>
                            </form>
                            @endif
                        </div>
                        @if (auth()->user() && Auth()->user()->userable_type == "App\Models\Customer") {{-- Assuming wishable is per user --}}
                        <div class="d-flex align-items-center flex-wrap"> {{-- Main flex container, align items vertically to center, allow wrapping --}}

                            {{-- Add to Cart Form Group --}}
                            <form action="{{ route('product.addToCart')}}" method="POST" class="d-flex align-items-center me-3 mb-2 mb-md-0"> {{-- Flex for its children, margin-end for spacing, margin-bottom for mobile --}}
                                @csrf
                                <label for="quantity" class="form-label me-2">@lang('Quantity'):</label> {{-- Remove bottom margin from label, add margin-end --}}
                                <input type="number" name="quantity" id="quantity" value="1" min="1" class="form-control form-control-sm" style="width: 50px;"> {{-- Use form-control-sm for smaller height, adjust width --}}
                                <input type="hidden" name="product_id" value="{{$product->id}}">
                                <button type="submit" class="btn btn-warning btn-sm ms-2 ml-2"> {{-- Use btn-sm, add margin-start --}}
                                    <i class="fas fa-cart-plus"></i> @lang('Add to Cart')
                                </button>
                            </form>

                            {{-- Wishlist Form Group --}}
                            {{-- No need for the extra span here if the form is display: inline or display: inline-block naturally or via flex item --}}
                            @php
                                // Example: Check if current user has wishlisted this product
                                $isWished = $product->wishable()->where('user_id', auth()->user()->id)->exists();
                            @endphp
                            <form action="{{ $isWished ? route('wish.update') : route('wish.store') }}" method="POST" class="d-inline mb-2 mb-md-0"> {{-- d-inline is fine, or it becomes a flex item. Margin-bottom for mobile --}}
                                @csrf
                                @if($isWished) @method('PUT') @endif
                                <input type="hidden" name="wishable_id" value="{{ $product->id }}">
                                <input type="hidden" name="wishable_type" value="{{ get_class($product) }}">
                                <button type="submit" class="btn btn-outline-danger btn-sm ml-2"> {{-- Use btn-sm to match other elements --}}
                                    <i class="fas fa-heart{{ $isWished ? '-broken' : '' }}"></i>
                                    {{ $isWished ? __('Remove from Wishlist') : __('Add to Wishlist') }}
                                </button>
                            </form>
                            @endauth

                        </div>

                        @endif
                    </div>
                </div>
            </div>
        </div>

       
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
        integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
