@extends('web.layout')

<title>{{ __('Medical Records for :name - Dr.Pets', ['name' => $pet->name]) }}</title>

@section('content')
<style>
    .record-card {
        margin-bottom: 1.5rem;
        border-left: 5px solid #007bff; /* Accent color for cards */
    }
    .record-card .card-header {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    .stats-display-list {
        list-style-type: none;
        padding-left: 0;
    }
    .stats-display-list li {
        padding: 0.25rem 0;
        border-bottom: 1px dashed #eee;
    }
    .stats-display-list li:last-child {
        border-bottom: none;
    }
    .stats-display-list strong {
        display: inline-block;
        min-width: 120px; /* Adjust as needed for alignment */
    }
    .notes-section {
        margin-top: 10px;
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 4px;
        white-space: pre-wrap; /* Preserve formatting of notes */
    }
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px dashed #ced4da;
    }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ __('Medical History: :name', ['name' => $pet->name]) }}</h2>
            <p class="lead text-muted">
                {{ __('Species: :species | Breed: :breed', ['species' => $pet->breed->animal->name, 'breed' => $pet->breed->name]) }}
                @if($pet->owner)
                    | {{ __('Owner: :owner', ['owner' => $pet->customer->userable->name]) }}
                @endif
            </p>
        </div>
        <a href="{{ route('pet.show', $pet->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('Back to Pet Profile') }}
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if($pet->records->isEmpty())
        <div class="empty-state">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <h4>{{ __('No Medical Records Found') }}</h4>
            <p>{{ __('There are no medical records available for :name yet.', ['name' => $pet->name]) }}</p>
        </div>
    @else
        <p class="mb-3 text-muted">{{ __('Displaying :count record(s). Newest first.', ['count' => $pet->records->count()]) }}</p>
        @foreach($pet->records as $record)
            <div class="card record-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-calendar-alt"></i> {{ __('Record Date: :date', ['date' => $record->created_at->format('F j, Y \a\\t g:i A')]) }}
                    </span>
                    @if($record->booking && $record->booking->service && $record->booking->service->servicable)
                        <small class="text-muted">
                            <i class="fas fa-user-md"></i> {{ __('Vet/Clinic: :name', ['name' => $record->booking->service->servicable->userable->name]) }}
                        </small>
                    @endif
                    <a href="{{ route('record.show', $record->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> {{ __('Show Record') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($record->booking && $record->booking->service)
                        <h5 class="card-title">
                            <i class="fas fa-stethoscope"></i> {{ __('Service: :service', ['service' => $record->booking->service->name]) }}
                        </h5>
                        <p class="card-text text-muted">
                            {{ __('Booking ID: :id | Scheduled: :date at :time', [
                                'id' => $record->booking->id,
                                'date' => \Carbon\Carbon::parse($record->booking->date)->format('D, M d, Y'),
                                'time' => \Carbon\Carbon::parse($record->booking->time)->format('g:i A')
                            ]) }}
                        </p>
                        <hr>
                    @else
                        <h5 class="card-title text-muted">{{ __('General Record (No specific booking linked or booking data missing)') }}</h5>
                        <hr>
                    @endif

                    <h6><i class="fas fa-notes-medical"></i> {{ __('Recorded Stats & Observations:') }}</h6>
                    @if($record->stats && (is_array($record->stats) ? count($record->stats) : false))
                        <ul class="stats-display-list">
                            @php $generalNotes = null; @endphp
                            @foreach($record->stats as $key => $value)
                                @if(strtolower($key) === 'notes')
                                    @php $generalNotes = $value; @endphp
                                    @continue
                                @endif
                                <li>
                                    <strong>{{ __(Str::title(str_replace(['_', '-'], ' ', $key))) }}:</strong>
                                    @if(is_array($value) || is_object($value))
                                        <pre class="mb-0" style="display: inline-block; background-color: #f8f9fa; padding: 2px 5px; border-radius: 3px;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        {{ $value ?? __('N/A') }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if($generalNotes)
                            <div class="notes-section">
                                <strong>{{ __('General Notes:') }}</strong><br>
                                {{ $generalNotes }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted"><em>{{ __('No specific stats were recorded for this entry.') }}</em></p>
                    @endif

                    @if ($record->notes && !$generalNotes)
                        <div class="notes-section">
                            <strong>{{ __('General Notes (from dedicated field):') }}</strong><br>
                            {{ $record->notes }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection


