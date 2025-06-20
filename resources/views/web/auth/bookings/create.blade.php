@extends('web.layout')

@section('title')
    <title>@lang('Dr.Pets - create a booking')</title>
@endsection

@section('styles') 
<style>
    .time-container {
        display: flex;
        justify-content: flex-start;
        flex-wrap: nowrap; /* Keep nowrap for horizontal scrolling */
        gap: 10px;
        padding: 10px;
        overflow-x: auto; /* Allows horizontal scrolling */
        scroll-padding-left: 10px; /* Optional: For snapping scroll */
        scroll-padding-right: 10px; /* Optional: For snapping scroll */
        min-height: 70px; /* Give it some min-height to show messages */
        border: 1px solid #eee; /* Optional: visual aid */
        align-items: center; /* Center placeholder text vertically */
    }

    .time-slot {
        min-width: 100px; /* Minimum width for each slot */
        padding: 10px;
        text-align: center;
        border: 2px solid #ccc;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        box-sizing: border-box;
        flex-shrink: 0; /* Prevent items from shrinking */
        display: flex; /* For centering text vertically */
        align-items: center; /* For centering text vertically */
        justify-content: center; /* For centering text horizontally */
        height: 50px; /* Fixed height for consistency */
        font-size: 0.9em;
        border-radius: 5px; /* Slightly rounded corners for button feel */
    }

    .available {
        background-color: #28a745; /* Bootstrap success green */
        color: white;
        border-color: #1e7e34;
    }
    .available:hover {
        background-color: #218838; /* Darker green on hover */
    }

    .unavailable {
        background-color: #dc3545; /* Bootstrap danger red */
        color: white;
        cursor: not-allowed;
        border-color: #b02a37;
        opacity: 0.7; /* Make unavailable slots slightly less prominent */
    }

    .selected {
        border: 3px solid #007bff; /* Bootstrap primary blue */
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        font-weight: bold;
        transform: scale(1.05); /* Slightly enlarge selected */
    }

    /* Styling for messages within the time container */
    .time-container-message {
        width: 100%;
        text-align: center;
        margin: 0;
        padding: 10px; /* Ensure message is visible */
        color: #6c757d; /* Bootstrap muted color */
    }
    .time-container-message.text-danger {
        color: #dc3545 !important;
    }
    .time-container-message.text-info {
        color: #17a2b8 !important;
    }
</style>
@endsection


