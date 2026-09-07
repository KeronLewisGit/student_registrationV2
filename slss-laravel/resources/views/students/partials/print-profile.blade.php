{{-- One printable student profile (header + all profile sections). Expects $student. --}}
@php($student = $student->forPrint())
<div class="profile-card">
    <div class="profile-inner">
        <div class="row align-items-start mb-4 print-header">
            <div class="col-md-3">
                <h6 class="fw-bold mb-2">Passport Size Photo</h6>
                @if($student->student_passport_photo)
                    <img src="{{ \App\Models\Student::documentUrl($student->student_passport_photo) ?? asset('images/noimage.jpg') }}"
                         alt=""
                         class="passport-photo"
                         onerror="this.onerror=null; this.src='{{ asset('images/noimage.jpg') }}';">
                @else
                    <img src="{{ asset('images/noimage.jpg') }}" alt="No Image" class="passport-photo">
                @endif
            </div>
            <div class="col-md-6 text-center">
                <h2 class="fw-bold mb-2" style="font-size: 2rem;">
                    Success Laventille Secondary School<br>Eastern Main Road
                </h2>
                <p class="text-muted record-title">Official Student Record</p>
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
            </div>
            <div class="col-md-3 text-end">
                <img src="{{ asset('images/successlogo.png') }}" alt="SLSS Logo" class="school-logo">
            </div>
        </div>

        @include('students.partials.profile-sections', ['student' => $student])

        <div class="print-footer">
            <span>Success Laventille Secondary School &middot; Official Student Record</span>
            <span>{{ $student->student_name ? ucwords(strtolower($student->student_name)) : '' }}@if($student->student_birth_certificate_pin) &middot; PIN {{ $student->student_birth_certificate_pin }}@endif</span>
            <span>Printed {{ now()->format('d/m/Y') }}</span>
        </div>
    </div>
</div>
