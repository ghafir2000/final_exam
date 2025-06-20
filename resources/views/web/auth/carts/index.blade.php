@extends('web.layout') 

<title>@lang('Dr.Pets - Your Carts')</title>


@section('content') {{-- Define the content section --}}
<body>
    <div class="container mt-4">
        <div class="card mx-auto" style="width: 80%;">
            <div class="card-header text-center bg-success text-white">
                <h2>@lang('My Shopping Cart')</h2>
            </div>
            <div class="card-body">
                @if($cartProducts->count() > 0)
                    <table class="table table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>@lang('Product')</th>
                                <th>@lang('Name')</th>
                                <th>@lang('Seller')</th>                                
                                <th>@lang('Price')</th>
                                <th>@lang('Quantity')</th>
                                <th>@lang('Subtotal')</th>
                                <th>@lang('Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach ($cartProducts as $cartItem)
                            @php
                                $product = $cartItem->product;
                                $seller = $product->productable->userable; 
                                $subtotal = ($product->price ?? 0) * $cartItem->quantity;
                                $total += $subtotal;
                            @endphp
                            <tr>
                                <td>
                                    <img src="{{ $product->getFirstMediaUrl('product_image') ? asset($product->getFirstMediaUrl('product_image')) : asset('images/upload_default.jpg') }}" alt="{{ $product->name }}" style="width: 70px; height: 70px; object-fit: cover;">
                                </td>
                                <td>{{ $product->name}}</td>
                                <td>{{ $seller->name ?? __('N/A') }}</td>
                                <td>${{ number_format($product->price,0, 2) }}</td>
                                <td>
                                    {{-- Simple display, or add +/- buttons for quantity update --}}
                                    {{ $cartItem->quantity }}
                                </td>
                                <td>${{ number_format($subtotal, 2) }}</td>
                                <td>
                                    <form action="{{ route('cart.destroy', $cartItem->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="@lang('Remove Item')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    {{-- Add update quantity form if needed --}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">@lang('Total'):</td>
                                <td class="fw-bold">${{ number_format($total, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="text-end mt-3">
                        <a href="{{ route('product.index') }}" class="btn btn-secondary">@lang('Continue Shopping')</a>
                        <a href="{{ route('cart.placeOrder') }}" class="btn btn-primary">@lang('Proceed to Checkout')</a> {{-- Assuming checkout route --}}
                    </div>
                @else
                    <p class="text-center">@lang('Your cart is empty. <a href=":link" class="btn btn-primary">Shop Now</a>', ['link' => route('product.index')])</p>
                @endif
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
