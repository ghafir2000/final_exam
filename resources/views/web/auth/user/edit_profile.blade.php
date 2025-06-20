@extends('web.layout') 

<title>@lang('Dr.Pets - Edit Your Profile')</title>

@section('content') {{-- Define the content section --}}
    <style>
        /* Removed .transparent-button styles as they aren't needed here */
        /* Removed the empty .profile-button ruleset as instructed */

        /* Style for the Update Profile button */
        .update-profile-button {
            background-color: lightgreen;
            border: 1px solid lightgreen; /* Added a border for consistency */
            color: black; /* Set text color for better readability on light green */
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease; /* Added transition for color */
        }

        .update-profile-button:hover {
            background-color:rgb(12, 68, 12); /* DARKER GREEN on hover */
            border-color: #008000; /* Match border color */
            color: white; /* Change text color to white for better contrast */
            cursor: pointer; /* Indicate it's clickable */
        }

        /* Adjust button styling for the container if needed */
        .d-flex.justify-content-center .btn {
             /* flex: 1; */ /* Removed this line as it's for multiple buttons */
             /* max-width: 300px; */
             margin-right: 0 !important; /* Ensure no margin */
        }
    </style>
</head>

<body>
    {{-- The include('web.auth.navbar') likely renders the navbar here --}}

    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-body">
                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                 {{-- Display Success Message --}}
                @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ __(session()->get('success')) }}
                </div>
                @endif

                {{-- Display Error Message --}}
                @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ __(session()->get('error')) }}
                </div>
                @endif

                {{-- Image Upload/Update Form --}}
                {{-- This form will handle ONLY the image upload --}}
                
                <form
                    action="{{ $user->hasMedia('profile_picture') ? route('image.update') : route('image.add') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="profilePictureForm" {{-- Keep the ID --}}
                >
                    @csrf

                    @if ($user->hasMedia('profile_picture'))
                        @method('PUT') {{-- Or PATCH, match your route definition --}}
                    @endif

                    <div class="text-center mb-4">
                         {{-- Hidden file input --}}
                        <input
                            type="file"
                            name="image"
                            id="imageUpload" {{-- Keep the ID --}}
                            class="d-none" {{-- Keep hidden --}}
                            onchange="this.form.submit()" {{-- Auto-submit this form --}}
                        />
                        
                        {{-- Add hidden fields for model, model_id, and collection --}}
                        <input type="hidden" name="model" value="{{ $user::class }}"> {{-- Use the userable_type as the model name --}}
                        <input type="hidden" name="model_id" value="{{ $user->id }}">
                        {{-- We pass user ID in the route, but if your controller expects model_id in request body, add this: --}}
                        <input type="hidden" name="collection" value="profile_picture"> {{-- Specify the collection name --}}


                        {{-- Display the image (inside a label linked to the input) --}}
                        <label for="imageUpload" style="cursor: pointer;">
                            <img
                                class="rounded-circle"
                                width="150px"
                                src="{{ $user->getFirstMediaUrl('profile_picture') ?: asset('images/upload_default.jpg') }}"
                                alt="{{ __($user->name . '\'s profile picture') }}"
                            >
                        </label>
                    </div>
                </form>

                {{-- Form for updating the user profile (text fields) --}}
                {{-- Action should be your user update route --}}
                <form method="POST" action="{{ route('user.update', ['user' => $user->id]) }}"> {{-- Assuming your user update route is named 'user.update' --}}
                    @csrf
                    @method('PUT') {{-- Use PUT method for updates --}}

                    {{-- Editable Fields --}}
                    {{-- ... (your name, phone, email, address, country fields) ... --}}
                     <div class="mb-3">
                        <label for="name" class="form-label">@lang('Name'):</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                         @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">@lang('Phone'):</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                         @error('phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">@lang('Email address'):</label>
                        <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
                         @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">@lang('Home Address'):</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $user->address) }}">
                         @error('address')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">@lang('Country'):</label>
                        <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $user->country) }}">
                         @error('country')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Conditional Editable Fields based on role --}}
                    @if($user->userable_type == "App\Models\Veterinarian" )
                    <div class="mb-3">
                        <label for="degree" class="form-label">@lang('Degree'):</label>
                        <input type="text" class="form-control" id="degree" name="degree" value="{{ old('degree', $user->userable->degree ?? '') }}">
                         @error('degree')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="degree_year" class="form-label">@lang('Degree Year'):</label>
                        <input type="text" class="form-control" id="degree_year" name="degree_year" value="{{ old('degree_year', $user->userable->degree_year ?? '') }}">
                         @error('degree_year')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="university" class="form-label">@lang('University'):</label>
                        <input type="text" class="form-control" id="university" name="university" value="{{ old('university', $user->userable->university ?? '') }}">
                         @error('university')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    @if($user->userable_type == "App\Models\Partner" )
                    <div class="mb-3">
                        <label for="website" class="form-label">@lang('Website'):</label>
                        <input type="text" class="form-control" id="website" name="website" value="{{ old('website', $user->userable->website ?? '') }}">
                         @error('website')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif


                    {{-- Update Profile Button --}}
                    <div class="mt-3 text-center d-flex justify-content-center">
                        <button type="submit" class="btn update-profile-button">@lang('Update Profile')</button> {{-- This button submits *this* form --}}
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
