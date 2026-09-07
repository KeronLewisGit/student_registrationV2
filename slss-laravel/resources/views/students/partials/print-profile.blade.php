{{-- One printable student profile (header + all profile sections). Expects $student. --}}
<div class="profile-card">
    <div class="profile-inner">
        <div class="row align-items-start mb-4">
            <div class="col-md-3">
                <h6 class="fw-bold mb-2">Passport Size Photo</h6>
                @if($student->student_passport_photo)
                    <img src="{{ asset($student->student_passport_photo) }}" alt="Passport photo of {{ $student->student_name }}" class="passport-photo">
                @else
                    <img src="{{ asset('images/noimage.jpg') }}" alt="No Image" class="passport-photo">
                @endif
            </div>
            <div class="col-md-6 text-center">
                <h2 class="fw-bold mb-2" style="font-size: 2rem;">
                    Success Laventille Secondary School<br>Eastern Main Road
                </h2>
                <p class="text-muted">Official Student Record</p>
            </div>
            <div class="col-md-3 text-end">
                <img src="{{ asset('images/successlogo.png') }}" alt="SLSS Logo" class="school-logo">
            </div>
        </div>

        @include('students.partials.profile-sections', ['student' => $student])
    </div>
</div>
