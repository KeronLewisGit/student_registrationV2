<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Profile - {{ $student->student_name }}</title>
    <style>
        {{-- Same look as the browser print view (students/partials/print-styles):
             plain letterhead, uppercase section titles over a hairline, a four-column
             field grid, "Not recorded" in grey italics, a footer line. Tables are used
             because dompdf has no flexbox. --}}
        @page { size: Legal; margin: 15mm 12mm 13mm; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: "DejaVu Sans", sans-serif; font-size: 9.2px; line-height: 1.3; color: #111; }

        .watermark {
            position: fixed; top: 45%; left: 8%; width: 84%;
            text-align: center; font-size: 54px; font-weight: bold; letter-spacing: 6px;
            color: #f1f1f1; z-index: -1;
        }

        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; }

        .letterhead { border-bottom: 1.5px solid #111; padding-bottom: 6px; margin-bottom: 4px; }
        .letterhead .label { font-size: 6.6px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #555; margin-bottom: 3px; }
        .letterhead .photo { width: 84px; height: 84px; border: 1px solid #999; }
        .letterhead h1 { font-size: 14px; margin: 0 0 2px; text-align: center; }
        .letterhead .record-title { font-size: 7.4px; text-transform: uppercase; letter-spacing: 1px; color: #555; text-align: center; margin: 0; }
        .letterhead .record-subject { font-size: 9.5px; font-weight: bold; text-align: center; margin: 4px 0 0; }
        .letterhead .crest { width: 90px; }

        .section { margin-top: 6px; page-break-inside: avoid; }
        .section-title { font-size: 8.4px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #999; padding-bottom: 2px; margin-bottom: 3px; }
        .section-title .compact { font-weight: normal; text-transform: none; letter-spacing: 0; margin-left: 8px; }
        .grid td { padding: 1px 4px 3px 0; width: 25%; }
        .field-label { font-size: 6.2px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #555; }
        .field-value { font-size: 8px; color: #111; word-wrap: break-word; }
        .not-recorded { color: #777; font-style: italic; }

        .footer { border-top: 1px solid #999; margin-top: 8px; padding-top: 3px; font-size: 6.6px; color: #555; }
    </style>
</head>
<body>
    <div class="watermark">OFFICIAL DOCUMENT</div>

    @php($photoPath = \App\Models\Student::documentPath($student->student_passport_photo))
    <table class="letterhead">
        <tr>
            <td style="width: 22%;">
                <div class="label">Passport Size Photo</div>
                @if($photoPath && file_exists($photoPath))
                    <img src="{{ $photoPath }}" class="photo" alt="">
                @else
                    <img src="{{ public_path('images/noimage.jpg') }}" class="photo" alt="">
                @endif
            </td>
            <td style="width: 56%;">
                <h1>Success Laventille Secondary School<br>Eastern Main Road</h1>
                <p class="record-title">Official Student Record</p>
                <p class="record-subject">
                    {{ $student->student_name ? ucwords(strtolower($student->student_name)) : 'Unnamed student' }}
                    @if($student->current_class) &middot; Class {{ $student->current_class }} @elseif($student->form_1_class) &middot; Form {{ $student->form_1_class }} @endif
                    @if(!$student->isActive()) &middot; {{ $student->enrolment_status_label }} @endif
                </p>
            </td>
            <td style="width: 22%; text-align: right;">
                @if(file_exists(public_path('images/successlogo.png')))
                    <img src="{{ public_path('images/successlogo.png') }}" class="crest" alt="">
                @endif
            </td>
        </tr>
    </table>

    @foreach(\App\Support\ProfileLayout::sections($student) as $section)
        <div class="section">
            @if($section['compact'] !== null)
                <div class="section-title">
                    {{ $section['title'] }}
                    <span class="compact">
                        @if($section['compact'] !== '') {{ $section['compact'] }} @else <span class="not-recorded">Not recorded</span> @endif
                    </span>
                </div>
            @else
                <div class="section-title">{{ $section['title'] }}</div>
                <table class="grid">
                    @foreach($section['rows'] as $row)
                        <tr>
                            @php($used = 0)
                            @foreach($row as $field)
                                @php($display = \App\Support\ProfileLayout::display($field))
                                @php($used += $field['span'])
                                <td colspan="{{ $field['span'] }}" style="width: {{ $field['span'] * 25 }}%;">
                                    <div class="field-label">{{ $field['label'] }}</div>
                                    <div class="field-value">
                                        @if($display === null)
                                            <span class="not-recorded">Not recorded</span>
                                        @else
                                            {{ $display }}
                                        @endif
                                    </div>
                                </td>
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

    <table class="footer">
        <tr>
            <td style="width: 40%;">Success Laventille Secondary School &middot; Official Student Record</td>
            <td style="width: 35%; text-align: center;">{{ $student->student_name ? ucwords(strtolower($student->student_name)) : '' }}@if($student->student_birth_certificate_pin) &middot; PIN {{ $student->student_birth_certificate_pin }}@endif</td>
            <td style="width: 25%; text-align: right;">Printed {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>
</body>
</html>
