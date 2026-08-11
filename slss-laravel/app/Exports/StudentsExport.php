<?php

namespace App\Exports;

use App\Models\Student;
use App\Services\StudentService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    /**
     * Columns included in the export, mapped to their spreadsheet heading.
     *
     * Keys are Student attributes; values are the column headings.
     *
     * @var array<string, string>
     */
    public const COLUMNS = [
        'id' => 'ID',
        'student_name' => 'Student Name',
        'form_1_class' => 'Form Class',
        'student_gender' => 'Gender',
        'student_dob' => 'Date of Birth',
        'citizen_type' => 'Citizenship Type',
        'student_birth_certificate_pin' => 'Birth Certificate PIN',
        'student_religion' => 'Religion',
        'student_country_of_birth' => 'Country of Birth',
        'student_nationality' => 'Nationality',
        'student_ethnicity' => 'Ethnicity',
        'student_contact' => 'Contact Number',
        'student_email' => 'Email',
        'student_current_address' => 'Current Address',

        'student_sea_date' => 'SEA Date',
        'student_primary_school' => 'Primary School',
        'student_sea_number' => 'SEA Number',

        'student_transfer_status' => 'Transfer Status',
        'student_transfer_date' => 'Transfer Date',
        'student_previous_form_class' => 'Previous Form Class',
        'student_previous_secondary_school' => 'Previous School',
        'student_previous_school_location' => 'Previous School Location',
        'student_transfer_reason' => 'Transfer Reason',

        'student_medical_condition' => 'Medical Condition',
        'student_bloodtype' => 'Blood Type',
        'student_allergies' => 'Allergies',
        'student_immunization_status' => 'Immunization Status',

        'student_family_crisis' => 'Family Crisis',
        'student_receiving_counselling' => 'Receiving Counselling',
        'student_physical_disabilities' => 'Physical Disabilities',
        'student_learning_disabilities' => 'Learning Disabilities',
        'student_educational_aid' => 'Educational Aid',
        'student_special_sea_concessions' => 'Special SEA Concessions',
        'student_emotional_factors' => 'Emotional/Developmental Factors',
        'student_other_intervention_information' => 'Other Intervention Information',

        'student_school_feeding_option' => 'School Feeding Programme',
        'student_social_welfare_status' => 'Social Welfare Status',
        'student_social_welfare_detail' => 'Social Welfare Detail',
        'student_mode_of_transport' => 'Mode of Transport',
        'student_access_to_device' => 'Access to Device',
        'student_device_shared' => 'Device Shared',
        'student_reliable_internet' => 'Reliable Internet',
        'student_internet_provider' => 'Internet Provider',
        'student_online_tools' => 'Online Tools',

        'mother_name' => "Mother's Name",
        'is_mother_active_or_deceased' => "Mother's Living Status",
        'mother_identification_type' => "Mother's ID Type",
        'mother_identification_number' => "Mother's ID Number",
        'mother_contact' => "Mother's Contact",
        'mother_email' => "Mother's Email",
        'mother_home_address' => "Mother's Home Address",
        'mother_profession' => "Mother's Profession",
        'mother_work_address' => "Mother's Work Address",

        'father_name' => "Father's Name",
        'is_father_active_or_deceased' => "Father's Living Status",
        'father_identification_type' => "Father's ID Type",
        'father_identification_number' => "Father's ID Number",
        'father_contact' => "Father's Contact",
        'father_email_address' => "Father's Email",
        'father_home_address' => "Father's Home Address",
        'father_profession' => "Father's Profession",
        'father_work_address' => "Father's Work Address",

        'emergency_contact_name' => 'Emergency Contact Name',
        'emergency_contact_relation_to_student' => 'Emergency Contact Relationship',
        'emergency_contact_number' => 'Emergency Contact Number',
        'emergency_contact_address' => 'Emergency Contact Address',

        'registration_date' => 'Registration Date',
        'registrant_name' => 'Registrant Name',
        'registrant_relationship_to_student' => 'Registrant Relationship',
        'registrant_identification_type' => 'Registrant ID Type',
        'registrant_identification_number' => 'Registrant ID Number',
        'registrant_nationality' => 'Registrant Nationality',
        'registrant_email' => 'Registrant Email',
    ];

    /**
     * Attributes cast to dates that need formatting for display.
     *
     * @var array<int, string>
     */
    protected const DATE_COLUMNS = [
        'student_dob',
        'student_sea_date',
        'student_transfer_date',
        'registration_date',
    ];

    public function __construct(
        protected array $filters = [],
        protected ?StudentService $studentService = null
    ) {
        $this->studentService = $studentService ?? new StudentService();
    }

    /**
     * Build the query for the export, honouring the same filters the
     * student listing uses so an exported report matches what the user sees.
     */
    public function query()
    {
        return $this->studentService->buildFilteredQuery($this->filters)
            ->select(array_merge(['id'], array_keys(self::COLUMNS)))
            ->orderBy('student_name');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return array_values(self::COLUMNS);
    }

    /**
     * @param  \App\Models\Student  $student
     * @return array<int, mixed>
     */
    public function map($student): array
    {
        $row = [];

        foreach (array_keys(self::COLUMNS) as $attribute) {
            $value = $student->{$attribute};

            if (in_array($attribute, self::DATE_COLUMNS, true) && $value) {
                $value = $value->format('d/m/Y');
            }

            $row[] = $value;
        }

        return $row;
    }

    public function title(): string
    {
        return 'All Students';
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '1E293B'],
                ],
            ],
        ];
    }
}
