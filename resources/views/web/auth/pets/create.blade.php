@extends('web.layout') 

<title>{{ __('Dr.Pets - New pet') }}</title>


@section('content') {{-- Define the content section --}}
<div class="container mt-4">
    <div class="card mx-auto" style="width: 80%;">
        <div class="card-header text-center bg-warning">
            <h2>{{ __('Create a Pet Profile') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('pet.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Pet Name') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="animal_id" class="form-label">{{ __('Animal') }}</label>
                    <select name="animal_id" class="form-control @error('animal_id') is-invalid @enderror" id="animal_id" onchange="updateBreeds()">
                        <option value="">{{ __('Select Animal') }}</option>
                        @foreach ($animals as $animal)
                            <option value="{{ $animal->id }}">{{ $animal->name }}</option>
                        @endforeach
                    </select>
                    @error('animal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <input type="hidden" name="customer_id" value="{{ auth()->user()->userable->id }}">

                <div class="mb-3">
                    <label for="breed_id" class="form-label">{{ __('Breed') }}</label>
                    <select name="breed_id" class="form-control @error('breed_id') is-invalid @enderror" id="breed_id">
                        <option value="">{{ __('Select Breed') }}</option>
                    </select>
                    @error('breed_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="fertility" class="form-label">{{ __('Fertility') }}</label>
                    <select name="fertility" class="form-control @error('fertility') is-invalid @enderror">
                        <option value="1">{{ __('Yes') }}</option>
                        <option value="0">{{ __('No') }}</option>
                    </select>
                    @error('fertility')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="gender" class="form-label">{{ __('Gender') }}</label>
                    <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                        <option value="1">{{ __('Male') }}</option>
                        <option value="0">{{ __('Female') }}</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="age" class="form-label">{{ __('Age') }}</label>
                    <input type="number" name="age" class="form-control @error('age') is-invalid @enderror">
                    @error('age')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">{{ __('Create Pet Profile') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
    let animals = @json($animals);

    function updateBreeds() {
        let selectedAnimalId = document.getElementById("animal_id").value;
        let breedSelect = document.getElementById("breed_id");

        // Clear existing options
        breedSelect.innerHTML = "<option value=''>{{ __('Select Breed') }}</option>";

        // Find the selected animal and its breeds
        let selectedAnimal = animals.find(animal => animal.id == selectedAnimalId);

        // Populate breed dropdown
        if (selectedAnimal && selectedAnimal.breeds) {
            selectedAnimal.breeds.forEach(breed => {
                let option = document.createElement("option");
                option.value = breed.id;
                option.textContent = breed.name;
                breedSelect.appendChild(option);
            });
        }
    }
</script>

