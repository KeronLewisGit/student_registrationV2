{{-- All sections of a printed student profile, rendered from App\Support\ProfileLayout
     as table rows so the browser and dompdf lay them out the same way. Every value
     goes through students.partials.field so nothing is ever left blank. --}}
@php($forPdf = $forPdf ?? false)
@foreach(\App\Support\ProfileLayout::sections($student) as $section)
    <div class="section-card">
        @if($section['compact'] !== null)
            <div class="section-title">
                {{ $section['title'] }}
                <span class="compact-value">
                    @if($section['compact'] !== '')
                        {{ $section['compact'] }}
                    @else
                        <span class="not-recorded">Not recorded</span>
                    @endif
                </span>
            </div>
        @else
            <div class="section-title">{{ $section['title'] }}</div>
            <table class="grid">
                @foreach($section['rows'] as $row)
                    <tr>
                        @php($used = 0)
                        @foreach($row as $field)
                            @php($used += $field['span'])
                            @include('students.partials.field', [
                                'label' => $field['label'],
                                'value' => $field['value'],
                                'format' => $field['format'],
                                'span' => $field['span'],
                                'forPdf' => $forPdf,
                            ])
                        @endforeach
                        @if($used < 4)
                            <td colspan="{{ 4 - $used }}"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
@endforeach
