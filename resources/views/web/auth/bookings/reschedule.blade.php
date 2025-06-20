@extends('web.layout') 

<title>@lang('Dr.Pets - Update booking')</title>


@section('content') {{-- Define the content section --}}
<head>
    <style>
        .time-container {
            display: flex;
            /* FIX: Align items to the start instead of centering */
            justify-content: flex-start; 
            flex-wrap: nowrap;
            gap: 10px;
            padding: 10px; /* Keep padding for space around */
            overflow-x: auto; /* Allows horizontal scrolling */
            /* Scroll padding is correct for spacing *while scrolling* */
            scroll-padding-left: 10px;
            scroll-padding-right: 10px;
        }

        .time-slot {
            min-width: 80px;
            min-height: 30px;
            padding: 10px;
            text-align: center;
            border: 2px solid #ccc;
            cursor: pointer;
            transition: 0.3s;
            box-sizing: border-box;
            flex-shrink: 0; /* Prevent items from shrinking */
        }

        .available {
            background-color: green;
            color: white;
        }

        .unavailable {
            background-color: red;
            color: white;
            cursor: not-allowed;
        }

        .selected {
            border: 2px solid blue;
        }
    </style>
</head>

{{-- Assuming you have a body tag somewhere, or this is a full Blade view --}}
{{-- If this is just a snippet, ensure it's placed within <body>...</body> --}}

<div class="container mt-4">
    <div class="card mx-auto" style="width: 50%;">
        <div class="card-header text-center bg-warning">
            <h2>@lang('Book an Appointment')</h2>
        </div>
        <div class="card-body">
            {{-- Add a check for $service existence --}}
            @if(isset($service))


                <form action="{{route('booking.update', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="date" class="form-label">@lang('Appointment Date Currently :') {{ \Carbon\Carbon::parse($booking->date)->format('D, M d, Y') }}
                            at {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}</label>
                        {{-- Add a name attribute to the input field --}}
                        <input type="date" class="form-control" name="date" required>
                    </div>

                 
                    <div class="time-container" id="timeContainer">
                        @forelse ($service->available_times as $time => $available)
                            <div class="time-slot {{ $available ? 'available' : 'unavailable' }}" 
                                data-time="{{ $time }}" onclick="selectTime(this)">
                                {{ $time }}
                            </div>
                        @empty
                            {{-- Message when no times are available --}}
                            <p class="text-muted text-center w-100">@lang('No available times found for this service.')</p>
                        @endforelse
                    </div>

                    <input type="hidden" name="time" id="selectedTime">
                    <input type="hidden" name="id" value="{{$booking->id}}">
                    <input type="hidden" name="status" value="{{\App\Enums\BookingEnums::PENDING}}">

                    <button type="submit" class="btn btn-success mt-3">@lang('Update Appointment')</button>
                </form>
            @else
                 <p class="text-danger text-center">@lang('Service not found or not provided.')</p>
            @endif
        </div>
    </div>
</div>

<script>
    function selectTime(element) {
        // Prevent selection of unavailable slots
        if (element.classList.contains('unavailable')) return;

        // Remove previous selection
        document.querySelectorAll('.time-slot').forEach(slot => slot.classList.remove('selected'));

        // Mark new selection
        element.classList.add('selected');

        // Store selected time in the hidden input field
        document.getElementById('selectedTime').value = element.dataset.time;

        console.log(`@lang('Selected time:') ${element.dataset.time}`);
    }
    

</script>

{{-- Include necessary script tags at the end of the body --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
    integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
    crossorigin="anonymous" referrerpolicy="no-referrer">
</script>
{{-- Add your own custom scripts here if needed --}}
