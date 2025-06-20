@extends('web.layout')

@section('title', __('Dr.Pets - Your Bookings')) {{-- More common to set title this way --}}

@section('content')

<div class="container mt-4">
    <div class="card mx-auto" style="width: 90%;"> {{-- Consider using Bootstrap width classes like w-100 or col classes if inside a row --}}
        <div class="card-header text-center bg-info text-white">
            <h2>{{ __('My Bookings') }}</h2>
        </div>
        <div class="card-body">
            {{-- Filtering Form --}}
            <form method="GET" action="{{ route('booking.index') }}" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-2">
                        <select name="pet_id" class="form-control form-control-sm">
                            <option value="">{{ __('All Pets') }}</option>
                            @foreach ($filterablePets ?? $bookings->pluck('pet')->filter()->unique('id') as $pet)
                            <option value="{{ $pet->id }}" {{ request('pet_id') == $pet->id ? 'selected' : '' }}>{{ $pet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="service_id" class="form-control form-control-sm">
                            <option value="">{{ __('All Services') }}</option>
                            @foreach ($filterableServices ?? $bookings->pluck('service')->filter()->unique('id') as $service)
                            <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="booking_status" class="form-control form-control-sm">
                            <option value="">{{ __('Booking Status') }}</option>
                            <option value="0" {{ request('booking_status') === '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="1" {{ request('booking_status') == '1' ? 'selected' : '' }}>{{ __('Booked') }}</option>
                            <option value="2" {{ request('booking_status') == '2' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="payment_status" class="form-control form-control-sm">
                            <option value="">{{ __('Payment Status') }}</option>
                            <option value="0" {{ request('payment_status') === '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                            <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            <option value="3" {{ request('payment_status') == '3' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Filter') }}</button>
                    </div>
                </div>
            </form>

            @if($bookings->count() > 0)
                <div class="table-responsive">
                    <table style="table-layout: auto; width: 100%;" class="table table-bordered table-striped text-center align-middle"> {{-- Changed width to 100% for responsiveness within table-responsive --}}
                        <thead class="table-dark">
                            <tr>
                                <th>{{ __('Pet Name') }}</th>
                                <th>{{ __('Service') }}</th>
                                <th>{{ __('Provider') }}</th>
                                <th>{{ __('Date & Time') }}</th>
                                <th>{{ __('Booking Status') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->pet->name ?? __('N/A') }}</td>
                                <td>{{ $booking->service->name ?? __('N/A') }}</td>
                                <td>
                                    @if($booking->service && $booking->service->servicable && $booking->service->servicable->userable)
                                        {{ $booking->service->servicable->userable->name }}
                                        <small class="d-block">({{ $booking->service->servicable_type == 'App\Models\Veterinarian' ? __('Veterinarian') : __('Partner') }})</small>
                                    @else
                                        {{ __('N/A') }}
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}
                                    <br>
                                    <small>{{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $booking->status == \App\Enums\BookingEnums::BOOKED ? 'success' : ($booking->status == \App\Enums\BookingEnums::PENDING ? 'warning' : 'secondary') }} mt-1"> {{-- Reduced mt-3 to mt-1 or remove if not needed --}}
                                        {{ $booking->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $latestPayment = $booking->payable()->latest()->first();
                                    @endphp

                                    @if($latestPayment)
                                        <span class="badge bg-{{ $latestPayment->status == \App\Enums\PaymentEnums::SUCCESS ? 'success' : ($latestPayment->status == \App\Enums\PaymentEnums::PENDING ? 'warning' : 'danger') }} mt-1"> {{-- Reduced mt-3 to mt-1 --}}
                                            {{ $latestPayment->getStatusLabel() }}
                                        </span>
                                    @elseif($booking->status == \App\Enums\BookingEnums::BOOKED)
                                        <span class="badge bg-info mt-1">{{ __('Awaiting Payment') }}</span> {{-- Reduced mt-3 to mt-1 --}}
                                    @else
                                        {{ __('N/A') }}
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-sm btn-info btn-custom">{{ __('View') }}</a> {{-- Removed ml-2 as gap-2 handles spacing --}}
                                        {{-- Logic for Book Session Button --}}
                                        {{-- This condition means: Booking is NOT 'Booked' AND there IS a latestPayment AND that payment was SUCCESSFUL --}}
                                        {{-- This seems like it might be for a re-booking or confirming a previously failed/pending one. --}}
                                        {{-- If the intent is to allow booking *if* payment is successful OR if it's just pending and needs confirmation, the logic might differ. --}}
                                        {{-- Consider if booking status should already be PENDING for this button to show. --}}
                                        @if( ($booking->status != \App\Enums\BookingEnums::BOOKED && $latestPayment && $latestPayment->status == \App\Enums\PaymentEnums::SUCCESS) ||
                                             ($booking->status == \App\Enums\BookingEnums::PENDING && !$latestPayment) )
                                            <form action="{{ route('booking.update', $booking->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="id" value="{{ $booking->id }}">
                                                <input type="hidden" name="status" value="{{ \App\Enums\BookingEnums::BOOKED }}"> {{-- This action will mark it as BOOKED --}}
                                                <button type="submit" class="btn btn-sm btn-success btn-custom">{{ __('Book Session') }}</button>
                                            </form>
                                        @elseif($booking->status == \App\Enums\BookingEnums::PENDING || ($booking->status == \App\Enums\BookingEnums::BOOKED && (!$latestPayment || $latestPayment->status == \App\Enums\PaymentEnums::PENDING)))
                                            {{-- Show Cancel button if booking is PENDING, OR if it's BOOKED but payment is still PENDING (or missing) --}}
                                            <form action="{{ route('booking.destroy', $booking->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('{{ __('Are you sure you want to cancel this booking?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger btn-custom">{{ __('Cancel') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End table-responsive --}}

                {{-- Pagination Links --}}
                @if (method_exists($bookings, 'links'))
                <div class="d-flex justify-content-center mt-3">
                    {{ $bookings->appends(request()->query())->links() }}
                </div>
                @endif
            @else
                <div class="alert alert-info text-center">
                    {{ __('No bookings available.') }}
                </div>
            @endif {{-- This closes @if($bookings->count() > 0) --}}

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}
</div> {{-- End container --}}

@endsection {{-- This closes @section('content') --}}