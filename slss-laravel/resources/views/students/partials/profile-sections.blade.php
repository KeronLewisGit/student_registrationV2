{{-- All sections of a printed student profile, rendered from App\Support\ProfileLayout
     (the PDF uses the same definition). Every value goes through students.partials.field
     so that nothing is ever left blank. --}}
@php($spanClass = [1 => 'col-md-3', 2 => 'col-md-6', 3 => 'col-md-9', 4 => 'col-md-12'])
@foreach(\App\Support\ProfileLayout::sections($student) as $section)
    @if($section['compact'] !== null)
        <div class="section-card section-card-compact">
            <div class="fw-bold">
                {{ $section['title'] }}
                <span class="compact-value">
                    @if($section['compact'] !== '')
                        {{ $section['compact'] }}
                    @else
                        <span class="not-recorded">Not recorded</span>
                    @endif
                </span>
            </div>
        </div>
    @else
        <div class="section-card">
            <div class="fw-bold mb-3 pb-2 border-bottom">{{ $section['title'] }}</div>
            @foreach($section['rows'] as $i => $row)
                <div class="row g-3 {{ $i > 0 ? 'mt-2' : '' }}">
                    @foreach($row as $field)
                        @include('students.partials.field', [
                            'label' => $field['label'],
                            'value' => $field['value'],
                            'format' => $field['format'],
                            'col' => $spanClass[$field['span']] ?? 'col-md-3',
                        ])
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
@endforeach
