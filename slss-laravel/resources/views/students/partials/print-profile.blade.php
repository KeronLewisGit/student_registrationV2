{{-- One printable student profile (letterhead + all sections + footer).
     Rendered identically by the browser and by dompdf. Expects $student;
     pass 'forPdf' => true from the PDF template so images use file paths. --}}
@php($student = $student->forPrint())
@php($forPdf = $forPdf ?? false)
@php($photoSrc = $student->hasUsablePhoto()
    ? ($forPdf ? \App\Models\Student::documentPath($student->student_passport_photo) : \App\Models\Student::documentUrl($student->student_passport_photo))
    : null)
@if($photoSrc && $forPdf && !file_exists($photoSrc))
    @php($photoSrc = null)
@endif
@php($placeholder = $forPdf ? public_path('images/noimage.jpg') : asset('images/noimage.jpg'))
@php($crest = $forPdf ? public_path('images/successlogo.png') : asset('images/successlogo.png'))

<div class="profile-card">
    <div class="watermark">OFFICIAL DOCUMENT</div>

    <table class="letterhead">
        <tr>
            <td class="photo-cell">
                <p class="label">Passport Size Photo</p>
                @if($photoSrc)
                    <img src="{{ $photoSrc }}" alt="" class="passport-photo" @unless($forPdf) onerror="this.onerror=null; this.src='{{ $placeholder }}';" @endunless>
                @else
                    <img src="{{ $placeholder }}" alt="No photo" class="passport-photo">
                @endif
            </td>
            <td class="title-cell">
                <h2>Success Laventille Secondary School<br>Eastern Main Road</h2>
                <p class="record-title">Official Student Record</p>
                <p class="record-subject">
                    {{ $student->student_name ? ucwords(strtolower($student->student_name)) : 'Unnamed student' }}
                    @if($student->current_class)
                        &middot; Class {{ $student->current_class }}
                    @elseif($student->form_1_class)
                        &middot; Form {{ $student->form_1_class }}
                    @endif
                    @if(!$student->isActive())
                        &middot; {{ $student->enrolment_status_label }}
                    @endif
                </p>
            </td>
            <td class="crest-cell">
                @if(!$forPdf || file_exists($crest))
                    <img src="{{ $crest }}" alt="SLSS crest" class="school-logo">
                @endif
            </td>
        </tr>
    </table>

    @include('students.partials.profile-sections', ['student' => $student, 'forPdf' => $forPdf])

    <table class="print-footer">
        <tr>
            <td style="width: 40%;">Success Laventille Secondary School &middot; Official Student Record</td>
            <td class="mid" style="width: 35%;">{{ $student->student_name ? ucwords(strtolower($student->student_name)) : '' }}@if($student->student_birth_certificate_pin) &middot; PIN {{ $student->student_birth_certificate_pin }}@endif</td>
            <td class="end" style="width: 25%;">Printed {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>
</div>
