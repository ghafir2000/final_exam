@extends('web.layout') 

<title>{{ __('Dr.Pets - Your Profile') }}</title>


@section('content') {{-- Define the content section --}}
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-body">
                @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
                @endif

                <div class="text-center mb-4">
                         <img class="rounded-circle" width="150px" src= "{{ $user->getFirstMediaUrl('profile_picture') ?: asset('images/upload_default.jpg') }}" alt="{{ $user->name }}'s profile picture">
                        <h3 class="mt-3">{{ $user->name }}</h3>
                        <h6 class="mt-1">{{ __(':role', ['role' => str_replace('App\\Models\\', '', $user->userable_type)]) }}</h6>
                    </div>

                    {{-- ... rest of your profile card content ... --}}

                    <div class="mb-3">
                        <label for="phone" class="form-label">{{ __('Phone:') }}</label>
                        <label class="form-control" id="phone">{{ $user->phone }}</label>
                    </div>
                    {{-- ... other fields ... --}}
                     <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address:') }}</label>
                        <label class="form-control" id="email">{{ $user->email }}</label>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">{{ __('Home Address:') }}</label>
                        <label class="form-control" id="address">{{ $user->address }}</label>
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">{{ __('Country:') }}</label>
                        <label class="form-control" id="country">{{ $user->country }}</label>
                    </div>

                    {{-- Conditional fields based on role --}}
                    @if($user->userable_type == "App\Models\Veterinarian")
                    <div class="mb-3">
                        <label for="degree" class="form-label">{{ __('Degree') }}</label>
                        <label class="form-control" id="degree">{{ $user->userable->degree ?? __('N/A') }}</label>
                    </div>
                    <div class="mb-3">
                        <label for="degree_year" class="form-label">{{ __('Degree Year') }}</label>
                        <label class="form-control" id="degree_year">{{ $user->userable->degree_year ?? __('N/A') }}</label>
                    </div>
                    <div class="mb-3">
                        <label for="university" class="form-label">{{ __('University') }}</label>
                        <label class="form-control" id="university">{{ $user->userable->university ?? __('N/A') }}</label>
                    </div>
                    @endif

                    @if($user->userable_type == "App\Models\Partner")
                    <div class="mb-3">
                        <label for="website" class="form-label">{{ __('Website') }}</label>
                        <label class="form-control" id="website">{{ $user->userable->website ?? __('N/A') }}</label>
                    </div>
                    @endif

                    <div class="mt-3">
                        <div class="row g-2 justify-content-center">

                            <div class="col-12 col-md-6 text-center">
                                <a href="{{ route('user.edit')}}" class="btn btn-success profile-button w-100">{{ __('Edit Profile') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
