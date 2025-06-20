@extends('web.layout') 

<title>{{ __('Dr.Pets - :name', ['name' => $user->name]) }}</title>

@section('content') {{-- Define the content section --}}
<style>
        .transparent-button {
            background-color: transparent;
            border: 2px dotted grey;
            transition: all 0.3s ease;
        }

        .transparent-button:hover {
            background-color: rgba(0, 0, 0, 0.1);
            border: 2px dotted black;
        }

        .text-fade-in {
            opacity: 1;
            color: grey;
            transition: opacity 0.3s ease;
            font-weight: bold;
        }

        .transparent-button:hover .text-fade-in {
            opacity: 1;
            color: black;
        }

        .d-flex.justify-content-center .btn {
            flex: 1;
            margin-right: .5rem;
        }

        .d-flex.justify-content-center .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-body">
                @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
                @endif
                
                <div class="text-center mb-4">
                    <img class="rounded-circle" width="150px" src= "{{ $user->getFirstMediaUrl('profile_picture') ?: asset('images/upload_default.jpg') }}" alt="{{ __(':name\'s profile picture', ['name' => $user->name]) }}">
                    <h3 class="mt-3">{{ $user->name }}</h3>
                    <h6 class="mt-1">{{ str_replace('App\\Models\\', '', $user->userable_type) }}</h6>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Phone:') }}</label>
                    <label class="form-control">{{ $user->phone }}</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Email address:') }}</label>
                    <label class="form-control">{{ $user->email }}</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Home Address:') }}</label>
                    <label class="form-control">{{ $user->address }}</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Country:') }}</label>
                    <label class="form-control">{{ $user->country }}</label>
                </div>

                @if($user->userable_type == "App\Models\Veterinarian")
                <div class="mb-3">
                    <label class="form-label">{{ __('Degree') }}</label>
                    <label class="form-control">{{ $user->userable->degree ?? __('N/A') }}</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Degree Year') }}</label>
                    <label class="form-control">{{ $user->userable->degree_year ?? __('N/A') }}</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('University') }}</label>
                    <label class="form-control">{{ $user->userable->university ?? __('N/A') }}</label>
                </div>
                @endif

                @if($user->userable_type == "App\Models\Partner")
                <div class="mb-3">
                    <label class="form-label">{{ __('Website') }}</label>
                    <label class="form-control">{{ $user->userable->website ?? __('N/A') }}</label>
                </div>
                @endif
                @if($user->userable_type == "App\Models\Admin")
                <div class="mb-3">
                    <label class="form-label">{{ __('Assigned Role') }}</label>
                    <label class="form-control">{{ $user->roles->pluck('name')->first() ?? __('N/A') }}</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Permissions') }}</label>
                    <label class="form-control">
                        {{ $user->getAllPermissions()->isNotEmpty() ? $user->getAllPermissions()->pluck('name')->implode(', ') : __('N/A') }}
                    </label>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
        integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>

