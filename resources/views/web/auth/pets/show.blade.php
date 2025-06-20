@extends('web.layout') 

<title>@lang('Dr.Pets -') {{$pet->name}} </title>


@section('content') {{-- Define the content section --}}

<body>
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2> @lang('Pet Profile for') {{$pet->name}}</h2>
            </div>

            <div class="card-body">
                <div class="d-flex">
                    <div class="me-3">
                        <label for="pet_picture" class="form-label">@lang('Pet Profile Picture')</label>
                        <img
                            width="150px"
                            src="{{ $pet->getFirstMediaUrl('pet_picture') ?: asset('images/upload_default.jpg') }}"
                            alt="{{ $pet->name }}'s profile picture"
                        >
                    </div>
                    <div class="col-8">
                        <h4>{{ $pet->name }}</h4>
                        <p>{{ $pet->description }}</p>
                        <ul class="list-unstyled">
                            <li><strong>@lang('Gender'):</strong> {{ $pet->gender ? __('Male') : __('Female') }}</li>
                            <li><strong>@lang('Fertility'):</strong> {{ $pet->fertility ? __('Yes') : __('No') }}</li>
                            <li><strong>@lang('Age'):</strong> {{ $pet->age }}</li>
                            <li><strong>@lang('Breed'):</strong> {{ $pet->breed->name }}</li>
                            <li><strong>@lang('Animal'):</strong> {{ $pet->breed->animal->name }}</li>
                        </ul>
                        <div class="d-flex align-items-center">
                            @if (auth()->user() && auth()->user()->userable_id === $pet->customer_id && auth()->user()->userable_type === "App\Models\Customer")
                                <a href="{{ route('pet.edit', ['id' => $pet->id]) }}" class="btn btn-warning">@lang('Edit pet')</a>
                                <form action="{{ route('pet.destroy', ['id' => $pet->id]) }}" method="POST" class="ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <div style="margin-left: 10px; margin-top: 15px;">
                                        <button type="submit" class="btn btn-danger ">@lang('Delete pet')</button>
                                    </div>
                                </form>
                            @endif
                            <div style="margin-left: 10px">
                                    <a href="{{ route('record.index', ['id' => $pet->id]) }}" class="btn btn-success ">@lang('Records')</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-1">
                    <h5>@lang('Bookings'):</h5>
                    <ul class="list-group">
                        @forelse ($pet->bookings as $booking)
                            <li class="list-group-item">
                                <a href="{{ route('booking.show', ['id' => $booking->id]) }}">
                                    {{ $booking->service->name }}  
                                </a> - {{ $booking->date }} by at {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }} by 
                                <a href="{{ route('user.show', ['id' => $booking->service->servicable->userable->id]) }}">
                                {{ $booking->service->servicable->userable->name }} 
                                </a>
                            </li>
                        @empty
                            <li class="list-group-item">@lang('No bookings')</li>
                        @endforelse
                    </ul>
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

