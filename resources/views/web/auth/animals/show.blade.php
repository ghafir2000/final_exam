@extends('web.layout') 

<title>@lang('Dr.Pets - ') {{ $animal->name }}</title>


@section('content') {{-- Define the content section --}}
<body>
    <div id="navbar"></div>
    <div class="container">
        <div class="card mx-auto" style="width: 80%;">
            <div class="card-body">
                <table class="table table-bordered text-center align-middle">
                    <thead style="background-color: #F7DC6F;">
                        <tr>
                            <th colspan="4">@lang('Animal Profile')</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Animal Row -->
                        <tr>
                            <td rowspan="{{ ceil(count($animal->breeds) / 3) + 1 }}">
                                <img src="{{ $animal->getFirstMediaUrl('animal_picture') ?: asset('images/upload_default.jpg') }}" class="img-fluid img-thumbnail" alt="{{ $animal->name }}">
                                <p><strong>{{ $animal->name }}</strong></p>
                                <p>{{ $animal->description }}</p>
                                @can('edit media')
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <a href="{{ route('animal.edit', ['id' => $animal->id]) }}" class="btn btn-warning">@lang('Edit animal')</a>
                                    @can('edit users')
                                    <form action="{{ route('animal.destroy', ['id' => $animal->id]) }}" method="POST" class="ms-2">
                                        @csrf
                                        @method('DELETE')
                                        <div style="margin-top: 15px;">
                                            <button type="submit" class="btn btn-danger ml-2">@lang('Delete animal')</button>
                                        </div>
                                    </form>
                                    @endcan
                                </div>
                                @endcan
                            </td>
                        </tr>

                        <!-- Breeds Rows -->
                        @foreach($animal->breeds->chunk(3) as $breedChunk)
                            <tr>
                                @foreach($breedChunk as $breed)
                                    <td>
                                        <img src="{{ $breed->getFirstMediaUrl('breed_picture') ?: asset('images/upload_default.jpg') }}" class="img-fluid img-thumbnail" alt="{{ $breed->name }}" style="width: 100px; height: 100px;">
                                        <p><strong>{{ $breed->name }}</strong></p>
                                        <p>{{ $breed->description }}</p>
                                    </td>
                                @endforeach
                                
                                <!-- Empty cells for rows with less than 3 breeds -->
                                @for($i = 0; $i < 3 - $breedChunk->count(); $i++)
                                    <td></td>
                                @endfor
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

