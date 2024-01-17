<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$pdo = require_once 'connect.php';

$sql="INSERT INTO student_registration_data (student_passport_photo, form_1_class, student_name, student_gender, student_current_address, student_dob, student_birth_certificate, student_birth_certficate_pin,
student_religion, student_country_of_birth, student_nationality, student_ethnicity, student_contact, student_email, student_sea_date, student_primary_school, student_sea_slip, student_sea_number, student_medical_condition, student_bloodtype,
student_allergies, student_immunization_status, student_school_feeding_option, student_social_welfare_status, student_mode_of_transport, student_access_to_device, is_mother_active_or_deceased, mother_name, mother_identification_type,
mother_identification_number, mother_home_address, mother_contact, mother_profession, mother_work_address, mother_email, is_father_active_or_deceased,
father_name, father_identification_type, father_identification_number, father_home_address, father_contact, father_profession, father_work_address,
father_email_address, emergency_contact_name, emergency_contact_address, emergency_contact_relation_to_student, emergency_contact_number, registration_date,
registrant_relationship_to_student, registrant_name, registrant_identification_type, registrant_identification_number, registrant_nationality, registrant_email) 
VALUES (:student_passport_photo, :student_form, :student_name, :student_gender, :student_current_address, :student_dob, :student_birth_certificate, :student_birth_certficate_pin,
:student_religion, :student_country_of_birth, :student_nationality, :student_ethnicity, :student_contact, :student_email, :student_sea_date, :student_primary_school, :student_sea_slip, :student_sea_number, :student_medical_condition, :student_bloodtype,
:student_allergies, :student_immunization_status, :student_school_feeding_option, :student_social_welfare_status, :student_mode_of_transport, :student_access_to_device, :is_mother_active_or_deceased, :mother_name, :mother_identification_type,
:mother_identification_number, :mother_home_address, :mother_contact, :mother_profession, :mother_work_address, :mother_email, :is_father_active_or_deceased,
:father_name, :father_identification_type, :father_identification_number, :father_home_address, :father_contact, :father_profession, :father_work_address,
:father_email_address, :emergency_contact_name, :emergency_contact_address, :emergency_contact_relation_to_student, :emergency_contact_number, :registration_date,
:registrant_relationship_to_student, :registrant_name, :registrant_identification_type, :registrant_identification_number, :registrant_nationality, :registrant_email)";

$stmt=$pdo->prepare($sql);

