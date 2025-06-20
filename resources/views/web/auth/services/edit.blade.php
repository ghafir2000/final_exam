@extends('web.layout')

<title>@lang('Dr.Pets - Edit') {{ $service->name }}</title>

@section('styles') 
<style>
    .time-slot-input-group {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 0.5rem; /* Adds space between elements */
    }
    .time-slot-input-group .time-input-field {
        flex-grow: 1; /* Takes up available space */
    }
    /* .time-slot-input-group .form-check removed as checkbox is removed */
    .time-slot-input-group .btn-danger {
        flex-shrink: 0; /* Prevent button from shrinking */
    }
</style>
@endsection


@section('content') 

    <div class="container mt-4">
        <div class="card mx-auto" style="width: 80%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2>@lang('Dr.Pets - Edit') {{ $service->name }}</h2>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ $service->hasMedia('service_picture') ? route('image.update') : route('image.add') }}"
                      method="POST" enctype="multipart/form-data" id="servicePictureForm"
                      style="display: flex; flex-direction: column; justify-content: center; align-items: center; margin-bottom: 20px;">
                    @csrf
                    @if ($service->hasMedia('service_picture'))
                        @method('PUT')
                    @endif
                    <input type="file" name="image" id="imageUpload" class="d-none" onchange="this.form.submit()" />
                    <input type="hidden" name="model" value="{{ get_class($service) }}">
                    <input type="hidden" name="model_id" value="{{ $service->id }}">
                    <input type="hidden" name="collection" value="service_picture">
                    <label for="imageUpload" style="cursor: pointer;">
                        <img class="rounded-circle" width="150px" height="150px"
                             src="{{ $service->getFirstMediaUrl('service_picture') ?: asset('images/upload_default.jpg') }}"
                             alt="{{ __('Dr.Pets - Service Picture') }}" style="display: block; margin: 0 auto; object-fit: cover;">
                    </label>
                    @error('image') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
                    @error('model') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
                    @error('model_id') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
                    @error('collection') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
                </form>

                <form action="{{ route('service.update', $service->id) }}" method="POST" id="updateServiceForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Dr.Pets - Service Name') }}</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Dr.Pets - Description') }}</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $service->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">{{ __('Dr.Pets - Price') }} ($)</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service->price) }}" required>
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                             <label for="duration" class="form-label">{{ __('Dr.Pets - Duration') }}</label>
                             {{--
                                 IMPORTANT DURATION CHANGE:
                                 Your UpdateServiceRequest expects duration as a string like "30 minutes".
                                 If $service->duration stores just the number (e.g., 30), we need to format it.
                                 If it already stores "30 minutes", then this is fine.
                                 Assuming $service->duration stores the numeric value of minutes.
                             --}}
                             @php
                                 $durationDisplayValue = old('duration', $service->duration);

                             @endphp
                             <input type="text" name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror" value="{{ $durationDisplayValue }}" required placeholder="{{ __('e.g., 30 minutes or 1 hour') }}">
                             @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- START: Available Times Section --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Available Times (HH:MM format)') }}</label>
                        <div id="available-times-container">
                            @php
                                $currentAvailableTimesArray = [];
                                $oldAvailableTimesJson = old('available_times');

                                if ($oldAvailableTimesJson) {
                                    $decoded = json_decode($oldAvailableTimesJson, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $currentAvailableTimesArray = $decoded;
                                    }
                                } elseif (isset($service->available_times)) {
                                    if (is_string($service->available_times)) {
                                        $decoded = json_decode($service->available_times, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            $currentAvailableTimesArray = $decoded;
                                        }
                                    } elseif (is_array($service->available_times)) {
                                        // If it's already an array (e.g., from an accessor or if you changed storage)
                                        $currentAvailableTimesArray = $service->available_times;
                                    }
                                }
                            @endphp

                            @if(!empty($currentAvailableTimesArray))
                                @foreach($currentAvailableTimesArray as $time)
                                    @if(is_string($time) && preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time))
                                    <div class="time-slot-input-group mb-2">
                                        <input type="time" class="form-control time-input-field @if($errors->has('available_times') || $errors->has('available_times.*')) is-invalid @endif" value="{{ $time }}">
                                        <button type="button" class="btn btn-danger btn-sm remove-time-slot-btn"><i class="fas fa-trash"></i></button>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                {{-- If there are no times, show one empty slot to start with --}}
                                <div class="time-slot-input-group mb-2">
                                    <input type="time" class="form-control time-input-field @if($errors->has('available_times') || $errors->has('available_times.*')) is-invalid @endif">
                                    <button type="button" class="btn btn-danger btn-sm remove-time-slot-btn"><i class="fas fa-trash"></i></button>
                                </div>
                            @endif
                        </div>
                        <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-plus"></i> {{ __('Dr.Pets - Add Time Slot') }}
                        </button>
                        {{-- Hidden input to store the JSON string of available times --}}
                        <input type="hidden" name="available_times" id="json_available_times">

                        @if ($errors->has('available_times'))
                            <div class="invalid-feedback d-block">{{ $errors->first('available_times') }}</div>
                        @endif
                        @foreach ($errors->getMessages() as $field => $message)
                            @if(str_starts_with($field, 'available_times.'))
                                <div class="invalid-feedback d-block">{{ $message[0] }}</div>
                            @endif
                        @endforeach
                    </div>
                    {{-- END: Available Times Section --}}

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn btn-primary">{{__("Update Service")}}</button>
                        <div>
                            <button type="button" class="btn btn-danger"
                                    onclick="if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) { document.getElementById('delete-service-form-{{ $service->id }}').submit(); }">
                                {{__("Delete Service")}}
                            </button>
                        </div>
                    </div>
                </form>

                <form id="delete-service-form-{{ $service->id }}" action="{{ route('service.destroy', $service->id) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