@section('content')
<div class="container mt-4">
    <div class="card mx-auto" style="max-width: 600px;"> {{-- Using max-width for better responsiveness --}}
        <div class="card-header text-center bg-warning">
            <h2>@lang('Book an Appointment')</h2>
        </div>
        <div class="card-body">
            @if(isset($service) && $service)
                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
                    @csrf
                    <div class="mb-3">
                        <label for="dateInput" class="form-label">@lang('Appointment Date')</label>
                        <input type="date" class="form-control" name="date" id="dateInput" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">@lang('Available Times')</label>
                        <div class="time-container" id="timeContainer">
                            {{-- Initial message will be set by JS --}}
                        </div>
                    </div>

                    <input type="hidden" name="time" id="selectedTime" required>
                    <input type="hidden" name="service_id" value="{{ $service->id }}">

                    <button type="submit" class="btn btn-success mt-3 w-100">@lang('Book Appointment')</button>
                </form>
            @else
                 <p class="text-danger text-center">@lang('Service not found or not provided.')</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function selectTime(element) {
        if (element.classList.contains('unavailable')) return;

        const timeContainer = document.getElementById('timeContainer');
        const existingErrorMsg = timeContainer.querySelector('.form-error-message');
        if (existingErrorMsg) {
            existingErrorMsg.remove();
        }

        document.querySelectorAll('.time-slot.selected').forEach(slot => {
            slot.classList.remove('selected');
        });
        element.classList.add('selected');
        document.getElementById('selectedTime').value = element.dataset.time;
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($service) && $service)
            const serviceId = '{{ $service->id }}';
            const dateInput = document.getElementById('dateInput');
            const timeContainer = document.getElementById('timeContainer');
            const selectedTimeInput = document.getElementById('selectedTime');
            const bookingForm = document.getElementById('bookingForm');

            function setTimeContainerMessage(message, type = 'muted') {
                if (timeContainer) {
                    let className = 'time-container-message';
                    if (type === 'danger') className += ' text-danger';
                    if (type === 'info') className += ' text-info';
                    timeContainer.innerHTML = `<p class="${className}">${message}</p>`;
                }
            }

            const today = new Date();
            const todayString = today.toISOString().split('T')[0];
            dateInput.setAttribute('min', todayString);

            // Initial message
            setTimeContainerMessage("@lang('Please select a date to see available times.')");

            dateInput.addEventListener('change', function() {
                const selectedDate = this.value;
                selectedTimeInput.value = ''; // Reset selected time

                // Clear previous visual selections and any submit error messages
                timeContainer.querySelectorAll('.time-slot.selected').forEach(slot => slot.classList.remove('selected'));
                const existingErrorMsg = timeContainer.querySelector('.form-error-message');
                if (existingErrorMsg) existingErrorMsg.remove();


                if (!selectedDate) {
                    setTimeContainerMessage("@lang('Please select a date to see available times.')");
                    return;
                }

                setTimeContainerMessage("@lang('Loading times...')", 'info');

                fetch(`{{ route('internal.services.getTimes') }}?service_id=${serviceId}&date=${selectedDate}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => { // Try to parse error message from JSON
                            let message = `HTTP error ${response.status}`;
                            if (errData && errData.message) message = errData.message;
                            else if (errData && errData.error) message = errData.error;
                            throw new Error(message);
                        }).catch(() => { // Fallback if response.json() fails or no specific error message
                            throw new Error(`HTTP error ${response.status}. Could not retrieve details.`);
                        });
                    }
                    return response.json();
                })
                .then(apiTimesStatus => { // Expects an object like {"10:00":true, "11:00":false}
                    if (!timeContainer) return;
                    timeContainer.innerHTML = ''; // Clear loading message

                    // Check if apiTimesStatus is a valid object and has entries
                    if (typeof apiTimesStatus !== 'object' || apiTimesStatus === null || Object.keys(apiTimesStatus).length === 0) {
                        setTimeContainerMessage("@lang('No time slots are defined or available for this service on the selected date.')", 'info');
                        return;
                    }

                    let hasAnyAvailableSlots = false;
                    const sortedTimes = Object.keys(apiTimesStatus).sort();

                    sortedTimes.forEach(timeString => {
                        const isAvailable = apiTimesStatus[timeString]; // true or false

                        if (isAvailable) {
                            hasAnyAvailableSlots = true;
                        }

                        const timeSlotDiv = document.createElement('div');
                        timeSlotDiv.className = `time-slot ${isAvailable ? 'available' : 'unavailable'}`;
                        timeSlotDiv.dataset.time = timeString;
                        timeSlotDiv.textContent = timeString; // You might want to format this for display later

                        if (isAvailable) {
                            timeSlotDiv.onclick = () => selectTime(timeSlotDiv);
                        }
                        timeContainer.appendChild(timeSlotDiv);
                    });

                    if (sortedTimes.length > 0 && !hasAnyAvailableSlots) {
                        // All slots returned by API are unavailable. The slots are already red.
                        // Add an explicit message.
                        const p = document.createElement('p');
                        p.className = 'time-container-message text-info mt-2'; // Added mt-2 for spacing
                        p.textContent = "@lang('All time slots for this date are currently booked.')";
                        timeContainer.appendChild(p); // Append after all slots
                    }
                    // If sortedTimes.length is 0, the earlier check handles the message.
                })
                .catch(error => {
                    console.error('Error fetching times:', error);
                    setTimeContainerMessage(`@lang('Could not load times:') ${error.message}`, 'danger');
                });
            });

            // Optional: Trigger change on page load if a date is already set (e.g., from browser cache or backend)
            if (dateInput.value) {
                dateInput.dispatchEvent(new Event('change'));
            }

            if (bookingForm) {
                bookingForm.addEventListener('submit', function(event) {
                    if (!selectedTimeInput.value) {
                        event.preventDefault(); // Stop form submission
                        const timeContainer = document.getElementById('timeContainer');
                        let errorMsgElement = timeContainer.querySelector('.form-error-message');
                        if (!errorMsgElement) {
                            errorMsgElement = document.createElement('p');
                            errorMsgElement.className = 'text-danger w-100 text-center mt-2 form-error-message';
                            // Append after slots or existing message
                            if (timeContainer.firstChild && timeContainer.firstChild.classList && timeContainer.firstChild.classList.contains('time-slot')) {
                                timeContainer.appendChild(errorMsgElement);
                            } else { // If only a message is there, or empty
                                timeContainer.insertAdjacentElement('beforeend', errorMsgElement);
                            }
                        }
                        errorMsgElement.textContent = "@lang('Please select an available time slot.')";
                        // Optionally scroll to make the message visible
                        // errorMsgElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        @endif
    });
</script>

@endsection
