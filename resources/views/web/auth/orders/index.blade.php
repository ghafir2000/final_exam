@extends('web.layout')

@section('title', __('Dr.Pets - My Orders'))

@section('content')
<div class="container mt-4">
    <div class="card mx-auto" style="width: 95%;"> {{-- Main container card --}}
        <div class="card-header text-center bg-primary ">
            <h2><i class="fas fa-receipt me-2"></i>@lang('My Orders')</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($orders->count() > 0)
                @foreach ($orders as $order)
                    <div class="card mb-3 order-card shadow-sm">
                        <div class="card-header bg-light order-card-header py-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                                <div class="mb-2 mb-md-0">
                                    <h5 class="mb-0 d-inline-block">
                                        @lang('Order') #{{ $order->id }}
                                    </h5>
                                    <h6 class="ms-2 mr-2 ml-2">{{ \Carbon\Carbon::parse($order->created_at)->format('F d, Y, h:i A') }}</h6>
                                </div>
                                @foreach ($order->cart->products as $productIndex => $product)
                                    <div class="cart-item mb-2 d-flex">
                                        <div style="margin-left: 5px; border-right: 1px solid #dee2e6; border-left: 1px solid #dee2e6;" class="pe-3">
                                            <h6 class="ml-1 mr-1">@lang('Cart Item') #{{ $productIndex + 1 }}</h6>
                                            <h7 class="ml-1 mr-1">{{ $product->name }}</h7>
                                            <hr class="ml-1 mr-1">
    
                                            <h7 class="fw-bold ml-1 mr-1">@lang('Quantity'): {{ $product->pivot->quantity }}</h7>
                                        </div>
                                    </div>
                                    <hr class="mt-0 mb-2">
                                @endforeach
                                
                                <div class="d-flex align-items-center">
                                    <span style="font-weight: bold; color: black; margin-right: 10px;" class="me-3">@lang('Total'): ${{ number_format($order->total, 2) }}</span>
                                    @php
                                        $statusClass = '';
                                        $statusText = \App\Enums\OrderEnums::label($order->status);
                                        switch ($order->status) {
                                            case \App\Enums\OrderEnums::PENDING:
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            case \App\Enums\OrderEnums::ORDERED:
                                                $statusClass = 'bg-success text-white';
                                                break;
                                            case \App\Enums\OrderEnums::CANCELLED:
                                                $statusClass = 'bg-secondary text-white';
                                                break;
                                            default:
                                                $statusClass = 'bg-light text-dark border';
                                        }
                                        if ($order->deleted_at) {
                                            $statusClass = 'bg-danger text-white';
                                            $statusText = __('Cancelled (Soft-deleted)');
                                        }
                                    @endphp
                                    <span class="badge rounded-pill {{ $statusClass }} p-2 px-3 mr-2 ml-2">{{ $statusText }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @if(!$order->deleted_at && $order->status != \App\Enums\OrderEnums::SHIPPED)
                                    <a href="{{ route('order.edit', $order->id) }}" class="btn-custom-2 btn-sm btn-info text-white">
                                        <i class=" fas fa-edit me-1"></i> @lang('Edit Order')
                                    </a>
                                    <form action="{{ route('order.destroy', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('@lang('Are you sure you want to cancel this order?')');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-custom-2 btn-sm btn-danger">
                                            <i class="  fas fa-times-circle me-1"></i> @lang('Cancel Order')
                                        </button>
                                    </form>
                                @elseif($order->deleted_at)
                                    <form action="{{ route('order.restore', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-custom-2 btn-sm btn-success">
                                            <i class=" fas fa-undo me-1"></i> @lang('Restore Order')
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="collapse" id="orderDetails{{ $order->id }}">
                                <h6 class="mt-2 text-secondary">@lang('Order Items'):</h6>
                                @if($order->carts && $order->carts->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 70px;">@lang('Image')</th>
                                                    <th>@lang('Product Name')</th>
                                                    <th>@lang('Seller')</th>
                                                    <th class="text-end">@lang('Price')</th>
                                                    <th class="text-center">@lang('Quantity')</th>
                                                    <th class="text-end">@lang('Subtotal')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->carts as $cartIndex => $cartItem)
                                                @foreach ($cartItem->products as $cartItem)
                                                    @php
                                                        $product = $cartItem;
                                                        $seller = $product->productable->userable ?? null; // Ensure productable and userable exist
                                                        $subtotal = ($product->price ?? 0) * $cartItem->pivot->quantity;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <img src="{{ $product->getFirstMediaUrl('product_image') ? asset($product->getFirstMediaUrl('product_image')) : asset('images/upload_default.jpg') }}" alt="{{ $product->name }}" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                        </td>
                                                        <td>{{ $product->name }}</td>
                                                        <td>{{ $seller->name ?? __('N/A') }}</td>
                                                        <td class="text-end">${{ number_format($product->price, 2) }}</td>
                                                        <td class="text-center">{{ $cartItem->quantity }}</td>
                                                        <td class="text-end fw-bold">${{ number_format($subtotal, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-light text-center">@lang('No items found in this order.')</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                @if (method_exists($orders, 'links'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
                @endif

            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>@lang('You have no orders yet.')
                    <a href="{{ route('product.index') }}" class="btn btn-sm btn-success ms-2">@lang('Shop Now')</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    
    .order-card-header {
        border-bottom: 1px solid #dee2e6;
    }
    .badge.rounded-pill {
        font-size: 0.85em;
    }
</style>
@endsection
