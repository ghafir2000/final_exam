@extends('web.layout')

{{-- It's common practice to put the title in a dedicated section or directly in the layout's <head> --}}
{{-- For example: @section('title', 'Dr.Pets - Login') --}}
<title>@lang('Dr.Pets - Login')</title>


@section('styles')
<style>
    .main-navbar {
        display: none !important;
    }

    /* Add styles for the logo animation */
    #logo {
        position: relative; /* Crucial for the 'top' CSS property to work */
        transition: top 1s ease-in-out; /* For smooth animation */
    }
</style>
@endsection {{-- Close the styles section --}}


@section('content') {{-- Define the content section --}}

{{-- The <body> tag is usually in your 'web.layout.blade.php', not repeated here.
     If your layout doesn't have it, then it's okay here, but it's unconventional. --}}
{{-- <body> --}}
    <div style="margin-top: -40px;" class="text-center">
        {{-- Added position:relative and transition via CSS above for cleaner HTML --}}
        <img class="rounded-circle mb-2" width="250px" src="{{asset('logos/Future Features.jpg')}}" id="logo">
    </div>

    <div class="container">
        <div class="card" style="background-color: rgba(255, 255, 255, 0.0);">
            <div class="card-body">
                @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
                @endif
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">@lang('Email address')</label>
                        <input type="email" name="email" class="form-control" id="email" aria-describedby="emailHelp" style="background-color: rgba(209, 239, 211, 0.0);">
                        @error('email')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">@lang('Password')</label>
                        <input type="password" name="password" class="form-control" id="password" style="background-color: rgba(209, 239, 211, 0.0);">
                        @error('password')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success">@lang('Login')</button>
                    <a href="{{ route('register') }}" class="btn btn-light border border-success">@lang('Register')</a>
                </form>
            </div>
        </div>
    </div>

    {{-- Place scripts towards the end of the body for better performance and DOM readiness --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logo = document.getElementById('logo');

            if (logo) {
                // For position: relative, '0px' is its natural flow position.
                // '-10px' will move it 10px upwards from its natural position.
                const naturalPositionTop = '10px';
                const upPositionTop = '-10px';
                let isUp = false; // Start in natural position

                setInterval(() => {
                    if (isUp) {
                        logo.style.top = naturalPositionTop;
                    } else {
                        logo.style.top = upPositionTop;
                    }
                    isUp = !isUp; // Toggle the state
                }, 700); // Animate every 1 second
            } else {
                console.error("Logo element with id 'logo' not found.");
            }
        });
    </script>

    {{-- These scripts are fine where they are, usually at the end of the body content --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
        integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- </body> --}} {{-- Corresponds to the opening <body> tag if you keep it --}}
{{-- </html> --}} {{-- Also usually in the layout --}}

@endsection {{-- Close the content section --}}
