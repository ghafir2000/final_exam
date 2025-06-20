@extends('web.layout')

{{-- Assuming $record is passed to this view, and its relationships are loaded --}}
<title>@lang('Dr.Pets - Record') {{ $record->booking->pet->name }} (@lang('Dr.Pets -') {{ $record->created_at->format('M j, Y') }})</title>

@section('content')
<style>
    /* Styles from your previous good version or new ones */
    .record-detail-card .card-header,
    .previous-records-card .card-header {
        font-weight: bold;
    }

    .previous-records-card .card-body {
        max-height: 500px; /* Can be adjusted */
        overflow-y: auto;
    }

    .record-section-item, /* For main record details items */
    .previous-record-item { /* For items in the previous records list */
        border-bottom: 1px solid #eee;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    .record-section-item:last-child,
    .previous-record-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .stats-key-value-list .stat-kv-item {
        display: flex;
        justify-content: space-between;
        padding: 0.3rem 0;
        border-bottom: 1px dotted #f0f0f0;
        line-height: 1.4;
    }
    .stats-key-value-list .stat-kv-item:last-child {
        border-bottom: none;
    }
    .stats-key-value-list .stat-key {
        font-weight: 600;
        margin-right: 10px;
        flex-shrink: 0;
        /* Consider a min-width if helpful: e.g., min-width: 150px; */
    }
    .stats-key-value-list .stat-value {
        text-align: left;
        word-break: break-word;
        flex-grow: 1;
    }

    .notes-block {
        background-color: #f8f9fa;
        padding: 12px 15px; /* Slightly more padding */
        border-radius: 5px; /* Slightly more rounded */
        margin-top: 12px;
        border: 1px solid #e0e0e0; /* Softer border */
    }
    .notes-block strong {
        display: block;
        margin-bottom: 8px; /* More space after "Notes:" */
        font-size: 1.05em; /* Slightly larger "Notes:" heading */
    }

    .compact-pre {
        margin-bottom: 0;
        padding: 8px 10px; /* Adjusted padding */
        background-color: #ffffff; /* Cleaner background for pre */
        border: 1px solid #ced4da; /* Standard border */
        border-radius: 4px;
        font-size: 0.9em; /* Consistent font size */
        white-space: pre-wrap;
        word-break: break-all;
    }
    .notes-content { /* For notes pre if inside notes-block */
        background-color: transparent;
        border: none;
        padding: 0;
    }

    .action-buttons .btn {
        margin-right: 0.5rem;
    }
    .action-buttons .btn:last-child {
        margin-right: 0;
    }

</style>

<div class="container mt-4">
    {{-- Display general success or error messages --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            @lang(session('success'))
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            @lang(session('error'))
        </div>
    @endif
    {{-- Errors display can be removed if not editing on this page --}}
    @if ($errors->any() && !isset($hideErrors)) {{-- Example: $hideErrors can be set if it's purely a view page --}}
        <div class="alert alert-danger">
            <p class="fw-bold">@lang('Please correct the following errors:')</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>@lang($error)</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Column for Main Record Details --}}
        <div class="col-md-7 mb-4 mb-md-0 mx-auto">
            <div class="card record-detail-card">
                <div class="card-header text-center" style="background-color: #A9DFBF;">
                    <h3>
                        @lang('Dr.Pets - Medical Record'): {{ $record->booking->pet->name }}
                        @if($record->booking->pet->breed && $record->booking->pet->breed->animal)
                            (@lang('Dr.Pets -') {{ $record->booking->pet->breed->animal->name }} - {{ $record->booking->pet->breed->name }})
                        @elseif($record->booking->pet->breed)
                            ({{ $record->booking->pet->breed->name }})
                        @elseif($record->booking->pet->species)
                            ({{ $record->booking->pet->species }})
                        @endif
                    </h3>
                    @if($record->booking->service)
                        <h5>@lang('Dr.Pets - Service'): {{ $record->booking->service->name }}</h5>
                    @endif
                    <p class="mb-0">
                        <i class="fas fa-calendar-alt"></i> @lang('Dr.Pets - Recorded'): {{ $record->created_at->format('F j, Y \a\\t g:i A') }}
                    </p>
                    @if($record->booking->service && $record->booking->service->servicable)
                        <p class="mb-0 small">
                            <i class="fas fa-user-md"></i> @lang('Dr.Pets - Vet/Clinic'): {{ $record->booking->service->servicable->userable->name ?? __('N/A') }}
                        </p>
                    @endif
                </div>
                <div class="card-body">
                    <div class="record-section-item"> 
                        @if ($record->stats)
                            @php
                                $mainRecordNotes = $record->stats['notes'] ?? null;
                            @endphp

                            @if(count(array_filter(array_keys($record->stats), fn($key) => $key !== 'notes')) > 0)
                                <h5 class="mb-2"><i class="fas fa-stethoscope"></i> @lang('Dr.Pets - Stats & Observations')</h5>
                                <div class="stats-key-value-list">
                                    @foreach($record->stats as $key => $value)
                                        @if($key === 'notes') @continue @endif
                                        <div class="stat-kv-item">
                                            <span class="stat-key">@lang(Str::title(str_replace(['_', '-'], ' ', $key))):</span>
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
                            @endif

                            @if($mainRecordNotes)
                                <div class="notes-block">
                                    <strong><i class="fas fa-file-alt"></i> @lang('Dr.Pets - General Notes / Diagnosis / Plan'):</strong>
                                    <pre class="compact-pre notes-content">{{ $mainRecordNotes }}</pre>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mt-2"><em>@lang('Dr.Pets - No detailed stats or notes were recorded for this entry.')</em></p>
                        @endif
                    </div>
                    <div class="mt-3 pt-3 border-top action-buttons">
                        <a href="{{ route('record.index', $record->booking->pet_id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> @lang('Dr.Pets - All Records for') {{ $record->booking->pet->name }}
                        </a>
                        {{-- Add Edit button if applicable --}}
                        {{-- @can('update', $record)
                            <a href="{{ route('record.edit', $record->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit"></i> @lang('Dr.Pets - Edit Record')
                            </a>
                        @endcan --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
