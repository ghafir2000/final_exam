@extends('web.layout')

<title>@lang('Dr.Pets - New Service')</title>

@section('content') {{-- Define the content section --}}
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
    .time-slot-input-group .btn-danger {
        flex-shrink: 0; /* Prevent button from shrinking */
    }

    /* Styles for Breed Repeater */
    .breed-slot-input-group {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 0.5rem;
    }
    .breed-slot-input-group .breed-select-field {
        flex-grow: 1;
    }
    .breed-slot-input-group .btn-danger {
        flex-shrink: 0;
    }
</style>
{{-- Removed </head> and <body> tags as they are typically in the layout --}}

<div class="container mt-4">
    <div class="card mx-auto" style="width: 80%;">
        <div class="card-header text-center" style="background-color: #F7DC6F;">
            <h2>@lang('Create a Service')</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('service.store') }}" method="POST" enctype="multipart/form-data" id="createServiceForm">
                @csrf

                {{-- Service Name, Animal, Description, Price, Duration fields --}}
                <div class="mb-3">
                    <label for="name" class="form-label">@lang('Service Name')</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="animal_id" class="form-label">@lang('Animal')</label>
                    <select name="animal_id" class="form-control @error('animal_id') is-invalid @enderror" id="animal_id" onchange="updateAllBreedSelects()" required>
                        <option value="">@lang('Select Animal')</option>
                        @foreach ($animals as $animal)
                            <option value="{{ $animal->id }}" {{ old('animal_id') == $animal->id ? 'selected' : '' }}>{{ $animal->name }}</option>
                        @endforeach
                    </select>
                    @error('animal_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- START: Breeds Repeater Section --}}
                <div class="mb-3">
                    <label class="form-label">@lang('Applicable Breeds')</label>
                    <div id="breeds-repeater-container">
                        {{-- Breed rows will be added here by JS --}}
                    </div>
                    <button type="button" id="add-breed-btn" class="btn btn-outline-secondary btn-sm mt-2">
                        <i class="fas fa-plus"></i> @lang('Add Breed')
                    </button>
                    @error('breeds')
                        <div class="invalid-feedback d-block">{{ $message }}</div> {{-- General error for the breeds array --}}
                    @enderror
                     @foreach ((is_array(old('breeds')) ? old('breeds') : []) as $key => $value)
                        @error("breeds.$key")
                            <div class="invalid-feedback d-block">{{ $message }}</div> {{-- Specific error for an item in breeds array --}}
                        @enderror
                    @endforeach
                </div>
                {{-- END: Breeds Repeater Section --}}

                <div class="mb-3">
                    <label for="description" class="form-label">@lang('Description')</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">@lang('Price ($)')</label>
                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') }}" required>
                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="duration" class="form-label">@lang('Duration (minutes)')</label>
                    <input type="number" class="form-control @error('duration') is-invalid @enderror" name="duration" value="{{ old('duration') }}" required placeholder="@lang('e.g., 30')">
                    @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- START: Available Times Section --}}
                <div class="mb-3">
                    <label class="form-label">@lang('Available Times (HH:MM format)')</label>
                    <div id="available-times-container">
                        @php
                            $oldAvailableTimesJson = old('available_times');
                            $oldAvailableTimesArray = [];
                            if ($oldAvailableTimesJson) {
                                $decoded = json_decode($oldAvailableTimesJson, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $oldAvailableTimesArray = $decoded;
                                }
                            }
                        @endphp
                        @if(!empty($oldAvailableTimesArray))
                            @foreach($oldAvailableTimesArray as $time)
                                <div class="time-slot-input-group mb-2">
                                    {{-- Add is-invalid class if 'available_times' or 'available_times.*' has errors --}}
                                    <input type="time" class="form-control time-input-field @if($errors->has('available_times') || $errors->has('available_times.*')) is-invalid @endif" value="{{ $time }}">
                                    <button type="button" class="btn btn-danger btn-sm remove-time-slot-btn"><i class="fas fa-trash"></i></button>
                                </div>
                            @endforeach
                        @else
                            {{-- Default initial row if no old data --}}
                            <div class="time-slot-input-group mb-2">
                                <input type="time" class="form-control time-input-field @if($errors->has('available_times') || $errors->has('available_times.*')) is-invalid @endif">
                                <button type="button" class="btn btn-danger btn-sm remove-time-slot-btn"><i class="fas fa-trash"></i></button>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="fas fa-plus"></i> @lang('Add Time Slot')
                    </button>
                    {{-- Hidden input to store the JSON string of available times --}}
                    <input type="hidden" name="available_times" id="json_available_times">

                    {{-- Display errors for available_times and available_times.* --}}
                    @if ($errors->has('available_times'))
                        <div class="invalid-feedback d-block">{{ $errors->first('available_times') }}</div>
                    @endif
                    @foreach ($errors->getMessages() as $field => $message)
                        @if(str_starts_with($field, 'available_times.'))
                            <div class="invalid-feedback d-block">{{ $message[0] }}</div>
                        @endif
                    @endforeach
                    @error('available_times_required') {{-- Keeping this if it's used elsewhere, though JSON approach changes things --}}
                        <div class="text-danger d-block small">{{ $message }}</div>
                    @enderror
                </div>
                {{-- END: Available Times Section --}}

                <div class="mb-3">
                    <label for="service_picture" class="form-label"> @lang('Service Image URL (Optional)') </label>
                    <input type="file" class="form-control @error('service_picture') is-invalid @enderror" id="service_picture" name="service_picture" value="{{ old('service_picture') }}">
                    @error('service_picture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <input type="hidden" name="servicable_type" value="{{ auth()->user()->userable_type }}">
                <input type="hidden" name="servicable_id" value="{{ auth()->user()->userable_id }}">

                <button type="submit" class="btn btn-success">@lang('Create Service')</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>

<script>
    let animalsWithBreeds = @json($animals);
    // globalTimeSlotCounter is no longer needed for time slots

    // Placed in global scope to be accessible by onchange attribute
    function updateAllBreedSelects() {
        const animalSelect = document.getElementById("animal_id");
        const breedsRepeaterContainer = document.getElementById('breeds-repeater-container');
        const selectedAnimalId = animalSelect.value;
        const breedSelects = breedsRepeaterContainer.querySelectorAll('.breed-select-field');

        const _populate = (selectElement, animalIdForOptions, currentSelectedBreedId) => {
            selectElement.innerHTML = "<option value=''>@lang('Select Breed')</option>"; // Clear existing and add default
            const animalData = animalsWithBreeds.find(animal => animal.id == animalIdForOptions);

            if (animalData && animalData.breeds && animalData.breeds.length > 0) {
                animalData.breeds.forEach(breed => {
                    let option = document.createElement("option");
                    option.value = breed.id;
                    option.textContent = breed.name;
                    selectElement.appendChild(option);
                });
            }
            if (currentSelectedBreedId && Array.from(selectElement.options).some(opt => opt.value == currentSelectedBreedId)) {
                selectElement.value = currentSelectedBreedId;
            } else {
                selectElement.value = '';
            }
        };

        breedSelects.forEach(select => {
            const previouslySelectedBreed = select.value;
            _populate(select, selectedAnimalId, previouslySelectedBreed);
        });
    }


    document.addEventListener('DOMContentLoaded', function() {
        const timeSlotsContainer = document.getElementById('available-times-container');
        const addTimeSlotBtn = document.getElementById('add-time-slot-btn');
        const createServiceForm = document.getElementById('createServiceForm');

        function createTimeSlotRow(initialTime = '') {
            const div = document.createElement('div');
            div.className = 'time-slot-input-group mb-2';

            const timeInput = document.createElement('input');
            timeInput.type = 'time';
            timeInput.className = 'form-control time-input-field';
            // Apply is-invalid class if there were general time errors on page load
            if (document.querySelector('#json_available_times + .invalid-feedback')) { // Crude check
                timeInput.classList.add('is-invalid');
            }
            timeInput.value = initialTime;
            // The 'name' attribute is not set here; values are collected before submit

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm remove-time-slot-btn';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            removeBtn.addEventListener('click', function() { div.remove(); });

            div.appendChild(timeInput);
            div.appendChild(removeBtn);
            return div;
        }

        if (addTimeSlotBtn) {
            addTimeSlotBtn.addEventListener('click', function() {
                timeSlotsContainer.appendChild(createTimeSlotRow());
            });
        }

        // Add remove functionality to dynamically added and existing (from old()) time slots
        timeSlotsContainer.addEventListener('click', function(event) {
            if (event.target.closest('.remove-time-slot-btn')) {
                event.target.closest('.time-slot-input-group').remove();
            }
        });


        // --- Breed Repeater Logic (Unchanged from your original, just included for completeness) ---
        const animalSelect = document.getElementById("animal_id");
        const breedsRepeaterContainer = document.getElementById('breeds-repeater-container');
        const addBreedBtn = document.getElementById('add-breed-btn');

        @php
            $processedOldBreeds = collect(old('breeds'))->filter(function ($value) {
                return $value !== null && $value !== '';
            })->values()->all();
        @endphp
        let oldBreeds = @json($processedOldBreeds);

        const _populateSingleBreedSelect = (selectElement, animalIdForOptions, breedToSelect = null) => {
            selectElement.innerHTML = "<option value=''>@lang('Select Breed')</option>";
            const animalData = animalsWithBreeds.find(animal => animal.id == animalIdForOptions);

            if (animalData && animalData.breeds && animalData.breeds.length > 0) {
                animalData.breeds.forEach(breed => {
                    let option = document.createElement("option");
                    option.value = breed.id;
                    option.textContent = breed.name;
                    selectElement.appendChild(option);
                });
            }
            if (breedToSelect) {
                selectElement.value = breedToSelect;
                if (selectElement.value !== String(breedToSelect)) {
                    selectElement.value = '';
                }
            }
        };

        function createBreedRow(breedIdToSelect = null) {
            const div = document.createElement('div');
            div.className = 'breed-slot-input-group mb-2';
            const breedSelect = document.createElement('select');
            breedSelect.name = 'breeds[]';
            breedSelect.className = 'form-control breed-select-field';
            const currentAnimalId = animalSelect.value;
            _populateSingleBreedSelect(breedSelect, currentAnimalId, breedIdToSelect);
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm remove-breed-slot-btn';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            removeBtn.addEventListener('click', function() { div.remove(); });
            div.appendChild(breedSelect);
            div.appendChild(removeBtn);
            breedsRepeaterContainer.appendChild(div);
        }

        if (oldBreeds && oldBreeds.length > 0) {
            const currentAnimalId = animalSelect.value;
            if (currentAnimalId) {
                 oldBreeds.forEach(breedId => createBreedRow(breedId));
            } else {
                oldBreeds.forEach(breedId => createBreedRow(breedId));
            }
        }
        if (addBreedBtn) {
            addBreedBtn.addEventListener('click', function() { createBreedRow(); });
        }
        // Add remove functionality for breed slots using event delegation
        breedsRepeaterContainer.addEventListener('click', function(event) {
            if (event.target.closest('.remove-breed-slot-btn')) {
                event.target.closest('.breed-slot-input-group').remove();
            }
        });
        // --- END: Breed Repeater Logic ---


        // --- Form Submission Logic for Available Times ---
        if (createServiceForm) {
            createServiceForm.addEventListener('submit', function(event) {
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
                    event.preventDefault(); // Prevent submission if critical element is missing
                }
                // Laravel's 'required' rule on 'available_times' in FormRequest will handle if timesArray is empty
                // (JSON.stringify([]) results in "[]")
            });
        }
    });
</script>
{{-- Removed </body> and </html> as they are typically in the layout --}}
@endsection