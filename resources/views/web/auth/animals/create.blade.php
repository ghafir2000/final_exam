@extends('web.layout') 

<title>@lang('Dr.Pets - create an animale')</title>


@section('content') {{-- Define the content section --}}
<body>
    <div class="container">
        <div class="card mx-auto" style="width: 80%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2>@lang('Create Animal Profile')</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('animal.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Animal Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">@lang('Animal Name')</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label">@lang('Description')</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Number of Breeds --}}
                    <div class="mb-3">
                        <label for="number_of_breeds" class="form-label">@lang('Number of Breeds')</label>
                        <input type="number" class="form-control" id="number_of_breeds" name="number_of_breeds" value="{{ old('number_of_breeds', 1) }}" min="1">                    </div>

                    {{-- Breed Fields (Will be dynamically updated) --}}
                    <div id="breed-fields">
                        <!-- JavaScript will populate this div with breed input fields -->
                    </div>

                    <button type="submit" class="btn btn-success mt-3">@lang('Create Animal')</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const numberOfBreedsInput = document.getElementById("number_of_breeds");
            const breedFieldsContainer = document.getElementById("breed-fields");

            function updateBreedFields() {
                const count = parseInt(numberOfBreedsInput.value) || 1;
                breedFieldsContainer.innerHTML = ""; // Clear existing fields

                for (let i = 0; i < count; i++) {
                    const breedFieldHtml = `
                        <div class="mb-3">
                            <label for="breeds.${i}.name" class="form-label">@lang('Breed ${i + 1} Name')</label>
                            <input type="text" class="form-control" id="breeds.${i}.name" name="breeds[${i}][name]" required>
                        </div>

                        <div class="mb-3">
                            <label for="breeds.${i}.description" class="form-label">@lang('Breed ${i + 1} Description')</label>
                            <textarea class="form-control" id="breeds.${i}.description" name="breeds[${i}][description]" rows="3"></textarea>
                        </div>
                    `;
                    breedFieldsContainer.insertAdjacentHTML("beforeend", breedFieldHtml);
                }
            }

            // Initialize breed fields based on old input (for validation errors)
            updateBreedFields();

            // Listen for changes in number_of_breeds field
            numberOfBreedsInput.addEventListener("input", updateBreedFields);
        });
    </script>

</body>
</html>