@endsection


@section('scripts') 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeSlotsContainer = document.getElementById('available-times-container');
            const addTimeSlotBtn = document.getElementById('add-time-slot-btn');
            const updateServiceForm = document.getElementById('updateServiceForm'); // Get the form

            function createTimeSlotRow(initialTime = '') {
                const div = document.createElement('div');
                div.className = 'time-slot-input-group mb-2';

                const timeInput = document.createElement('input');
                timeInput.type = 'time';
                timeInput.className = 'form-control time-input-field';
                // Apply is-invalid class if there were general time errors on page load
                if (document.querySelector('#json_available_times + .invalid-feedback')) {
                    timeInput.classList.add('is-invalid');
                }
                timeInput.value = initialTime;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-danger btn-sm remove-time-slot-btn';
                removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                // removeBtn.addEventListener('click', function() { div.remove(); }); // Use event delegation

                div.appendChild(timeInput);
                div.appendChild(removeBtn);
                return div;
            }

            if (addTimeSlotBtn) {
                addTimeSlotBtn.addEventListener('click', function() {
                    timeSlotsContainer.appendChild(createTimeSlotRow());
                });
            }

            // Event delegation for remove buttons
            timeSlotsContainer.addEventListener('click', function(event) {
                if (event.target.closest('.remove-time-slot-btn')) {
                    event.target.closest('.time-slot-input-group').remove();
                }
            });

            // Form Submission Logic for Available Times
            if (updateServiceForm) {
                updateServiceForm.addEventListener('submit', function(event) {
                    const timeInputs = timeSlotsContainer.querySelectorAll('.time-input-field');
                    const timesArray = [];
                    timeInputs.forEach(input => {
                        if (input.value) { // Only add non-empty time values
                            timesArray.push(input.value);
                        }
                    });

                    const jsonAvailableTimesInput = document.getElementById('json_available_times');
                    if (jsonAvailableTimesInput) {
                        jsonAvailableTimesInput.value = JSON.stringify(timesArray);
                    } else {
                        console.error('Hidden input for available_times JSON not found!');
                        event.preventDefault(); // Prevent submission
                    }
                });
            }
        });
    </script>
@endsection