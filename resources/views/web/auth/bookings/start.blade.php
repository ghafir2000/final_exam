@extends('web.layout')

{{-- Assuming $booking is passed to this view --}}
<title>{{ __('Dr.Pets - Start Session: :petName - :serviceName', ['petName' => $booking->pet->name, 'serviceName' => $booking->service->name]) }}</title>

@section('content')
<style>
/* Styles from your existing <style> tag */
.stat-entry { /* For the form section */
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    gap: 0.5rem;
}
.stat-entry .form-control {
    flex-grow: 1;
}
.stat-entry .btn-danger {
    flex-shrink: 0;
}
.previous-records-card .card-body {
    max-height: 450px;
    overflow-y: auto;
}

/* New/Modified styles for Previous Records display */
.previous-record-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}
.previous-record-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.stats-key-value-list .stat-kv-item {
    display: flex; /* Key and value on the same line */
    justify-content: space-between; /* Pushes key left, value right */
    padding: 0.3rem 0; /* Vertical padding */
    border-bottom: 1px dotted #f0f0f0; /* Light separator for each stat */
    line-height: 1.4; /* Better readability */
}
.stats-key-value-list .stat-kv-item:last-child {
    border-bottom: none;
}

.stats-key-value-list .stat-key {
    font-weight: 600; /* Make key bold */
    margin-right: 10px; /* Space between key and value */
    flex-shrink: 0; /* Prevent key from shrinking too much */
    /* You could set a min-width here if needed, e.g., min-width: 120px; */
}

.stats-key-value-list .stat-value {
    text-align: left; /* Align value text to the left (was right before, might not be desired) */
    word-break: break-word; /* Allow long values to wrap */
    flex-grow: 1; /* Allow value to take remaining space */
}

.notes-block {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
}
.notes-block strong {
    display: block;
    margin-bottom: 5px;
}

.compact-pre { /* For displaying JSON or preformatted notes cleanly */
    margin-bottom: 0;
    padding: 5px;
    background-color: #e9ecef; /* Slightly different background for pre */
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.875em; /* Make pre text a bit smaller */
    white-space: pre-wrap; /* Crucial for wrapping */
    word-break: break-all; /* Break long words/strings in JSON */
}
.notes-content { /* Specific styling for notes content if needed */
    background-color: transparent; /* Make notes pre transparent if inside notes-block */
    border: none;
    padding: 0;
}  
</style>

