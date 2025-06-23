@extends('web.layout')

<title>{{ __('Dr.Pets - Register') }}</title>

@section('content') {{-- Define the content section --}}

<body>
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-header text-center bg-warning">
                <h2>{{ __('Register') }}</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('user.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address') }}</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">{{ __('Phone number') }}</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone"
                            value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm password') }}</label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                            name="password_confirmation" required>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Role Selection --}}
                    <div class="mb-3">
                        <label for="role" class="form-label">{{ __('Role') }}</label>
                        <select name="userable_type" id="role" class="form-select @error('userable_type') is-invalid @enderror" required>
                            <option value="">{{ __('Select role') }}</option>
                            @foreach (\App\Models\User::distinct()->pluck('userable_type')->reject(fn($type) => $type === 'App\Models\Admin') as $role)
                                <option value="{{ $role }}" {{ old('userable_type') == $role ? 'selected' : '' }}>
                                    {{ str_replace('App\\Models\\', '', $role) }}
                                </option>
                            @endforeach
                        </select>
                        @error('userable_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Dynamic Fields Container --}}
                    <div id="role-fields"></div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-success">{{ __('Register') }}</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("role").addEventListener("change", function() {
            const selectedRole = this.value;
            const fieldsContainer = document.getElementById("role-fields");
            fieldsContainer.innerHTML = ""; // Clear previous fields

            if (selectedRole.includes("Veterinarian")) {
                fieldsContainer.innerHTML += `
                    <div class="mb-3">
                        <label for="degree" class="form-label">{{ __('Degree') }}</label>
                        <input type="text" class="form-control" id="degree" name="degree" required>
                        @error('degree')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="degree_year" class="form-label">{{ __('Degree Year') }}</label>
                        <input type="text" class="form-control" id="degree_year" name="degree_year" required>
                        @error('degree_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="university" class="form-label">{{ __('University') }}</label>
                        <input type="text" class="form-control" id="university" name="university" required>
                        @error('university')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                `;
            }

            if (selectedRole.includes("Partner")) {
                fieldsContainer.innerHTML += `
                    <div class="mb-3">
                        <label for="business_name" class="form-label">{{ __('Website') }}</label>
                        <input type="text" class="form-control" id="website" name="website" required>
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                `;
            }
            // No additional fields for Customers, so nothing is appended when they are selected.
        });
    </script>
</body>
</html>