$stmt->execute([
  ":student_passport_photo" => $_POST['fields']['student_passport']['value'],
  ":student_form" => $_POST['fields']['student_class']['value'],
  ":student_name" => $_POST['fields']['student_first_name']['value'] . " " . $_POST['fields']['student_last_name']['value'],
  ":student_gender" => $_POST['fields']['student_gender']['value'],
  ":student_current_address" => $_POST['fields']['student_address_line1']['value'] . " " . $_POST['fields']['student_city']['value'] . " " . $_POST['fields']['student_village']['value'] ,
  ":student_dob" => $_POST['fields']['student_dob']['value'], 
  ":student_birth_certificate" => $_POST['fields']['student_birth_certificate']['value'],
  ":student_birth_certficate_pin" => $_POST['fields']['student_birth_pin']['value'],
  ":student_religion" => $_POST['fields']['student_religion']['value'],
  ":student_country_of_birth" => $_POST['fields']['student_country_of_birth']['value'],
  ":student_nationality" => $_POST['fields']['student_nationality']['value'],
  ":student_ethnicity" => $_POST['fields']['student_ethnicity']['value'],
  ":student_contact" => $_POST['fields']['student_contact_no']['value'],
  ":student_email" => $_POST['fields']['student_email']['value'],
  ":student_sea_date" => $_POST['fields']['student_sea_date']['value'],
  ":student_primary_school" => $_POST['fields']['student_primary_school']['value'],
  ":student_sea_slip" => $_POST['fields']['student_sea_slip']['value'],
  ":student_sea_number" => $_POST['fields']['student_sea_number']['value'],
  ":student_medical_condition" => $_POST['fields']['student_medical_condition']['value'],
  ":student_bloodtype" => $_POST['fields']['student_blood_type']['value'],
  ":student_allergies" => $_POST['fields']['student_allergies']['value'],
  ":student_immunization_status" => $_POST['fields']['student_immunisation_status']['value'],
  ":student_school_feeding_option" => $_POST['fields']['student_school_feeding_option']['value'],
  ":student_social_welfare_status" => $_POST['fields']['student_social_services']['value'],
  ":student_mode_of_transport" => $_POST['fields']['student_transport_method']['value'],
  ":student_access_to_device" => $_POST['fields']['student_continuos_access']['value'],
  ":is_mother_active_or_deceased" => $_POST['fields']['mother_status']['value'],
  ":mother_name" => $_POST['fields']['mother_first_name']['value'] . " " . $_POST['fields']['mother_last_name']['value'],
  ":mother_identification_type" => $_POST['fields']['mother_identification']['value'],
  ":mother_identification_number" => $_POST['fields']['mother_identification_number']['value'],
  ":mother_home_address" => $_POST['fields']['mother_address_line1']['value'] . " " . $_POST['fields']['mother_city']['value'] . " " . $_POST['fields']['mother_village']['value'],
  ":mother_contact" => $_POST['fields']['mother_contact']['value'],
  ":mother_profession" => $_POST['fields']['mother_profession']['value'],
  ":mother_work_address" => $_POST['fields']['mother_work_address_line1']['value'] . " " . $_POST['fields']['mother_work_city']['value'] . " " . $_POST['fields']['mother_work_village']['value'],
  ":mother_email" => $_POST['fields']['mother_email']['value'],
  ":is_father_active_or_deceased" => $_POST['fields']['father_status']['value'],
  ":father_name" => $_POST['fields']['father_first_name']['value'] . " " . $_POST['fields']['father_last_name']['value'],
  ":father_identification_type" => $_POST['fields']['father_identification_type']['value'],
  ":father_identification_number" => $_POST['fields']['father_identification_no']['value'],
  ":father_home_address" => $_POST['fields']['father_address_line1']['value'] . " " . $_POST['fields']['father_city']['value'] . " " . $_POST['fields']['father_village']['value'],
  ":father_contact" => $_POST['fields']['father_contact']['value'],
  ":father_profession" => $_POST['fields']['father_profession']['value'],
  ":father_work_address" => $_POST['fields']['father_work_address_line1']['value'] . " " . $_POST['fields']['father_work_city']['value'] . " " . $_POST['fields']['father_work_village']['value'],
  ":father_email_address" => $_POST['fields']['father_email']['value'],
  ":emergency_contact_name" => $_POST['fields']['emergency_first_name']['value'] . " " . $_POST['fields']['emergency_last_name']['value'],
  ":emergency_contact_address" => $_POST['fields']['emergency_address_line1']['value'] . " " . $_POST['fields']['emergency_city']['value'] . " " . $_POST['fields']['emergency_village']['value'],
  ":emergency_contact_relation_to_student" => $_POST['fields']['emergency_relation']['value'],
  ":emergency_contact_number" => $_POST['fields']['emergency_contact']['value'],
  ":registration_date" => $_POST['fields']['registrant_date']['value'],
  ":registrant_relationship_to_student" => $_POST['fields']['registrant_relationsip_to_student']['value'],
  ":registrant_name" => $_POST['fields']['registrant_first_name']['value'] . " " . $_POST['fields']['registrant_last_name']['value'], 
  ":registrant_identification_type" => $_POST['fields']['registrant_identification_type']['value'], 
  ":registrant_identification_number" => $_POST['fields']['registrant_identification_number']['value'], 
  ":registrant_nationality" => $_POST['fields']['registrant_nationality']['value'], 
  ":registrant_email" => $_POST['fields']['registrant_email']['value']
]);

$conn = null;
?>