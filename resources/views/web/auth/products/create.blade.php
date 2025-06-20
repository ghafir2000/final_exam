@extends('web.layout')

@section('title')@lang("Dr.Pets - New Product ")@endsection

@section('styles')
<style>
    .category-slot-input-group { /* Renamed from time-slot-input-group for clarity */
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 0.5rem;
    }
    .category-slot-input-group .category-select-field { /* Renamed */
        flex-grow: 1;
    }
    .category-slot-input-group .btn-danger {
        flex-shrink: 0;
    }
    .is-invalid + .select2-container .select2-selection--single,
    .is-invalid + .select2-container .select2-selection--multiple {
        border-color: #dc3545;
    }
    .invalid-feedback-select2 {
        width: 100%;
        margin-top: .25rem;
        font-size: .875em;
        color: #dc3545;
        display: none; /* Initially hidden */
    }
    .is-invalid ~ .invalid-feedback-select2 {
        display: block; /* Show when select is invalid */
    }
</style>
@endsection


@section('content')

<div class="container mt-4">
    <div class="card mx-auto" style="width: 80%;">
        <div class="card-header text-center" style="background-color: #F7DC6F;">
            <h2>{{ __('Create a Product') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data" id="createProductForm">
                @csrf

                {{-- Product Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Product Name') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- CRF --}}
                <div class="mb-3">
                    <label for="CRF" class="form-label">{{ __('CRF (Optional)') }}</label> {{-- Assuming CRF might be optional or has specific meaning --}}
                    <input type="text" class="form-control @error('CRF') is-invalid @enderror" id="CRF" name="CRF" value="{{ old('CRF') }}">
                    @error('CRF') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Price --}}
                <div class="mb-3">
                    <label for="price" class="form-label">{{ __('Price ($)') }}</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- START: Categories Repeater Section --}}
                <div class="mb-3">
                    <label class="form-label">@lang('Applicable Categories')</label>
                    <div id="categories-repeater-container">
                        {{-- Render old categories if validation failed --}}
                        @if(is_array(old('categories')))
                            @foreach(old('categories') as $index => $oldCategoryId)
                                @if($oldCategoryId) {{-- Ensure the old category ID is not null/empty --}}
                                <div class="category-slot-input-group mb-2">
                                    <select name="categories[]" class="form-control category-select-field @error("categories.$index") is-invalid @enderror" required>
                                        <option value="">@lang('Select Category')</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @if($category->id == $oldCategoryId) selected @endif>
                                                {{ $category->name }} {{-- Assuming your category model has 'name' --}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-danger btn-sm remove-category-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @error("categories.$index")
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif
                            @endforeach
                        @endif
                        {{-- New category rows will be added here by JS --}}
                    </div>
                    <button type="button" id="add-category-btn" class="btn btn-outline-secondary btn-sm mt-2">
                        <i class="fas fa-plus"></i> @lang('Add Category')
                    </button>
                    @error('categories') {{-- General error for the categories array (e.g., "categories field is required") --}}
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                {{-- END: Categories Repeater Section --}}
                <div class="mb-3">
                    <label for="product_picture" class="form-label"> @lang('Product Image (Optional)') </label>
                    <input type="file" class="form-control @error('product_picture') is-invalid @enderror" id="product_picture" name="product_picture" accept="image/*">
                    @error('product_picture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">{{ __('Create Product') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')

{{-- Pass PHP variables to JavaScript --}}
<script>
    // Make categories available to JavaScript
    // Ensure $categories is not empty and is correctly structured
    const allAvailableCategories = @json($categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])->all());
    const langSelectCategory = "@lang('Select Category')";
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoriesRepeaterContainer = document.getElementById('categories-repeater-container');
    const addCategoryBtn = document.getElementById('add-category-btn');

    const createCategoryRowElement = () => {
        const uniqueId = Date.now(); // For unique IDs if needed later, not strictly necessary for this setup

        const rowDiv = document.createElement('div');
        rowDiv.className = 'category-slot-input-group mb-2';

        const categorySelect = document.createElement('select');
        categorySelect.name = 'categories[]';
        categorySelect.className = 'form-control category-select-field';
        categorySelect.required = true;

        // Add a default "Select Category" option
        const defaultOption = new Option(langSelectCategory, '');
        categorySelect.add(defaultOption);

        // Populate with all available categories
        if (allAvailableCategories && allAvailableCategories.length > 0) {
            allAvailableCategories.forEach(category => {
                const option = new Option(category.name, category.id);
                categorySelect.add(option);
            });
        } else {
            // Handle case where no categories are available, though ideally this shouldn't happen
            // Or the default option is enough
            console.warn('No categories available to populate the select dropdown.');
        }

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger btn-sm remove-category-btn'; // Use the same class for delegation
        removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
        // Event listener for remove will be handled by delegation

        rowDiv.appendChild(categorySelect);
        rowDiv.appendChild(removeBtn);
        categoriesRepeaterContainer.appendChild(rowDiv);
    };

    addCategoryBtn.addEventListener('click', createCategoryRowElement);

    // Event delegation for remove buttons
    // This will work for rows rendered by PHP (old values) and rows added by JS
    categoriesRepeaterContainer.addEventListener('click', function(event) {
        // Traverse up to find the button if the icon was clicked
        const removeButton = event.target.closest('.remove-category-btn');
        if (removeButton) {
            // Traverse up to find the parent row div and remove it
            removeButton.closest('.category-slot-input-group').remove();
        }
    });

    // Optional: Add at least one category row if none exist (neither from old() nor manually added)
    // and if the container is empty on load.
    // if (categoriesRepeaterContainer.children.length === 0) {
    //     createCategoryRowElement();
    // }
});
</script>

{{-- Make sure these are loaded after your custom scripts or use defer/async if they are in <head>
     It's better to include them at the end of the body in your main layout file.
--}}
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script> --}}

@endsection