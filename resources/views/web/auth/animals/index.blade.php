@extends('web.layout') 

<title>@lang('Dr.Pets - Animals')</title>


@section('content') {{-- Define the content section --}}

<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">@lang('All Animals')</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('Name')</th>
                            <th scope="col">@lang('Animal Description')</th>
                            <th scope="col">@lang('Breeds')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($animals as $animal)
                            <tr>
                                <td><a href="{{ route('animal.show', ['id' => $animal->id]) }}">{{ $animal->name }}</a></td>
                                <td>{{ $animal->description }}</td>
                                <td>
                                    @foreach($animal->breeds as $breed)
                                        <div><a href="{{ route('breed.show', ['id' => $breed->id]) }}">{{ $breed->name }}</a></div>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

