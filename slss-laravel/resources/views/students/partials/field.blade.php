{{-- One label/value cell of a printed profile (a table cell spanning 1-4 of
     the 4 grid columns). Every field on the record goes through this partial
     so that missing data is always rendered the same way ("Not recorded").

     Params: label, value, format ('name' | 'date' | 'document' | 'yesno' | null),
             span (1-4, default 1), forPdf (bool) --}}
@php
    $format = $format ?? null;
    $span = $span ?? 1;
    $forPdf = $forPdf ?? false;
    $display = \App\Models\Student::printValue($value ?? null, $format);
    $documentUrl = $format === 'document' ? \App\Models\Student::documentUrl($value ?? null) : null;
@endphp
<td colspan="{{ $span }}" class="span-{{ $span }}">
    <h5>{{ $label }}</h5>
    <p>
        @if($display === null)
            <span class="not-recorded">Not recorded</span>
        @elseif($documentUrl)
            @if($forPdf)
                On file
            @else
                <a href="{{ $documentUrl }}" target="_blank" rel="noopener" class="no-print">View document</a><span class="print-only">On file</span>
            @endif
        @else
            {{ $display }}
        @endif
    </p>
</td>
