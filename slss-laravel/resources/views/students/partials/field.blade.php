{{-- One label/value cell of a printed profile.

     Every field on the record goes through this partial so that missing data
     is always rendered the same way ("Not recorded"), never as a blank.

     Params:
       label   - field caption
       value   - raw value (string, Carbon date, or null)
       format  - optional: 'name' (Title Case), 'date' (d/m/Y), 'document'
                 (link on screen / "On file" on paper), 'yesno' (0/1 -> No/Yes)
       col     - optional grid class, default col-md-3 (one of four columns) --}}
@php
    $format = $format ?? null;
    $display = \App\Models\Student::printValue($value ?? null, $format);
    $documentUrl = $format === 'document' ? \App\Models\Student::documentUrl($value ?? null) : null;
@endphp
<div class="{{ $col ?? 'col-md-3' }}">
    <h5>{{ $label }}</h5>
    <p>
        @if($display === null)
            <span class="not-recorded">Not recorded</span>
        @elseif($documentUrl)
            <a href="{{ $documentUrl }}" target="_blank" rel="noopener" class="no-print">View document</a><span class="print-only">On file</span>
        @else
            {{ $display }}
        @endif
    </p>
</div>
