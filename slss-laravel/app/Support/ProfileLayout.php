<?php

namespace App\Support;

use App\Models\Student;

/**
 * The single definition of what a printed student record contains and in
 * what order. The HTML print view and the PDF both render from this, so the
 * two documents can never drift apart.
 *
 * A field is: label, value (already resolved from the student), format
 * ('name', 'date', 'document', 'yesno' or null) and span (1-4 of a 4-column
 * grid). A section may collapse to a single line ("compact") when it has
 * nothing worth printing in full.
 */
class ProfileLayout
{
    /**
     * @return array<int, array{title:string, compact:?string, rows:array<int, array<int, array{label:string, value:mixed, format:?string, span:int}>>}>
     */
    public static function sections(Student $s): array
    {
        $f = fn (string $label, $value, ?string $format = null, int $span = 1) =>
            ['label' => $label, 'value' => $value, 'format' => $format, 'span' => $span];

        $intake = trim((Student::canonicalClass($s->form_1_class) ?? (string) $s->form_1_class)
            . ($s->intake_year ? ' · ' . $s->intake_year : ''));

        $sections = [];

        $sections[] = ['title' => 'Student Personal Information', 'compact' => null, 'rows' => [
            [$f('Current Class', $s->current_class), $f('Student Name', $s->student_name, 'name'), $f('Gender', $s->student_gender), $f('Date of Birth', $s->student_dob, 'date')],
            [$f('Form 1 Class / Intake', $intake), $f('Citizenship Type', $s->citizen_type), $f('Birth Certificate Pin', $s->student_birth_certificate_pin), $f('Birth Certificate', $s->student_birth_certificate, 'document')],
            [$f('Religion', $s->student_religion), $f('Country of Birth', $s->student_country_of_birth), $f('Nationality', $s->student_nationality), $f('Enrolment Status', $s->enrolment_status_label . ($s->status_note ? ' · ' . $s->status_note : ''))],
            [$f('Ethnicity', $s->student_ethnicity), $f('Contact Number', $s->student_contact), $f('Email Address', $s->student_email, null, 2)],
            [$f('Current Address', $s->student_current_address, 'name', 4)],
        ]];

        $sections[] = ['title' => 'SEA Information', 'compact' => null, 'rows' => [
            [$f('SEA Exam Date', $s->student_sea_date, 'date'), $f('Primary School', $s->student_primary_school, 'name'), $f('SEA Number', $s->student_sea_number), $f('SEA Slip', $s->student_sea_slip, 'document')],
        ]];

        $hasTransferDetails = $s->student_transfer_slip || $s->student_transfer_reason || $s->student_transfer_date
            || $s->student_previous_form_class || $s->student_previous_secondary_school || $s->student_previous_school_location;
        $isTransfer = $s->student_transfer_status
            && !in_array(strtolower(trim((string) $s->student_transfer_status)), ['no', 'n/a', 'none'], true);

        if ($isTransfer || $hasTransferDetails) {
            $sections[] = ['title' => 'Transfer Information', 'compact' => null, 'rows' => [
                [$f('Transfer Status', $s->student_transfer_status), $f('Transfer Slip', $s->student_transfer_slip, 'document'), $f('Transfer Date', $s->student_transfer_date, 'date'), $f('Previous Form Class', $s->student_previous_form_class)],
                [$f('Previous Secondary School', $s->student_previous_secondary_school, 'name', 2), $f('Previous School Location', $s->student_previous_school_location, 'name', 2)],
                [$f('Transfer Reason', $s->student_transfer_reason, null, 4)],
            ]];
        } else {
            $sections[] = ['title' => 'Transfer Information', 'compact' => $s->student_transfer_status ? 'Not a transfer student' : '', 'rows' => []];
        }

        $sections[] = ['title' => 'Medical Information', 'compact' => null, 'rows' => [
            [$f('Blood Type', $s->student_bloodtype), $f('Immunization Status', $s->student_immunization_status), $f('Allergies', $s->student_allergies, null, 2)],
            [$f('Medical Conditions', $s->student_medical_condition, null, 4)],
        ]];

        $sections[] = ['title' => 'Special Needs & Intervention', 'compact' => null, 'rows' => [
            [$f('Family Crisis', $s->student_family_crisis, 'yesno'), $f('Receiving Counselling', $s->student_receiving_counselling, 'yesno'), $f('Educational Aid', $s->student_educational_aid, 'yesno')],
            [$f('Physical Disabilities', $s->student_physical_disabilities, 'yesno'), $f('Learning Disabilities', $s->student_learning_disabilities, 'yesno'), $f('Special SEA Concessions', $s->student_special_sea_concessions, 'yesno')],
            [$f('Emotional Factors', $s->student_emotional_factors, 'yesno', 2), $f('Other Intervention Information', $s->student_other_intervention_information, 'yesno', 2)],
        ]];

        $sections[] = ['title' => 'Personal Preferences', 'compact' => null, 'rows' => [
            [$f('School Feeding Option', $s->student_school_feeding_option), $f('Social Welfare Status', $s->student_social_welfare_status), $f('Mode of Transport', $s->student_mode_of_transport)],
            [$f('Social Welfare Details', $s->student_social_welfare_detail, null, 4)],
            [$f('Access to Device', $s->student_access_to_device), $f('Device Shared', $s->student_device_shared), $f('Reliable Internet', $s->student_reliable_internet)],
            [$f('Internet Provider', $s->student_internet_provider, null, 2), $f('Online Tools', $s->student_online_tools, null, 2)],
        ]];

        foreach ([['Mother', 'mother', 'is_mother_active_or_deceased', 'mother_email'], ['Father', 'father', 'is_father_active_or_deceased', 'father_email_address']] as [$who, $p, $statusAttr, $emailAttr]) {
            $sections[] = ['title' => "Parent/Guardian Information ({$who})", 'compact' => null, 'rows' => [
                [$f("{$who}'s Name", $s->{"{$p}_name"}, 'name'), $f('Status', $s->{$statusAttr}), $f('Death Certificate', $s->{"{$p}_death_certificate"}, 'document')],
                [$f('Identification Type', $s->{"{$p}_identification_type"}), $f('Identification Number', $s->{"{$p}_identification_number"})],
                [$f('Home Address', $s->{"{$p}_home_address"}, 'name', 4)],
                [$f('Contact Number', $s->{"{$p}_contact"}), $f('Profession', $s->{"{$p}_profession"}, 'name'), $f('Email Address', $s->{$emailAttr})],
                [$f('Work Address', $s->{"{$p}_work_address"}, 'name', 4)],
            ]];
        }

        $sections[] = ['title' => 'Emergency Contact Information', 'compact' => null, 'rows' => [
            [$f('Contact Name', $s->emergency_contact_name, 'name'), $f('Relation to Student', $s->emergency_contact_relation_to_student, 'name'), $f('Contact Number', $s->emergency_contact_number)],
            [$f('Address', $s->emergency_contact_address, 'name', 4)],
        ]];

        $sections[] = ['title' => 'Registrant Information', 'compact' => null, 'rows' => [
            [$f('Registration Date', $s->registration_date, 'date'), $f('Relationship to Student', $s->registrant_relationship_to_student), $f('Registrant Name', $s->registrant_name, 'name')],
            [$f('Identification Type', $s->registrant_identification_type), $f('Identification Number', $s->registrant_identification_number), $f('Nationality', $s->registrant_nationality)],
            [$f('Email Address', $s->registrant_email, null, 4)],
        ]];

        return $sections;
    }

    /**
     * Text to print for a field: the formatted value, "On file" for a stored
     * document, or null when nothing is recorded.
     */
    public static function display(array $field): ?string
    {
        if ($field['format'] === 'document') {
            return Student::documentLabel($field['value']);
        }

        return Student::printValue($field['value'], $field['format']);
    }
}
