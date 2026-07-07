<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// —– Bootstrap your PDO connection (returns a $pdo)
$pdo = require 'connect.php';

// —– Helpers —–
/**
 * Return the raw field value, or a default if it's missing/empty.
 */
function field(string $key, $default = 'N/A'): string {
    return $_POST['fields'][$key]['value'] ?? $default;
}

/**
 * Concatenate multiple fields, skipping empties.
 * If none exist, return $default.
 */
function multi_field(array $keys, string $default = 'N/A', string $sep = ' '): string {
    $vals = [];
    foreach ($keys as $k) {
        if (!empty($_POST['fields'][$k]['value'])) {
            $vals[] = $_POST['fields'][$k]['value'];
        }
    }
    return $vals ? implode($sep, $vals) : $default;
}

// —– Prepare SQL —–
$sql = "
INSERT INTO student_registration_data (
  student_passport_photo, form_1_class, student_name, student_gender, citizen_type,
  student_current_address, student_dob, student_birth_certificate, student_birth_certficate_pin,
  student_religion, student_country_of_birth, student_nationality, student_ethnicity,
  student_contact, student_email, student_sea_date, student_primary_school,
  student_sea_slip, student_sea_number, student_transfer_status, student_transfer_slip,
  student_transfer_date, student_previous_secondary_school, student_previous_school_location,
  student_medical_condition, student_bloodtype, student_allergies, student_immunization_status,
  student_family_crisis, student_recieving_counselling, student_physical_disibilities,
  student_learning_disabilities, student_educational_aid, student_special_sea_concessions,
  student_emotional_factors, student_other_intervention_information,
  student_school_feeding_option, student_social_welfare_status, student_mode_of_transport,
  student_access_to_device, is_mother_active_or_deceased, mother_name,
  mother_identification_type, mother_identification_number, mother_home_address,
  mother_contact, mother_profession, mother_work_address, mother_email,
  is_father_active_or_deceased, father_name, father_identification_type,
  father_identification_number, father_home_address, father_contact,
  father_profession, father_work_address, father_email_address,
  emergency_contact_name, emergency_contact_address,
  emergency_contact_relation_to_student, emergency_contact_number,
  registration_date, registrant_relationship_to_student,
  registrant_name, registrant_identification_type, registrant_identification_number,
  registrant_nationality, registrant_email
)
VALUES (
  :student_passport_photo, :student_form, :student_name, :student_gender, :citizen_type,
  :student_current_address, :student_dob, :student_birth_certificate, :student_birth_certficate_pin,
  :student_religion, :student_country_of_birth, :student_nationality, :student_ethnicity,
  :student_contact, :student_email, :student_sea_date, :student_primary_school,
  :student_sea_slip, :student_sea_number, :student_transfer_status, :student_transfer_slip,
  :student_transfer_date, :student_previous_secondary_school, :student_previous_school_location,
  :student_medical_condition, :student_bloodtype, :student_allergies, :student_immunization_status,
  :student_family_crisis, :student_recieving_counselling, :student_physical_disibilities,
  :student_learning_disabilities, :student_educational_aid, :student_special_sea_concessions,
  :student_emotional_factors, :student_other_intervention_information,
  :student_school_feeding_option, :student_social_welfare_status, :student_mode_of_transport,
  :student_access_to_device, :is_mother_active_or_deceased, :mother_name,
  :mother_identification_type, :mother_identification_number, :mother_home_address,
  :mother_contact, :mother_profession, :mother_work_address, :mother_email,
  :is_father_active_or_deceased, :father_name, :father_identification_type,
  :father_identification_number, :father_home_address, :father_contact,
  :father_profession, :father_work_address, :father_email_address,
  :emergency_contact_name, :emergency_contact_address,
  :emergency_contact_relation_to_student, :emergency_contact_number,
  :registration_date, :registrant_relationship_to_student,
  :registrant_name, :registrant_identification_type, :registrant_identification_number,
  :registrant_nationality, :registrant_email
)
";

$stmt = $pdo->prepare($sql);

