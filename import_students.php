<?php
ini_set('memory_limit', '512M');

$pdo = require_once 'connect.php';               // connect.php should return a PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$csvPath = __DIR__ . '/student20252.csv';

// Map CSV headers -> DB placeholders
$map = [
  'student_passport_photo'=>'student_passport_photo',
  'form_1_class'=>'student_form',
  'student_name'=>'student_name',
  'student_gender'=>'student_gender',
  'citizen_type'=>'citizen_type',
  'student_current_address'=>'student_current_address',
  'student_dob'=>'student_dob',
  'student_birth_certificate'=>'student_birth_certificate',
  'student_birth_certficate_pin'=>'student_birth_certficate_pin',
  'student_religion'=>'student_religion',
  'student_country_of_birth'=>'student_country_of_birth',
  'student_nationality'=>'student_nationality',
  'student_ethnicity'=>'student_ethnicity',
  'student_contact'=>'student_contact',
  'student_email'=>'student_email',
  'student_sea_date'=>'student_sea_date',
  'student_primary_school'=>'student_primary_school',
  'student_sea_slip'=>'student_sea_slip',
  'student_sea_number'=>'student_sea_number',
  'student_transfer_status'=>'student_transfer_status',
  'student_transfer_slip'=>'student_transfer_slip',
  'student_transfer_date'=>'student_transfer_date',
  'student_previous_secondary_school'=>'student_previous_secondary_school',
  'student_previous_school_location'=>'student_previous_school_location',
  'student_medical_condition'=>'student_medical_condition',
  'student_bloodtype'=>'student_bloodtype',
  'student_allergies'=>'student_allergies',
  'student_immunization_status'=>'student_immunization_status',
  'student_family_crisis'=>'student_family_crisis',
  'student_recieving_counselling'=>'student_recieving_counselling',
  'student_physical_disibilities'=>'student_physical_disibilities',
  'student_learning_disabilities'=>'student_learning_disabilities',
  'student_educational_aid'=>'student_educational_aid',
  'student_special_sea_concessions'=>'student_special_sea_concessions',
  'student_emotional_factors'=>'student_emotional_factors',
  'student_other_intervention_information'=>'student_other_intervention_information',
  'student_school_feeding_option'=>'student_school_feeding_option',
  'student_social_welfare_status'=>'student_social_welfare_status',
  'student_mode_of_transport'=>'student_mode_of_transport',
  'student_access_to_device'=>'student_access_to_device',
  'is_mother_active_or_deceased'=>'is_mother_active_or_deceased',
  'mother_name'=>'mother_name',
  'mother_identification_type'=>'mother_identification_type',
  'mother_identification_number'=>'mother_identification_number',
  'mother_home_address'=>'mother_home_address',
  'mother_contact'=>'mother_contact',
  'mother_profession'=>'mother_profession',
  'mother_work_address'=>'mother_work_address',
  'mother_email'=>'mother_email',
  'is_father_active_or_deceased'=>'is_father_active_or_deceased',
  'father_name'=>'father_name',
  'father_identification_type'=>'father_identification_type',
  'father_identification_number'=>'father_identification_number',
  'father_home_address'=>'father_home_address',
  'father_contact'=>'father_contact',
  'father_profession'=>'father_profession',
  'father_work_address'=>'father_work_address',
  'father_email_address'=>'father_email_address',
  'emergency_contact_name'=>'emergency_contact_name',
  'emergency_contact_address'=>'emergency_contact_address',
  'emergency_contact_relation_to_student'=>'emergency_contact_relation_to_student',
  'emergency_contact_number'=>'emergency_contact_number',
  'registration_date'=>'registration_date',
  'registrant_relationship_to_student'=>'registrant_relationship_to_student',
  'registrant_name'=>'registrant_name',
  'registrant_identification_type'=>'registrant_identification_type',
  'registrant_identification_number'=>'registrant_identification_number',
  'registrant_nationality'=>'registrant_nationality',
  'registrant_email'=>'registrant_email',
];

$sql = "
INSERT IGNORE INTO student_registration_data (
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
) VALUES (
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
)";

try {
  if (!file_exists($csvPath)) {
    throw new RuntimeException("CSV not found at: $csvPath");
  }

  $stmt = $pdo->prepare($sql);

  $fh = fopen($csvPath, 'r');
  if (!$fh) throw new RuntimeException("Cannot open CSV: $csvPath");

  // BOM-safe header read
  $first = fgets($fh);
  if ($first === false) throw new RuntimeException('CSV is empty.');
  $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
  $headers = array_map('trim', str_getcsv($first));

  $pdo->beginTransaction();

  $rowNum = 1; $inserted = 0; $skipped = 0;

  while (($row = fgetcsv($fh)) !== false) {
    $rowNum++;

    // init params
    $params = array_fill_keys(array_values($map), null);

    // map CSV -> params
    foreach ($headers as $i => $h) {
      if (!isset($row[$i])) continue;
      if (isset($map[$h])) {
        $val = trim($row[$i]);
        $val = str_replace('\/', '/', $val);            // fix \/ issue
        $params[$map[$h]] = ($val === '') ? null : $val;
      }
    }

    // normalize PIN
    if (!empty($params['student_birth_certficate_pin'])) {
      $pin = strtoupper(preg_replace('/[^0-9A-Z]/', '', $params['student_birth_certficate_pin']));
      $params['student_birth_certficate_pin'] = $pin ?: null;
    } else {
      $params['student_birth_certficate_pin'] = null;
    }

    // execute (keys match placeholders without colons)
    $stmt->execute($params);

    $inserted += $stmt->rowCount() > 0 ? 1 : 0;
    if ($stmt->rowCount() === 0) $skipped++;
  }

  $pdo->commit();
  fclose($fh);

  echo "Done. Inserted: $inserted, Skipped (duplicates/invalid): $skipped\n";

} catch (Throwable $e) {
  if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
  die("Failed: " . $e->getMessage() . "\n");
}
 