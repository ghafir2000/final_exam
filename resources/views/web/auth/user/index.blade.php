@extends('web.layout') 

<title>@lang('Dr.Pets -') {{__(str_replace('App\\Models\\', '', $users[0]->userable_type))}}</title>


@section('content') {{-- Define the content section --}}
<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">@lang('All') {{__(str_replace('App\\Models\\', '', $users[0]->userable_type))}}s</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('Name')</th>
                            <th scope="col">@lang('Email')</th>
                            @if($users[0]->userable_type == "App\Models\Veterinarian")
                                <th scope="col">@lang('Degree')</th>
                                <th scope="col">@lang('Degree Year')</th>
                                <th scope="col">@lang('University')</th>
                            @elseif($users[0]->userable_type == "App\Models\Partner")
                                <th scope="col">@lang('Website')</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td><a href="{{ route('user.show', ['id' => $user->id]) }}">{{ $user->name }}</a></td>
                                <td>{{ $user->email }}</td>
                                @if($user->userable_type == 'App\Models\Veterinarian')
                                    <td>{{ $user->userable->degree }}</td>
                                    <td>{{ $user->userable->degree_year }}</td>
                                    <td>{{ $user->userable->university }}</td>
                                @elseif($user->userable_type == 'App\Models\Partner')
                                    <td><a href="{{ $user->userable->website }}" target="_blank">{{ $user->userable->website }}</a></td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </body>
    @endforeach


