@extends('web.layout')

@section('title', __('Dr.Pets - Booking Details'))

@section('content')
<div class="container mt-4 mb-4">
    <div class="card mx-auto" style="max-width: 700px;"> {{-- Adjusted width for more content --}}
        <div class="card-header text-center" style="background-color: #A2D9CE;"> {{-- A slightly different color for booking --}}
            <h2>{{ __('Booking Details') }}</h2>
        </div>

        <div class="card-body">
            <div class="row">
                {{-- Left Column: Pet Image & Basic Info --}}
                <div class="col-md-4 text-center border-end">
                    @if($booking->pet)
                        <h5>{{ __('Pet:') }} {{ $booking->pet->name }}</h5>
                        <img
                            width="150px"
                            class="img-fluid rounded mb-2"
                            src="{{ $booking->pet->hasMedia('pet_picture') ? $booking->pet->getFirstMediaUrl('pet_picture') : asset('images/uploud_defauls.jpg') }}"
                            alt="{{ $booking->pet->name }}'s picture"
                        >
                    @else
                        <h5>{{ __('Pet: N/A') }}</h5>
                        <img width="150px" class="img-fluid rounded mb-2" src="{{ asset('images/uploud_defauls.jpg') }}" alt="{{ __('Default Pet Image') }}">
                    @endif
                    <hr>
                    <a href="{{ route('booking.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to My Bookings') }}
                    </a>
                </div>

                {{-- Right Column: Booking Specifics --}}
                <div class="col-md-8 ps-md-4">
                    <h4>{{ __('Service:') }} {{ $booking->service->name ?? 'N/A' }}</h4>

                    <dl class="row mt-3">
                        <dt class="col-sm-4">{{ __('Provider:') }}</dt>
                        <dd class="col-sm-8">
                            @if($booking->service && $booking->service->servicable && $booking->service->servicable->userable)
                                {{ $booking->service->servicable->userable->name }}
                                <small class="d-block text-muted">
                                    ({{ $booking->service->servicable_type == 'App\Models\Veterinarian' ? __('Veterinarian') : __('Partner') }})
                                </small>
                            @else
                                {{ __('N/A') }}
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ __('Date & Time:') }}</dt>
                        <dd class="col-sm-8">
                            {{ \Carbon\Carbon::parse($booking->date)->format('D, M d, Y') }}
                            {{ __('at') }} {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}
                        </dd>

                        <dt class="col-sm-4">{{ __('Booking Status:') }}</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $booking->status == \App\Enums\BookingEnums::BOOKED ? 'success' : ($booking->status == \App\Enums\BookingEnums::PENDING ? 'warning' : 'secondary') }} mt-1">
                                {{ $booking->getStatusLabel() }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">{{ __('Payment Status:') }}</dt>
                        <dd class="col-sm-8">
                            @php
                                $latestPayment = $booking->payable()->latest()->first();
                            @endphp

                            @if($latestPayment)
                                <span class="badge bg-{{ $latestPayment->status == \App\Enums\PaymentEnums::SUCCESS ? 'success' : ($latestPayment->status == \App\Enums\PaymentEnums::PENDING ? 'warning' : 'danger') }} mt-1">
                                    {{ $latestPayment->getStatusLabel() }}
                                </span>
                            @elseif($booking->status == \App\Enums\BookingEnums::BOOKED)
                                <span class="badge bg-info">{{ __('Awaiting Payment') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('N/A') }}</span>
                            @endif
                        </dd>

                    </dl>
                    
                    <hr>
                   {{-- Action Buttons --}}
                    <div class="mt-3 text-center">
                        @php
                            $latestPayment = $booking->payable()->latest()->first();
                        @endphp

                        {{-- Veterinarian or Partner Actions --}}
                        @if(auth()->user()->userable_type == \App\Models\Veterinarian::class || auth()->user()->userable_type == \App\Models\Partner::class)

                            @if($booking->status == \App\Enums\BookingEnums::BOOKED && $latestPayment && $latestPayment->status == \App\Enums\PaymentEnums::SUCCESS)
                                @if(\Carbon\Carbon::parse($booking->date)->isToday())
                                    <form action="{{ route('booking.start') }}" method="GET" class="d-inline-block me-2">

                                        <input type="hidden" name="id" value="{{ $booking->id }}">
                                        <input type="hidden" name="status" value="{{ \App\Enums\BookingEnums::STARTED }}">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-play-circle"></i> {{ __('Start Session') }}</button>
                                    </form>
                                @else
                                    <span class="badge bg-light text-dark p-2">{{ __('Session Scheduled for a Future Date') }}</span>
                                @endif
                            @elseif($booking->status == \App\Enums\BookingEnums::STARTED)
                                <span class="badge bg-success p-2"><i class="fas fa-check-circle"></i> {{ __('Session In Progress') }}</span>
                                <form action="{{ route('booking.start') }}" method="GET" class="d-inline-block me-2">

                                        <input type="hidden" name="id" value="{{ $booking->id }}">
                                        <input type="hidden" name="status" value="{{ \App\Enums\BookingEnums::STARTED }}">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-play-circle"></i> {{ __('Back to Session') }}</button>
                                    </form>

                            @elseif($booking->status == \App\Enums\BookingEnums::PENDING || ($booking->status == \App\Enums\BookingEnums::BOOKED && (!$latestPayment || $latestPayment->status != \App\Enums\PaymentEnums::SUCCESS)))
                                <form action="{{ route('booking.destroy', $booking->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to cancel this booking?') }}')"><i class="fas fa-times-circle"></i> {{ __('Cancel Booking') }}</button>
                                </form>
                            @elseif($booking->status == \App\Enums\BookingEnums::CANCELLED)
                                <span class="badge bg-danger p-2">{{ __('Booking Cancelled') }}</span>
                            @elseif($booking->status == \App\Enums\BookingEnums::COMPLETED)
                                <span class="badge bg-info p-2">{{ __('Session Completed') }}</span>
                            @endif

                            @if(in_array($booking->status, [\App\Enums\BookingEnums::PENDING, \App\Enums\BookingEnums::BOOKED]))
                                <form action="{{ route('booking.reschedule', $booking->id) }}" method="GET" class="d-inline-block ms-2">
                                    @csrf

                                    <button type="submit" class="btn btn-info"><i class="fas fa-calendar-alt"></i> {{ __('Reschedule') }}</button>
                                </form>
                            @endif

                        {{-- Customer Actions --}}
                        @elseif(auth()->user()->userable_type == \App\Models\Customer::class)

                            @if($booking->status == \App\Enums\BookingEnums::PENDING && (!$latestPayment || $latestPayment->status != \App\Enums\PaymentEnums::SUCCESS))
                                <form action="{{ route('payment.create') }}" method="GET" class="d-inline-block me-2">
                                    @csrf
                                    <input type="hidden" name="payable_id" value="{{ $booking->id }}">
                                    <input type="hidden" name="payable_type" value="{{ get_class($booking) }}">
                                    <button type="submit" class="btn btn-success"><i class="fas fa-credit-card"></i> {{ __('Pay Now') }}</button>
                                </form>
                            @endif

                            @if(in_array($booking->status, [\App\Enums\BookingEnums::PENDING, \App\Enums\BookingEnums::BOOKED]))
                                <form action="{{ route('booking.update', $booking->id)}}" method="POST" class="d-inline-block me-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{$booking->id}}">
                                    <input type="hidden" name="status" value="{{ \App\Enums\BookingEnums::CANCELLED }}">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('{{ __('Are you sure you want to cancel this booking? This action may be irreversible depending on the provider\'s policy.') }}')"><i class="fas fa-times"></i> {{ __('Cancel Booking') }}</button>
                                </form>

                               <form action="{{ route('booking.reschedule',$booking->id) }}" method="GET" class="d-inline-block ms-2">
                                    @csrf

                                    <button type="submit" class="btn btn-info"><i class="fas fa-calendar-alt"></i> {{ __('Reschedule') }}</button>
                                </form>
                            @elseif($booking->status == \App\Enums\BookingEnums::STARTED)
                                <span class="badge bg-primary p-2">{{ __('Session In Progress') }}</span>

                            @elseif($booking->status == \App\Enums\BookingEnums::CANCELLED)
                                <span class="badge bg-danger p-2">{{ __('Your Booking is Cancelled') }}</span>
                                <form action="{{ route('booking.destroy', $booking->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to cancel this booking?') }}')"><i class="fas fa-times-circle"></i> {{ __('DELETE Booking') }}</button>
                                </form>
                            @elseif($booking->status == \App\Enums\BookingEnums::COMPLETED)
                                <span class="badge bg-success p-2">{{ __('Session Completed') }}</span>
                            @endif

                        @endif
                    </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <dt class="col-sm-4">{{ __('Your Notes:') }}</dt>
            <dd class="col-sm-8">
                @if($booking->record)
                        {{json_encode($booking->record->stats)}}
                @else
                    {{ __('The Medical Records for this booking will be here after the session ends') }}
                @endif
            </dd>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-body dt {
        font-weight: 500;
    }
    .card-body dd {
        margin-bottom: 0.75rem;
    }
</style>
@endpush