// —– Bind parameters with defaults and conditional logic —–
$params = [
  ':student_passport_photo'                => field('student_passport'),
  ':student_form'                          => field('student_class'),
  ':student_name'                          => multi_field(['student_first_name','student_last_name'], ''),
  ':student_gender'                        => field('student_gender'),
  ':citizen_type'                          => field('student_citizenship_type'),
  ':student_current_address'               => multi_field([
                                              'student_house_no','student_address_line1',
                                              'student_community','student_village',
                                              'student_city','student_corporartion'
                                            ], ''),
  ':student_dob'                           => field('student_dob','1900-01-01'),
  ':student_birth_certificate'             => field('student_birth_certificate'),
  ':student_birth_certficate_pin'          => field('student_birth_pin'),
  ':student_religion'                      => field('student_religion'),
  ':student_country_of_birth'              => field('student_country_of_birth'),
  ':student_nationality'                   => ( ($n = field('student_nationality','')) === 'Other'
                                              ? field('other_student_nationality')
                                              : $n ),
  ':student_ethnicity'                     => field('student_ethnicity'),
  ':student_contact'                       => field('student_contact_no'),
  ':student_email'                         => field('student_email'),
  ':student_sea_date'                      => field('student_sea_date','1900-01-01'),
  ':student_primary_school'                => field('student_primary_school'),
  ':student_sea_slip'                      => field('student_sea_slip'),
  ':student_sea_number'                    => field('student_sea_number'),
  ':student_transfer_status'               => field('transfer_status'),
  ':student_transfer_slip'                 => field('student_transfer_slip'),
  ':student_transfer_date'                 => field('student_transfer_year','1900-01-01'),
  ':student_previous_secondary_school'     => field('student_transfer_school'),
  ':student_previous_school_location'      => multi_field([
                                              'transfer_address_line1',
                                              'transfer_city','transfer_village'
                                            ], ''),
  ':student_medical_condition'             => field('student_medical_condition'),
  ':student_bloodtype'                     => field('student_blood_type'),
  ':student_allergies'                     => field('student_allergies'),
  ':student_immunization_status'           => field('student_immunisation_status'),
  ':student_family_crisis'                 => ( ($c = field('student_family_crisis','')) === 'Other'
                                              ? field('student_other_crisis')
                                              : $c ),
  ':student_recieving_counselling'         => (field('recieved_counselling') === 'Yes'
                                              ? field('counselling_explanation')
                                              : 'N/A'),
  ':student_physical_disibilities'         => (field('physical_disabilities') === 'Yes'
                                              ? field('stated_physical_disabilities')
                                              : 'N/A'),
  ':student_learning_disabilities'         => (field('learning_disabilities') === 'Yes'
                                              ? field('stated_learning_disabilities')
                                              : 'N/A'),
  ':student_educational_aid'               => field('educational_aid'),
  ':student_special_sea_concessions'       => field('special_concessions'),
  ':student_emotional_factors'             => (field('developmental_factors') === 'Yes'
                                              ? field('stated_developmental_factors')
                                              : 'N/A'),
  ':student_other_intervention_information'=> field('other_intervention_information'),
  ':student_school_feeding_option'         => field('student_school_feeding_option'),
  ':student_social_welfare_status'         => field('student_social_services'),
  ':student_mode_of_transport'             => field('student_transport_method'),
  ':student_access_to_device'              => field('student_continuos_access'),
  ':is_mother_active_or_deceased'          => (field('mother_living_status') === 'Deceased'
                                              ? 'Deceased'
                                              : 'Alive'),
  ':mother_name'                           => multi_field(['mother_first_name','mother_last_name'], ''),
  ':mother_identification_type'            => field('mother_identification'),
  ':mother_identification_number'          => field('mother_identification_number'),
  ':mother_home_address'                   => multi_field([
                                              'mother_house_no','mother_address_line1',
                                              'mother_community','mother_village',
                                              'mother_city','mother_corporartion'
                                            ], ''),
  ':mother_contact'                        => field('mother_contact'),
  ':mother_profession'                     => field('mother_profession'),
  ':mother_work_address'                   => multi_field([
                                              'mother_work_address_line1',
                                              'mother_work_city','mother_work_village'
                                            ], ''),
  ':mother_email'                          => field('mother_email'),
  ':is_father_active_or_deceased'          => (field('father_living_status') === 'Deceased'
                                              ? 'Deceased'
                                              : 'Alive'),
  ':father_name'                           => multi_field(['father_first_name','father_last_name'], ''),
  ':father_identification_type'            => field('father_identification_type'),
  ':father_identification_number'          => field('father_identification_no'),
  ':father_home_address'                   => multi_field([
                                              'father_house_no','father_address_line1',
                                              'father_community','father_village',
                                              'father_city','father_corporartion'
                                            ], ''),
  ':father_contact'                        => field('father_contact'),
  ':father_profession'                     => field('father_profession'),
  ':father_work_address'                   => multi_field([
                                              'father_work_address_line1',
                                              'father_work_city','father_work_village'
                                            ], ''),
  ':father_email_address'                  => field('father_email'),
  ':emergency_contact_name'                => multi_field(['emergency_first_name','emergency_last_name'], ''),
  ':emergency_contact_address'             => multi_field([
                                              'emergency_address_line1',
                                              'emergency_city','emergency_village'
                                            ], ''),
  ':emergency_contact_relation_to_student' => (field('emergency_relation') === 'Other'
                                              ? field('other_emergency_contact')
                                              : field('emergency_relation')),
  ':emergency_contact_number'              => field('emergency_contact'),
  ':registration_date'                     => date('Y-m-d'),
  ':registrant_relationship_to_student'    => (field('registrant_relationsip_to_student') === 'Other'
                                              ? field('registrant_other_relationship')
                                              : field('registrant_relationsip_to_student')),
  ':registrant_name'                       => multi_field(['registrant_first_name','registrant_last_name'], ''),
  ':registrant_identification_type'        => field('registrant_identification_type'),
  ':registrant_identification_number'      => field('registrant_identification_number'),
  ':registrant_nationality'                => (field('registrant_nationality') === 'Other'
                                              ? field('registrant_other_nationality')
                                              : field('registrant_nationality')),
  ':registrant_email'                      => field('registrant_email')
];

$stmt->execute($params);
