@extends('web.layout')

<title>{{ __('Dr.Pets - Services Title') }}</title> {{-- Key: "Dr.Pets - Services Title" --}}

@section('content')

@if (session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
@endif

<div class="container mt-4">
    <div class="card mx-auto" style="width: 90%;">
        <div class="card-header text-center" style="background-color: #F7DC6F;">
            <div class="card shadow-sm">
    <div class="card-header" style="background-color: #f8e377; border-bottom: 1px solid #efe073;">
        <h4 class="mb-0 text-dark text-center fw-bold">{{ __('Service Listings') }}</h4> {{-- Key: "Service Listings" --}}
    </div>
    <div class="card-body p-4">
        <form method="GET" action="{{ route('service.index') }}">

            {{-- Row 1: Search, Animal, Breed, By --}}
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-lg-4 col-md-5">
                    <label for="search" class="form-label">{{ __('Search Services') }}</label> {{-- Key: "Search Services" --}}
                    <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="{{ __('e.g., Checkup, Grooming') }}" value="{{ request('search') }}"> {{-- Key: "e.g., Checkup, Grooming" --}}
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="animal_id" class="form-label">{{ __('Animal') }}</label> {{-- Key: "Animal" --}}
                    <select name="animal_id" id="animal_id" class="form-control">
                        <option value="">{{ __('All Animals') }}</option> {{-- Key: "All Animals" --}}
                        @foreach ($services->pluck('breeds.*.animal')->flatten()->unique('id')->filter() as $animal)
                            <option value="{{ $animal->id }}" {{ request('animal_id') == $animal->id ? 'selected' : '' }}>{{ $animal->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="breed_id" class="form-label">{{ __('Breed') }}</label> {{-- Key: "Breed" --}}
                    <select name="breed_id" id="breed_id" class="form-control">
                        <option value="">{{ __('All Breeds') }}</option> {{-- Key: "All Breeds" --}}
                        @foreach ($services->pluck('breeds')->flatten()->unique('id')->filter() as $breed)
                            <option value="{{ $breed->id }}" {{ request('breed_id') == $breed->id ? 'selected' : '' }}>{{ $breed->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label for="servicable_id" class="form-label">{{ __('By') }}</label> {{-- Key: "By" --}}
                    <select name="servicable_id" id="servicable_id" class="form-control">
                        <option value="">{{ __('All Providers') }}</option> {{-- Key: "All Providers" --}}
                        @foreach ($services->pluck('servicable')->flatten()->unique('id')->filter() as $servicable)
                            <option value="{{ $servicable->id }}" {{ request('servicable_id') == $servicable->id ? 'selected' : '' }}>{{ $servicable->userable->name ?? __('Unknown') }}</option> {{-- Key: "Unknown" --}}
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 2: Provider Type, Sort by Price, Price Range --}}
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label for="servicable_type" class="form-label">{{ __('Provider Type') }}</label> {{-- Key: "Provider Type" --}}
                    <select name="servicable_type" id="servicable_type" class="form-control">
                        <option value="">{{ __('Any') }}</option> {{-- Key: "Any" --}}
                        @foreach ($services->pluck('servicable_type')->unique()->filter() as $type)
                            {{-- For JSON, translating class_basename still means having keys like "Veterinarian", "Partner" in your JSON file --}}
                            <option value="{{ $type }}" {{ request('servicable_type') == $type ? 'selected' : '' }}>{{ __(class_basename($type)) }}</option> {{-- Key will be "Veterinarian", "Partner", etc. --}}
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12">
                    <label for="price" class="form-label">{{ __('Sort by Price') }}</label> {{-- Key: "Sort by Price" --}}
                    <select name="price" id="price" class="form-control">
                        <option value="">{{ __('Default') }}</option> {{-- Key: "Default" --}}
                        <option value="asc" {{ request('price') == 'asc' ? 'selected' : '' }}>{{ __('Ascending') }}</option> {{-- Key: "Ascending" --}}
                        <option value="desc" {{ request('price') == 'desc' ? 'selected' : '' }}>{{ __('Descending') }}</option> {{-- Key: "Descending" --}}
                    </select>
                </div>
                <div class="col-lg-5 col-md-8 price-slider-group">
                    <label for="price_range_min" class="form-label">{{ __('Price Range') }}</label> {{-- Key: "Price Range" --}}
                    <div class="price-slider d-flex align-items-center">
                        @php
                            $minPrice = intval(floor($services->min('price')));
                            $maxPrice = intval($services->max('price'));
                            $currentMin = intval(request('price_range.min', $minPrice));
                            $currentMax = intval(request('price_range.max', $maxPrice));
                        @endphp
                        <output for="price_range_min" id="minPriceOutput" class="me-2">{{ intval($currentMin) }}</output>
                        <input type="range" name="price_range[min]" id="price_range_min"
                               min="{{ $minPrice }}"
                               max="{{ $maxPrice }}"
                               value="{{ intval($currentMin) }}"
                               class="form-range flex-grow-1" oninput="document.getElementById('minPriceOutput').value = this.value">

                        <span class="mx-2">-</span>

                        <input type="range" name="price_range[max]" id="price_range_max"
                               min="{{ $minPrice }}"
                               max="{{ $maxPrice }}"
                               value="{{ intval($currentMax) }}"
                               class="form-range flex-grow-1" oninput="document.getElementById('maxPriceOutput').value = this.value">
                        <output for="price_range_max" id="maxPriceOutput" class="ms-2">{{ intval($currentMax) }}</output>
                    </div>
                </div>
            </div>

            {{-- Row 3: Filter Actions --}}
            <div class="row g-3 mt-3">
                <div class="col-lg-4 col-md-12 ms-auto d-flex justify-content-end filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm me-2 mr-3"><i class="fas fa-filter"></i> {{ __('Filter') }}</button> {{-- Key: "Filter" --}}
                    <a href="{{ route('service.index') }}" class="btn btn-outline-secondary btn-sm" title="{{ __('Clear Filters Title') }}"> {{-- Key: "Clear Filters Title" --}}
                        <i class="fas fa-times"></i> {{ __('Clear') }} {{-- Key: "Clear" --}}
                    </a>
                </div>
            </div>
        </form>
        <div class="card-body">
            @if($services->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>{{ __('Image') }}</th> {{-- Key: "Image" --}}
                                <th>{{ __('Name') }}</th> {{-- Key: "Name" --}}
                                <th>{{ __('By (Type)') }}</th> {{-- Key: "By (Type)" --}}
                                <th>{{ __('Price') }}</th> {{-- Key: "Price" --}}
                                <th>{{ __('Actions') }}</th> {{-- Key: "Actions" --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($services as $service)
                            <tr>
                                <td>
                                    <img class="rounded-circle" width="80px" height="80px" style="object-fit: cover;"
                                        src="{{ $service->getFirstMediaUrl('service_picture') ?: asset('images/upload_default.jpg') }}"
                                        alt="{{ $service->name }} {{ __('service picture alt') }}"> {{-- Key: "service picture alt" --}}
                                </td>
                                <td>{{ $service->name }}</td>
                                <td>
                                    {{ $service->servicable->userable->name ?? __('N/A') }} {{-- Key: "N/A" --}}
                                    @if($service->servicable_type)
                                        <small class="d-block text-muted">({{ __(class_basename($service->servicable_type)) }})</small> {{-- Key will be "Veterinarian", "Partner", etc. --}}
                                    @endif
                                </td>
                                <td>${{ number_format($service->price, 2) }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        <a href="{{ route('service.show', $service->id) }}" class="btn btn-info btn-sm btn-custom" title="{{ __('View Details Title') }}"><i class="fas fa-eye"></i></a> {{-- Key: "View Details Title" --}}
                                        @auth
                                            @if($select)
                                                <form action="{{ route('booking.create') }}" method="GET" class="d-inline">
                                                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                                                    @if(request()->has('pet_id'))
                                                        <input type="hidden" name="pet_id" value="{{ request('pet_id') }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-primary btn-sm btn-custom">{{ __('Select') }}</button> {{-- Key: "Select" --}}
                                                </form>
                                            @else
                                                @if(auth()->check() && auth()->user()->userable && $service->servicable_id === auth()->user()->userable_id)
                                                    <a href="{{ route('service.edit', $service->id) }}" class="btn btn-warning btn-sm btn-custom" title="{{ __('Edit Service Title') }}"><i class="fas fa-edit"></i></a> {{-- Key: "Edit Service Title" --}}
                                                    <form action="{{ route('service.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this service :name?', ['name' => $service->name]) }}')"> {{-- Key: "Are you sure you want to delete this service :name?" --}}
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm btn-custom" title="{{ __('Delete Service Title') }}"><i class="fas fa-trash"></i></button> {{-- Key: "Delete Service Title" --}}
                                                    </form>
                                                @endif
                                            @endif
                                        @endauth
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($services instanceof \Illuminate\Pagination\LengthAwarePaginator && $services->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $services->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    @if(collect(request()->except('page'))->filter()->isNotEmpty()) 
                        <p>{{ __('No services match your current filter criteria.') }}</p> {{-- Key: "No services match your current filter criteria." --}}
                        <a href="{{ route('service.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo"></i> {{ __('Clear Filters Button') }}</a> {{-- Key: "Clear Filters Button" --}}
                    @elseif(!$select)
                        <p>{{ __('no services yet.') }}
                            <a href="{{ route('service.create') }}" class="btn btn-success"><i class="fas fa-plus-circle"></i> {{ __('Add New Service') }}</a> {{-- Key: "Add New Service" --}}
                        </p>
                    @else
                         <p>{{ __('No services available for selection.') }}</p> {{-- Key: "No services available for selection." --}}
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@if(isset($animalsForFilter) && isset($breedsForFilter)) 
<script>
document.addEventListener('DOMContentLoaded', function() {
    const animalSelect = document.getElementById('animal_id');
    const breedSelect = document.getElementById('breed_id');
    if (!animalSelect || !breedSelect) return;

    const breedsData = @json($breedsForFilter->mapWithKeys(fn($breed) => [$breed->id => ['name' => $breed->name, 'animal_id' => $breed->animal_id]])->all());
    const initialBreedValue = breedSelect.value;
    const allBreedsText = "{{ __('All Breeds') }}"; // Key: "All Breeds" (for JS)

    function populateBreeds() {
        const selectedAnimalId = animalSelect.value;
        breedSelect.innerHTML = `<option value="">${allBreedsText}</option>`;

        for (const breedId in breedsData) {
            if (breedsData.hasOwnProperty(breedId)) {
                const breed = breedsData[breedId];
                if (!selectedAnimalId || breed.animal_id == selectedAnimalId) {
                    const option = new Option(breed.name, breedId);
                    breedSelect.add(option);
                }
            }
        }
        if (breedSelect.querySelector(`option[value="${initialBreedValue}"]`)) {
            breedSelect.value = initialBreedValue;
        }
    }
    animalSelect.addEventListener('change', populateBreeds);
    populateBreeds();
});
</script>
@endif

@endsection