<div class="container mt-4">
    {{-- Display general success or error messages --}}
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
    @if ($errors->any())
        <div class="alert alert-danger">
            <p class="fw-bold">{{ __('Please correct the following errors:') }}</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Column for Current Session & New Record --}}
        <div class="col-md-7 mb-4 mb-md-0">
            <div class="card">
                <div class="card-header text-center" style="background-color: #A9DFBF;">
                    <h3>{{ __('Session for: :petName', ['petName' => $booking->pet->name]) }}
                        @if($booking->pet->breed && $booking->pet->breed->animal)
                            ({{ __(':animal - :breed', ['animal' => $booking->pet->breed->animal->name, 'breed' => $booking->pet->breed->name]) }})
                        @elseif($booking->pet->breed)
                            ({{ __(':breed', ['breed' => $booking->pet->breed->name]) }})
                        @elseif($booking->pet->species)
                            ({{ __(':species', ['species' => $booking->pet->species]) }})
                        @endif
                    </h3>
                    <h5>{{ __('Service: :serviceName', ['serviceName' => $booking->service->name]) }}</h5>
                    <p class="mb-0">{{ __('Scheduled: :date at :time', ['date' => \Carbon\Carbon::parse($booking->date)->format('D, M d, Y'), 'time' => \Carbon\Carbon::parse($booking->time)->format('g:i A')]) }}</p>
                    @if($booking->service->servicable)
                        <p class="mb-0 small">{{ __('Vet/Clinic: :name', ['name' => $booking->service->servicable->userable->name ?? __('N/A')]) }}</p>
                    @endif
                </div>
                <div class="card-body">
                    <h4>{{ __('Enter New Record Details') }}</h4>
                    <form action="{{ route('record.store')}}" method="POST" id="newRecordForm">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Current Stats / Observations:') }}</label>
                            <div id="stats-container">
                                @if(old('stats'))
                                    @foreach(old('stats') as $index => $statPair)
                                        @if ($index === 'notes') @continue @endif {{-- Skip 'notes' here, handled by textarea --}}
                                        @if(isset($statPair['key']) || isset($statPair['value']))
                                        <div class="stat-entry mb-2">
                                            <input type="text" name="stats[{{ $index }}][key]" class="form-control form-control-sm @error('stats.'.$index.'.key') is-invalid @enderror" placeholder="{{ __('Stat Name (e.g., Weight)') }}" value="{{ $statPair['key'] ?? '' }}">
                                            <input type="text" name="stats[{{ $index }}][value]" class="form-control form-control-sm @error('stats.'.$index.'.value') is-invalid @enderror" placeholder="{{ __('Stat Value (e.g., 10kg)') }}" value="{{ $statPair['value'] ?? '' }}">
                                            <button type="button" class="btn btn-danger btn-sm remove-stat-btn"><i class="fas fa-times"></i></button>
                                        </div>
                                        @error('stats.'.$index.'.key') <div class="text-danger small d-block">{{ $message }}</div> @enderror
                                        @error('stats.'.$index.'.value') <div class="text-danger small d-block">{{ $message }}</div> @enderror
                                        @endif
                                    @endforeach
                                @else
                                    {{-- Add one empty row to start if no old data --}}
                                    <div class="stat-entry mb-2">
                                        <input type="text" name="stats[0][key]" class="form-control form-control-sm" placeholder="{{ __('Stat Name (e.g., Weight)') }}">
                                        <input type="text" name="stats[0][value]" class="form-control form-control-sm" placeholder="{{ __('Stat Value (e.g., 10kg)') }}">
                                        <button type="button" class="btn btn-danger btn-sm remove-stat-btn"><i class="fas fa-times"></i></button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" id="add-stat-btn" class="btn btn-outline-secondary btn-sm mt-2">
                                <i class="fas fa-plus"></i> {{ __('Add Stat') }}
                            </button>
                            @error('stats') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes_field" class="form-label fw-bold">{{ __('General Notes / Diagnosis / Treatment Plan:') }}</label> {{-- Changed id for uniqueness --}}
                            <textarea name="stats[notes]" id="notes_field" class="form-control @error('stats.notes') is-invalid @enderror" rows="5" placeholder="{{ __('Enter any additional notes, diagnosis, and treatment plan here...') }}">{{ old('stats.notes') }}</textarea> {{-- Corrected old helper --}}
                            @error('stats.notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> {{ __('End Session & Save Record') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

       {{-- Previous Records Column (Relevant Part) --}}
<div class="col-md-5">
    <div class="card previous-records-card">
        <div class="card-header text-center" style="background-color: #AED6F1;">
            <h4>{{ __('Previous Records for :petName', ['petName' => $booking->pet->name]) }}</h4>
        </div>
        <div class="card-body">
            @php
                $previousRecords = $booking->pet->records()->with('booking.service')->latest()->get();
            @endphp

            @if($previousRecords->isNotEmpty())
                @foreach($previousRecords as $record)
                    {{-- Skip logic remains the same --}}
                    @if($record->booking_id === $booking->id && !$record->stats && !$record->notes) @continue @endif
                    @if($record->booking_id === $booking->id && $record->created_at->gt(\Carbon\Carbon::now()->subMinutes(1))) @continue @endif

                    <div class="previous-record-item">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0"> {{-- Reduced margin --}}
                                    <i class="fas fa-calendar-alt"></i> {{ __('Record from: :date', ['date' => $record->created_at->format('M j, Y \a\t g:i A')]) }}
                                </h6>
                                @if($record->booking && $record->booking->service)
                                    <small class="text-muted d-block">{{ __('Service: :serviceName', ['serviceName' => $record->booking->service->name]) }}</small>
                                @endif
                            </div>
                            <form action="{{ route('record.destroy', $record->id) }}" method="POST" id="delete-record-form-{{ $record->id }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="confirmRecordDelete({{ $record->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        @if($record->stats && is_array($record->stats) && count($record->stats) > 0)
                            @php
                                $currentRecordNotes = null;
                                if (isset($record->stats['notes'])) {
                                    $currentRecordNotes = $record->stats['notes'];
                                }
                            @endphp

                            {{-- Check if there are any stats other than 'notes' --}}
                            @if(count(array_filter(array_keys($record->stats), fn($key) => $key !== 'notes')) > 0)
                                <div class="mt-2">
                                    <strong class="d-block mb-1">{{ __('Stats/Observations:') }}</strong>
                                    <div class="stats-key-value-list"> {{-- New container for key-value items --}}
                                        @foreach($record->stats as $key => $value)
                                            @if($key === 'notes') @continue @endif
                                            <div class="stat-kv-item"> {{-- Each key-value pair --}}
                                                <span class="stat-key">{{ __(Str::title(str_replace(['_', '-'], ' ', $key))) }}:</span>
                                                <span class="stat-value">
                                                    @if(is_array($value) || is_object($value))
                                                        <pre class="compact-pre">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        {{ $value ?? __('N/A') }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($currentRecordNotes)
                                <div class="notes-block mt-2"> {{-- Added mt-2 for spacing --}}
                                    <strong>{{ __('Notes:') }}</strong>
                                    <pre class="compact-pre notes-content">{{ $currentRecordNotes }}</pre> {{-- Used pre for notes too --}}
                                </div>
                            @endif
                        {{-- This 'else' handles the case where 'stats' is empty or not an array --}}
                        {{-- We can add an 'elseif' for $record->notes if it's a separate DB column --}}
                        @elseif ($record->notes) {{-- If stats is empty, but separate 'notes' field has data --}}
                            <div class="notes-block mt-2">
                                <strong>{{ __('Notes:') }}</strong>
                                <pre class="compact-pre notes-content">{{ $record->notes }}</pre>
                            </div>
                        @else
                            <p class="text-muted mt-2"><em>{{ __('No detailed stats or notes were recorded for this entry.') }}</em></p>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="text-center text-muted mt-3">{{ __('No previous medical records found for this pet.') }}</p>
            @endif
        </div>
    </div>
</div>

<script>
    // JavaScript for adding/removing stat rows
    document.addEventListener('DOMContentLoaded', function() {
        const statsContainer = document.getElementById('stats-container');
        const addStatBtn = document.getElementById('add-stat-btn');

        // Try to get a more robust starting index
        let statIndex = 0;
        const existingStatInputs = statsContainer.querySelectorAll('.stat-entry input[name^="stats["]');
        existingStatInputs.forEach(input => {
            const match = input.name.match(/stats\[(\d+)\]/);
            if (match && parseInt(match[1]) >= statIndex) {
                statIndex = parseInt(match[1]) + 1;
            }
        });
        if (statIndex === 0 && existingStatInputs.length === 0) { // If no inputs, start at 0 for the first new one
             statIndex = {{ old('stats') ? (is_array(old('stats')) ? collect(old('stats'))->filter(fn($v, $k) => $k !== 'notes')->count() : 0) : 0 }};
             if (statIndex === 0) statIndex = 0; // Ensure it's at least 0 for the first element
        }


        function createStatRow() {
            const div = document.createElement('div');
            div.className = 'stat-entry mb-2';

            const keyInput = document.createElement('input');
            keyInput.type = 'text';
            keyInput.name = `stats[${statIndex}][key]`;
            keyInput.className = 'form-control form-control-sm';
            keyInput.placeholder = '{{ __('Stat Name (e.g., Temperature)') }}';

            const valueInput = document.createElement('input');
            valueInput.type = 'text';
            valueInput.name = `stats[${statIndex}][value]`;
            valueInput.className = 'form-control form-control-sm';
            valueInput.placeholder = '{{ __('Stat Value (e.g., 38.5°C)') }}';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm remove-stat-btn';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', function() {
                div.remove();
            });

            div.appendChild(keyInput);
            div.appendChild(valueInput);
            div.appendChild(removeBtn);
            statsContainer.appendChild(div);
            statIndex++;
        }

        if (addStatBtn) {
            addStatBtn.addEventListener('click', createStatRow);
        }

        if (statsContainer) {
            statsContainer.addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('remove-stat-btn') || e.target.closest('.remove-stat-btn'))) {
                    let button = e.target.classList.contains('remove-stat-btn') ? e.target : e.target.closest('.remove-stat-btn');
                    button.closest('.stat-entry').remove();
                }
            });
        }

        // If no 'stats' fields rendered by PHP (neither old data nor the default single one), add one,
        // but only if the stats-container is truly empty of .stat-entry divs.
        if (statsContainer && statsContainer.querySelectorAll('.stat-entry').length === 0) {
             if (!document.querySelector('#stats-container .stat-entry')) {
                createStatRow(); // Call it to add the first row if none exist
             }
        }
    });

    // JavaScript for confirming record deletion
    function confirmRecordDelete(recordId) {
        if (confirm('{{ __('Are you sure you want to permanently delete this medical record? This action cannot be undone.') }}')) {
            document.getElementById('delete-record-form-' + recordId).submit();
        }
    }
</script>
@endsection
