@extends('web.layout') 

<title>{{ __('Dr.Pets - :name', ['name' => $service->name]) }}</title>

@section('content')

<body>
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2>{{ __('Service Details for :name', ['name' => $service->name]) }}</h2>
            </div>
            <div class="card-body">
                <div class="d-flex">
                    <div class="me-3">
                        <img
                            width="150px"
                            src="{{ $service->getFirstMediaUrl('service_picture') ?: asset('images/upload_default.jpg') }}"
                            alt="{{ __(':name\'s profile picture', ['name' => $service->name]) }}"
                        >
                    </div>
                    <div class="col-8">
                        <h4>{{ $service->name }}</h4>
                        <p>{{ $service->description }}</p>
                        <ul class="list-unstyled">
                            <li><strong>{{ __('Servicable Type:') }}</strong> {{ __(class_basename($service->servicable_type)) }}</li>
                            <li><strong>{{ __('Provided by:') }}</strong> {{ $service->servicable->userable->name ?? __('N/A') }}</li>
                            <li><strong>{{ __('Price:') }}</strong> ${{ number_format($service->price, 2) }}</li>
                        </ul>
                        <div class="btn-group d-flex align-items-center mt-3" role="group">
                            <form action="{{ route('user.show',$service->servicable->userable->id) }}" method="GET">
                                @csrf
                                <button type="submit" class="btn btn-success me-2 ml-2">{{ __('Show Profile') }}</button>
                            </form>
                            @if(auth()->check() && auth()->user()->userable && $service->servicable_id === auth()->user()->userable_id && $service->servicable_type === get_class(auth()->user()->userable))
                            <a href="{{ route('service.edit', ['id' => $service->id]) }}" class="btn btn-warning mb-3 mr-2 ml-2">{{ __('Edit') }}</a>
                            <form action="{{ route('service.destroy', ['id' => $service->id]) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger " onclick="return confirm('{{ __('Are you sure you want to delete this service?') }}')">{{ __('Delete') }}</button>
                            </form>
                            @endif
                            @if(auth()->check() && auth()->user()->userable && $service->servicable_id !== auth()->user()->userable_id && $service->servicable_type === get_class(auth()->user()->userable))
                            <form action="{{ route('chat.store', ['chatable_id' => $service->servicable->userable->id, 'chatable_type' => get_class($service->servicable->userable)]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-info me-2 ml-2">{{ __('Chat') }}</button>
                            </form>
                        </div>
                        <div>
                            @if(auth()->check() && !auth()->user()->hasRole('provider'))
                                @php
                                $isWished = $service->wishable()->where('user_id', auth()->user()->id)->exists();
                                @endphp
                                <form action="{{ $isWished ? route('wish.update') : route('wish.store') }}" method="POST" style="display: inline;">
                                    @csrf
                                    @if($isWished) @method('PUT') @endif
                                    <input type="hidden" name="wishable_id" value="{{ $service->id }}">
                                    <input type="hidden" name="wishable_type" value="{{ get_class($service) }}">
                                    <button type="submit" class="btn btn-outline-danger ml-2">
                                        <i class="fas fa-heart{{ $isWished ? '-broken' : '' }}"></i>
                                        {{ $isWished ? __('Remove from Wishlist') : __('Add to Wishlist') }}
                                    </button>
                                </form>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h5>{{ __('Bookings for this Service:') }}</h5>
                    <ul class="list-group" id="bookingList">
                        @forelse ($service->bookings as $booking)
                            @if(($booking->pet->customer_id == auth()->user()->userable_id && auth()->user()->userable_type == 'App/Models/Customer') ||
                             ($booking->service->servicable_id == auth()->user()->userable_id && auth()->user()->userable_type == $booking->service->servicable_type))
                            <li class="list-group-item">
                                    <a href="{{ route('booking.show', ['id' => $booking->id]) }}">
                                        {{ $booking->service->name }}  
                                    </a> - {{ $booking->date }} by at {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }} by 
                                    <a href="{{ route('user.show', ['id' => $booking->service->servicable->userable->id]) }}">
                                    {{ $booking->service->servicable->userable->name }} 
                                    </a>
                                </li>
                            @endif
                        @empty
                            <li class="list-group-item">{{ __('No bookings') }}</li>
                        @endforelse
                    </ul>
                    <div id="noBookingsMessage" class="text-muted" style="display: none;">{{ __('No bookings available.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookingList = document.getElementById('bookingList');
            const noBookingsMessage = document.getElementById('noBookingsMessage');
            if (bookingList.children.length === 0) {
                noBookingsMessage.style.display = 'block';
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
        integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>

