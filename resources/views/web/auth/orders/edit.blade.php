@extends('web.layout')

@section('title', __('Dr.Pets - Edit Order #') . $order->id)

@section('styles')
<style>
    body.dark-mode td { color:rgb(255, 255, 255) !important; }
    body.dark-mode .text-primary { color:rgb(0, 0, 0) !important; } /* Consider using theme variables if possible */
    body.dark-mode .text-primary-2 { color:rgb(0, 0, 0) !important; font-weight: bold; } /* Consider using theme variables */
</style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="card mx-auto" style="width: 90%;">
        <div class="card-header text-center bg-info text-white">
            <h2><i class="fas fa-edit me-2"></i>@lang('Edit Order') #{{ $order->id }}</h2>
        </div>
        <div class="card-body p-4">
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
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">@lang('Please correct the errors below:')</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Ensure OrderEnums::SHIPPED is the correct status to prevent editing --}}
            {{-- You might want to allow editing for specific statuses like PENDING, PROCESSING etc. --}}
            {{-- e.g., @if(in_array($order->status, [\App\Enums\OrderEnums::PENDING, \App\Enums\OrderEnums::AWAITING_PAYMENT])) --}}
            @if($order->status != \App\Enums\OrderEnums::SHIPPED)
                <form action="{{ route('cart.updateMany') }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Or POST if your route is defined as POST --}}

                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    {{-- This assumes $order->carts returns a collection of Cart models,
                         and each Cart model has a 'products' relationship. --}}
                    @if($order->cart->products && $order->cart->products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 80px;">@lang('Product')</th>
                                        <th>@lang('Name')</th>
                                        <th class="text-end">@lang('Price')</th>
                                        <th style="width: 150px;" class="text-center">@lang('Quantity')</th>
                                        <th class="text-end">@lang('Subtotal')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $currentTotal = 0; @endphp
                                    {{-- Loop 1: Iterating through Cart models associated with the Order --}}
                                    @foreach ($order->cart->products as $productIndex => $product)
                                        @php
                                            // Ensure $product->pivot is not null and has quantity.
                                            // If $product->pivot is null, there's an issue with your relationship setup or data.
                                            $quantity = $product->pivot->quantity ?? 0;
                                            $price = $product->price ?? 0;
                                            $subtotal = $price * $quantity;
                                            $currentTotal += $subtotal;
                                        @endphp
                                        <tr>
                                            <td>
                                                <img src="{{ $product->getFirstMediaUrl('product_image') ? $product->getFirstMediaUrl('product_image') : asset('images/upload_default.jpg') }}" alt="{{ $product->name ?? 'Product Image' }}" class="img-fluid rounded" style="width: 70px; height: 70px; object-fit: cover;">
                                            </td>
                                            <td>{{ $product->name ?? 'N/A' }}</td>
                                            <td class="text-end">${{ number_format($price, 2) }}</td>
                                            <td class="text-center">
                                                {{--
                                                    CRITICAL FOR SUBMISSION:
                                                    - '$product->pivot->id' MUST be the unique ID of the cart_product row.
                                                    - Ensure your Cart model's 'products' relationship uses ->withPivot('id', 'quantity').
                                                    - Ensure your 'cart_product' table HAS an 'id' primary key column.
                                                    - If $product->pivot or $product->pivot->id is null, this form submission will break.
                                                --}}
                                                @if($product->pivot && isset($product->pivot->id))
                                                    <input type="hidden" name="cartProducts[{{ $product->pivot->id }}][id]" value="{{ $product->pivot->id }}">
                                                    <input type="number" name="cartProducts[{{ $product->pivot->id }}][quantity]" class="form-control form-control-sm text-center quantity-input" value="{{ $quantity }}" min="0" data-price="{{ $price }}" style="width: 80px; margin: auto;">
                                                    <small class="form-text text-muted d-block mt-1">@lang('0 to remove')</small>
                                                    @else
                                                    <span class="text-danger">@lang('Item data error')</span>
                                                    {{-- Log this issue for debugging if it occurs --}}
                                                    {{-- Log::error('Missing pivot data for product ID: ' . $product->id . ' in cart ID: ' . $cartItem->id); --}}
                                                @endif
                                            </td>
                                            <td class="text-end subtotal-cell fw-bold">${{ number_format($subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="4" class="text-primary">@lang('Original Order Total'):</td>
                                        <td class="text-primary">${{ number_format($currentTotal, 2) }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="4" class="fw-bold text-primary-2">@lang('New Calculated Total'):</td>
                                        <td class="fw-bold text-primary-2" id="newOrderTotal">${{ number_format($currentTotal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <hr>
                        <div class="text-end mt-4">
                            <a href="{{ route('order.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-1"></i> @lang('Cancel Edit')
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> @lang('Save Changes to Order')
                            </button>
                        </div>
                    @else
                        <div class="alert alert-light text-center">
                            @lang('This order currently has no items. You might want to cancel it or add items (if functionality exists).')
                        </div>
                         <div class="text-end mt-3">
                            <a href="{{ route('order.index') }}" class="btn btn-secondary">@lang('Back to Orders')</a>
                        </div>
                    @endif
                </form>
            @else
                 <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>@lang('This order has been shipped and cannot be edited.')
                    <a href="{{ route('order.index') }}" class="btn btn-link ms-2">@lang('Back to Orders')</a>
                 </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const newOrderTotalCell = document.getElementById('newOrderTotal');

    function formatCurrency(amount) {
        // Basic formatting, consider using Intl.NumberFormat for robust localization
        return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function calculateNewTotal() {
        let newTotal = 0;
        quantityInputs.forEach(input => {
            const quantity = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            const subtotal = quantity * price;
            newTotal += subtotal;

            // Update individual subtotal cell for the row
            const row = input.closest('tr');
            if (row) {
                const subtotalCell = row.querySelector('.subtotal-cell');
                if (subtotalCell) {
                    subtotalCell.textContent = formatCurrency(subtotal);
                }
            }
        });

        if (newOrderTotalCell) {
            newOrderTotalCell.textContent = formatCurrency(newTotal);
        }
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', calculateNewTotal); // 'input' event is generally better for number fields
        input.addEventListener('change', calculateNewTotal); // Also on change for spinners etc.
    });

    // Initial calculation on page load if there are items
    if(quantityInputs.length > 0) {
        calculateNewTotal();
    }
});
</script>
@endsection