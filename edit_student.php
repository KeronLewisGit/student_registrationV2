<?php
// edit_student.php — view/update a student row in student_registration_data
declare(strict_types=1);
require __DIR__ . '/auth_bootstrap.php';
requireLogin($auth);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/* ---------- Safe session bootstrap (works on cPanel) ---------- */
$sessionOK = false;
$sessionDir = __DIR__ . '/storage/sessions';
if (!is_dir($sessionDir)) @mkdir($sessionDir, 0775, true);

$curSavePath = ini_get('session.save_path');
if (!$curSavePath || !is_dir($curSavePath)) {
  session_save_path($sessionDir);
}

try {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
  }
  $sessionOK = (session_status() === PHP_SESSION_ACTIVE);
} catch (\Throwable $e) {
  $sessionOK = false;
}

/* ---------- DB ---------- */
$pdo = require __DIR__ . '/connect.php';

$DB          = "`gkblvzmy_student-portal`";
$TABLE       = "$DB.`student_registration_data`";
$PK_COL      = "id";

$UPLOAD_DIR  = __DIR__ . "/uploads/passports";
$UPLOAD_URL  = "uploads/passports";

/*
 * Map safe form names => REAL DB columns (from schema).
 */
$FIELDS = [
  'form_1_class'                  => 'form_1_class',
  'student_name'                  => 'student_name',
  'student_gender'                => 'student_gender',
  'student_current_address'       => 'student_current_address',
  // 'residential_address' is shown in the form but NOT stored.
  'student_dob'                   => 'student_dob',
  'student_birth_certficate_pin'  => 'student_birth_certficate_pin',
  'student_religion'              => 'student_religion',
  'student_contact'               => 'student_contact',
  'student_country_of_birth'      => 'student_country_of_birth',
  'student_nationality'           => 'student_nationality',
  'student_ethnicity'             => 'student_ethnicity',
  'student_email'                 => 'student_email',
  'student_sea_date'              => 'student_sea_date',
  'student_primary_school'        => 'student_primary_school',
  'student_sea_number'            => 'student_sea_number',
  'student_medical_condition'     => 'student_medical_condition',
  'student_bloodtype'             => 'student_bloodtype',
  'student_allergies'             => 'student_allergies',
  'student_school_feeding_option' => 'student_school_feeding_option',
  'student_social_welfare_status' => 'student_social_welfare_status',
  'student_mode_of_transport'     => 'student_mode_of_transport',
  'student_immunization_status'   => 'student_immunization_status',
  'student_access_to_device'      => 'student_access_to_device',

  'mother_name'                   => 'mother_name',
  'is_mother_active_or_deceased'  => 'is_mother_active_or_deceased',
  'mother_identification_type'    => 'mother_identification_type',
  'mother_identification_number'  => 'mother_identification_number',
  'mother_home_address'           => 'mother_home_address',
  'mother_contact'                => 'mother_contact',
  'mother_profession'             => 'mother_profession',
  'mother_work_address'           => 'mother_work_address',
  'mother_email_address'          => 'mother_email',

  'father_name'                   => 'father_name',
  'is_father_active_or_deceased'  => 'is_father_active_or_deceased',
  'father_identification_type'    => 'father_identification_type',
  'father_identification_number'  => 'father_identification_number',
  'father_home_address'           => 'father_home_address',
  'father_contact'                => 'father_contact',
  'father_profession'             => 'father_profession',
  'father_work_address'           => 'father_work_address',
  'father_email_address'          => 'father_email_address',

  'emergency_contact_name'                => 'emergency_contact_name',
  'emergency_contact_relation_to_student' => 'emergency_contact_relation_to_student',
  'emergency_contact_number'              => 'emergency_contact_number',
  'emergency_contact_address'             => 'emergency_contact_address',

  'registrant_relationship_to_student' => 'registrant_relationship_to_student',
  'registrant_identification_type'     => 'registrant_identification_type',
  'registrant_identification_number'   => 'registrant_identification_number',
  'name_of_registrant'                 => 'registrant_name',
  'registration_date'                  => 'registration_date',

  'student_passport_photo'             => 'student_passport_photo', // file handled separately
];

$DATE_FIELDS = ['student_dob','student_sea_date','registration_date'];

/* ---------- Load record ---------- */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  echo "<p style='font:14px/1.4 sans-serif'>Missing or invalid <code>?id</code>. Example: <code>edit_student.php?id=123</code></p>";
  exit;
}
$stmt = $pdo->prepare("SELECT * FROM $TABLE WHERE `$PK_COL` = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  echo "<p style='font:14px/1.4 sans-serif'>Student not found (id ".(int)$id.").</p>";
  exit;
}

/* ---------- CSRF ---------- */
if ($sessionOK && empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $sessionOK ? $_SESSION['csrf'] : hash_hmac('sha256', 'edit-student|'.$id, __FILE__);

$errors = [];
$okMsg  = "";

/* ---------- Save ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $posted = $_POST['csrf'] ?? '';
  $valid  = $sessionOK ? hash_equals($_SESSION['csrf'] ?? '', $posted)
                       : hash_equals(hash_hmac('sha256', 'edit-student|'.$id, __FILE__), $posted);

  if (!$valid) {
    $errors[] = "Invalid security token. Reload the page and try again.";
  } else {
    // Gather posted fields we store
    $data = [];
    foreach ($FIELDS as $form => $col) {
      if ($form === 'student_passport_photo') continue; // file handled below
      if (!array_key_exists($form, $_POST)) continue;
      $val = trim((string)$_POST[$form]);

      if ($val !== '' && in_array($form, $DATE_FIELDS, true)) {
        $ts = strtotime($val);
        $val = $ts ? date('Y-m-d', $ts) : null;
      } elseif ($val === '') {
        $val = null;
      }
      $data[$form] = $val;
    }

    // Optional image upload
    if (!is_dir($UPLOAD_DIR)) @mkdir($UPLOAD_DIR, 0775, true);
    if (!empty($_FILES['student_passport_photo']['name']) && is_uploaded_file($_FILES['student_passport_photo']['tmp_name'])) {
      $ext = strtolower(pathinfo($_FILES['student_passport_photo']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
        $errors[] = "Passport photo must be an image (jpg, png, gif, webp).";
      } else {
        $fileName = "student_{$id}_" . time() . "." . $ext;
        $destPath = $UPLOAD_DIR . "/" . $fileName;
        if (!move_uploaded_file($_FILES['student_passport_photo']['tmp_name'], $destPath)) {
          $errors[] = "Failed to save uploaded file.";
        } else {
          $data['student_passport_photo'] = $UPLOAD_URL . "/" . $fileName;
        }
      }
    }

    if (!$errors) {
      // Quote identifiers safely
      $bq = static function (string $idn): string {
        return '`' . str_replace('`', '``', $idn) . '`';
      };

      // Build deterministic placeholders
      $sets   = [];
      $params = [];
      $i = 0;
      foreach ($data as $form => $val) {
        $col = $FIELDS[$form];
        $ph  = ':p' . (++$i);
        $sets[] = $bq($col) . " = $ph";
        $params[$ph] = $val;
      }

      if ($sets) {
        $sql = "UPDATE $TABLE SET " . implode(', ', $sets) . " WHERE " . $bq($PK_COL) . " = :id";
        $params[':id'] = $id;

        $upd = $pdo->prepare($sql);
        $upd->execute($params);

        // Reload minimal fields for redirect
        $stmt = $pdo->prepare("SELECT student_name, form_1_class, registration_date FROM $TABLE WHERE " . $bq($PK_COL) . " = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Build filters for records_new.php
        $year = null;
        if (!empty($row['registration_date'])) {
          $ts = strtotime($row['registration_date']);
          if ($ts) $year = (int)date('Y', $ts);
        }

        // Normalize class to 1A..1F if needed
        $clsRaw = trim((string)($row['form_1_class'] ?? ''));
        if (preg_match('/^1?[A-Za-z]$/', $clsRaw)) {
          $studentClass = (str_starts_with($clsRaw, '1') ? strtoupper($clsRaw) : ('1' . strtoupper($clsRaw)));
        } else {
          $studentClass = $clsRaw;
        }

        $query = array_filter([
          'year'          => $year,
          'student_class' => $studentClass,
          'student_name'  => trim((string)($row['student_name'] ?? '')),
        ], static fn($v) => $v !== null && $v !== '');

        header('Location: records_new.php' . ($query ? ('?' . http_build_query($query)) : ''), true, 303);
        exit;
      } else {
        $okMsg = "Nothing to update.";
      }
    }
  }
}

/* ---------- Helpers for view ---------- */
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function v(array $row, string $col): string { return isset($row[$col]) ? (string)$row[$col] : ''; }
function inputValue(array $row, string $form, array $FIELDS): string {
  if (!isset($FIELDS[$form])) return '';
  return h(v($row, $FIELDS[$form]));
}

$genders   = ['', 'Male', 'Female', 'Other'];
$yesNo     = ['', 'Yes', 'No'];
$aliveDec  = ['', 'Alive', 'Deceased'];
$classes   = ['', 'A','B','C','D','E','F'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Student #<?= (int)$id ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="successlogo.png">
<style>
  :root{
    --bg:#0f172a; --bg2:#111827; --card:#ffffff; --text:#0f172a;
    --muted:#6b7280; --line:#e5e7eb; --primary:#0b5fff; --primary-600:#0a53e8; --danger:#ef4444;
  }
  *{ box-sizing:border-box; font-family:"Lato",system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
  html,body{ height:100%; }
  body{
    margin:0; color:var(--text);
    background:
      radial-gradient(1200px 600px at 10% 10%, #1f2937 0%, transparent 60%),
      radial-gradient(1200px 600px at 90% 90%, #0b5fff22 0%, transparent 60%),
      linear-gradient(160deg,var(--bg),var(--bg2));
  }

  /* App bar */
  .appbar{
    width:100%; max-width:1200px; margin:24px auto 16px;
    background:var(--card); border:1px solid var(--line); border-radius:16px; padding:12px 16px;
    display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 20px 60px rgba(0,0,0,.20);
  }
  .brand{ display:flex; align-items:center; gap:12px; }
  .brand img{ width:40px; height:40px; object-fit:contain; }
  .brand h1{ font-size:18px; margin:0; line-height:1.2; }
  .brand .sub{ font-size:13px; color:var(--muted); margin:0; }
  .appbar-actions{ display:flex; gap:8px; }

  .btn{
    appearance:none; border:0; background:var(--primary); color:#fff; border-radius:12px;
    padding:10px 14px; font-weight:700; cursor:pointer; transition:background .15s, transform .02s;
  }
  .btn:hover{ background:var(--primary-600); }
  .btn:active{ transform:translateY(1px); }
  .btn.secondary{ background:#111; }
  .btn.ghost{ background:#fff; color:var(--text); border:1px solid var(--line); }
  .btn.danger{ background:var(--danger); }

  /* Card-ish editor container */
  .editor{
    width:100%; max-width:1200px; margin:0 auto 28px; background:var(--card);
    border:1px solid var(--line); border-radius:16px; box-shadow:0 12px 32px rgba(0,0,0,.12);
    padding:20px;
  }
  .bar{
    display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:8px;
  }
  .title{ display:flex; align-items:center; gap:10px; }
  .title h2{ margin:0; font-size:20px; }
  .chip{ color:#374151; font-size:12px; background:#f3f4f6; border:1px solid var(--line); padding:4px 8px; border-radius:999px; }

  .msg{ padding:10px 12px; border-radius:10px; margin:12px 0; font-size:14px; }
  .msg.ok{ background:#ecfdf5; border:1px solid #10b981; color:#065f46; }
  .msg.err{ background:#fff1f2; border:1px solid #f43f5e; color:#9f1239; }

  form{ }
  fieldset{
    border:1px solid var(--line); border-radius:14px; margin:18px 0; padding:14px; background:#fff;
  }
  legend{ padding:0 8px; font-weight:800; color:#111; font-size:14px; }
  .grid{ display:grid; grid-template-columns: repeat(12, 1fr); gap:12px; }
  .col-2{ grid-column: span 2; } .col-3{ grid-column: span 3; } .col-4{ grid-column: span 4; }
  .col-6{ grid-column: span 6; } .col-8{ grid-column: span 8; } .col-12{ grid-column: span 12; }

  label{ font-size:12px; font-weight:700; color:#111; display:block; margin-bottom:6px; }
  input[type=text], input[type=date], input[type=email], select, textarea, input[type=file]{
    width:100%; border:1px solid var(--line); border-radius:10px; padding:11px 12px; background:#fff; outline:none;
    transition:border-color .15s, box-shadow .15s;
  }
  textarea{ min-height:72px; resize:vertical; }
  input:focus, select:focus, textarea:focus{
    border-color:var(--primary); box-shadow:0 0 0 3px #0b5fff22;
  }

  .thumb{ width:140px; height:140px; object-fit:cover; border:1px solid var(--line); border-radius:12px; background:#f3f4f6; }
  .muted{ color:var(--muted); font-size:12px; }

  .actions{
    position:sticky; bottom:-1px; background:#fff; padding:14px 0 0; margin-top:8px;
    display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--line);
  }

  /* Responsive */
  @media (max-width: 992px){
    .editor{ margin:0 16px 28px; }
  }
  @media (max-width: 768px){
    .grid{ grid-template-columns: repeat(6, 1fr); }
    .col-8{ grid-column: span 6; } .col-6{ grid-column: span 6; } .col-4{ grid-column: span 6; }
    .col-3{ grid-column: span 3; } .col-2{ grid-column: span 3; }
    .appbar{ margin:16px; }
  }
  @media (max-width: 480px){
    .grid{ grid-template-columns: repeat(4, 1fr); }
    .col-8,.col-6,.col-4,.col-3,.col-2{ grid-column: span 4; }
  }
</style>
</head>
<body>

  <!-- App Bar -->
  <header class="appbar" role="banner" aria-label="Editor toolbar">
    <div class="brand">
      <img src="successlogo.png" alt="">
      <div>
        <h1>Success Student Management System</h1>
        <p class="sub">Edit Student</p>
      </div>
    </div>
    <div class="appbar-actions">
      <a class="btn ghost" href="javascript:history.back()">Back</a>
    </div>
  </header>

  <main class="editor" role="main" aria-labelledby="title">
    <div class="bar">
      <div class="title">
        <h2 id="title">Edit Student</h2>
        <span class="chip">#<?= (int)$id ?></span>
      </div>
    </div>

    <?php if ($okMsg): ?>
      <div class="msg ok"><?= h($okMsg) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="msg err">
        <strong>Please fix the following:</strong>
        <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

      <fieldset>
        <legend>Student Information</legend>
        <div class="grid">
          <div class="col-4">
            <label>Form 1 Class</label>
            <select name="form_1_class">
              <?php foreach ($classes as $c): ?>
                <option value="<?= h($c) ?>" <?= inputValue($row,'form_1_class',$FIELDS)===$c?'selected':''; ?>><?= $c?:'—' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4"><label>Student Name</label><input type="text" name="student_name" value="<?= inputValue($row,'student_name',$FIELDS) ?>"></div>
          <div class="col-4">
            <label>Gender</label>
            <select name="student_gender">
              <?php foreach ($genders as $g): ?>
                <option value="<?= h($g) ?>" <?= inputValue($row,'student_gender',$FIELDS)===$g?'selected':''; ?>><?= $g?:'—' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6"><label>Current Address</label><textarea name="student_current_address"><?= inputValue($row,'student_current_address',$FIELDS) ?></textarea></div>

          <!-- Not stored (no column in DB) -->
          <div class="col-6"><label>Residential Address (if different)</label><textarea name="residential_address"></textarea></div>

          <div class="col-4"><label>Date of Birth</label><input type="date" name="student_dob" value="<?= inputValue($row,'student_dob',$FIELDS) ?>"></div>
          <div class="col-4"><label>Birth Certificate Pin</label><input type="text" name="student_birth_certficate_pin" value="<?= inputValue($row,'student_birth_certficate_pin',$FIELDS) ?>"></div>
          <div class="col-4"><label>Religion</label><input type="text" name="student_religion" value="<?= inputValue($row,'student_religion',$FIELDS) ?>"></div>
          <div class="col-4"><label>Student Contact</label><input type="text" name="student_contact" value="<?= inputValue($row,'student_contact',$FIELDS) ?>"></div>
          <div class="col-4"><label>Country of Birth</label><input type="text" name="student_country_of_birth" value="<?= inputValue($row,'student_country_of_birth',$FIELDS) ?>"></div>
          <div class="col-4"><label>Nationality</label><input type="text" name="student_nationality" value="<?= inputValue($row,'student_nationality',$FIELDS) ?>"></div>
          <div class="col-4"><label>Ethnicity</label><input type="text" name="student_ethnicity" value="<?= inputValue($row,'student_ethnicity',$FIELDS) ?>"></div>
          <div class="col-8"><label>Email</label><input type="email" name="student_email" value="<?= inputValue($row,'student_email',$FIELDS) ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>S.E.A Information</legend>
        <div class="grid">
          <div class="col-4"><label>SEA Exam Date</label><input type="date" name="student_sea_date" value="<?= inputValue($row,'student_sea_date',$FIELDS) ?>"></div>
          <div class="col-4"><label>Primary School</label><input type="text" name="student_primary_school" value="<?= inputValue($row,'student_primary_school',$FIELDS) ?>"></div>
          <div class="col-4"><label>S.E.A Number</label><input type="text" name="student_sea_number" value="<?= inputValue($row,'student_sea_number',$FIELDS) ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Medical</legend>
        <div class="grid">
          <div class="col-6"><label>Medical Complications</label><input type="text" name="student_medical_condition" value="<?= inputValue($row,'student_medical_condition',$FIELDS) ?>"></div>
          <div class="col-3"><label>Blood Group</label><input type="text" name="student_bloodtype" value="<?= inputValue($row,'student_bloodtype',$FIELDS) ?>"></div>
          <div class="col-3"><label>Allergies</label><input type="text" name="student_allergies" value="<?= inputValue($row,'student_allergies',$FIELDS) ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Personal</legend>
        <div class="grid">
          <div class="col-3"><label>Boxlunch Preference</label><input type="text" name="student_school_feeding_option" value="<?= inputValue($row,'student_school_feeding_option',$FIELDS) ?>"></div>
          <div class="col-3"><label>Social Welfare</label><input type="text" name="student_social_welfare_status" value="<?= inputValue($row,'student_social_welfare_status',$FIELDS) ?>"></div>
          <div class="col-3"><label>Mode of Transport</label><input type="text" name="student_mode_of_transport" value="<?= inputValue($row,'student_mode_of_transport',$FIELDS) ?>"></div>
          <div class="col-3">
            <label>Immunized</label>
            <select name="student_immunization_status">
              <?php foreach ($yesNo as $v): ?>
                <option value="<?= h($v) ?>" <?= inputValue($row,'student_immunization_status',$FIELDS)===$v?'selected':''; ?>><?= $v?:'—' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4">
            <label>Continuous Access to Device</label>
            <select name="student_access_to_device">
              <?php foreach ($yesNo as $v): ?>
                <option value="<?= h($v) ?>" <?= inputValue($row,'student_access_to_device',$FIELDS)===$v?'selected':''; ?>><?= $v?:'—' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Mother</legend>
        <div class="grid">
          <div class="col-4"><label>Name</label><input type="text" name="mother_name" value="<?= inputValue($row,'mother_name',$FIELDS) ?>"></div>
          <div class="col-4">
            <label>Status</label>
            <select name="is_mother_active_or_deceased">
              <?php foreach ($aliveDec as $v): ?>
                <option value="<?= h($v) ?>" <?= inputValue($row,'is_mother_active_or_deceased',$FIELDS)===$v?'selected':''; ?>><?= $v?:'—' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4"><label>Profession</label><input type="text" name="mother_profession" value="<?= inputValue($row,'mother_profession',$FIELDS) ?>"></div>
          <div class="col-6"><label>Home Address</label><input type="text" name="mother_home_address" value="<?= inputValue($row,'mother_home_address',$FIELDS) ?>"></div>
          <div class="col-3"><label>Contact</label><input type="text" name="mother_contact" value="<?= inputValue($row,'mother_contact',$FIELDS) ?>"></div>
          <div class="col-3"><label>ID Type</label><input type="text" name="mother_identification_type" value="<?= inputValue($row,'mother_identification_type',$FIELDS) ?>"></div>
          <div class="col-4"><label>ID Number</label><input type="text" name="mother_identification_number" value="<?= inputValue($row,'mother_identification_number',$FIELDS) ?>"></div>
          <div class="col-6"><label>Work Address</label><input type="text" name="mother_work_address" value="<?= inputValue($row,'mother_work_address',$FIELDS) ?>"></div>
          <div class="col-6"><label>Email Address</label><input type="email" name="mother_email_address" value="<?= inputValue($row,'mother_email_address',$FIELDS) ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Father</legend>
        <div class="grid">
          <div class="col-4"><label>Name</label><input type="text" name="father_name" value="<?= inputValue($row,'father_name',$FIELDS) ?>"></div>
          <div class="col-4">
            <label>Status</label>
            <select name="is_father_active_or_deceased">
              <?php foreach ($aliveDec as $v): ?>
                <option value="<?= h($v) ?>" <?= inputValue($row,'is_father_active_or_deceased',$FIELDS)===$v?'selected':''; ?>><?= $v?:'—' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4"><label>Profession</label><input type="text" name="father_profession" value="<?= inputValue($row,'father_profession',$FIELDS) ?>"></div>
          <div class="col-6"><label>Home Address</label><input type="text" name="father_home_address" value="<?= inputValue($row,'father_home_address',$FIELDS) ?>"></div>
          <div class="col-3"><label>Contact</label><input type="text" name="father_contact" value="<?= inputValue($row,'father_contact',$FIELDS) ?>"></div>
          <div class="col-3"><label>ID Type</label><input type="text" name="father_identification_type" value="<?= inputValue($row,'father_identification_type',$FIELDS) ?>"></div>
          <div class="col-4"><label>ID Number</label><input type="text" name="father_identification_number" value="<?= inputValue($row,'father_identification_number',$FIELDS) ?>"></div>
          <div class="col-6"><label>Work Address</label><input type="text" name="father_work_address" value="<?= inputValue($row,'father_work_address',$FIELDS) ?>"></div>
          <div class="col-6"><label>Email Address</label><input type="email" name="father_email_address" value="<?= inputValue($row,'father_email_address',$FIELDS) ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Emergency Contact</legend>
        <div class="grid">
          <div class="col-4"><label>Name</label><input type="text" name="emergency_contact_name" value="<?= inputValue($row,'emergency_contact_name',$FIELDS) ?>"></div>
          <div class="col-4"><label>Relation</label><input type="text" name="emergency_contact_relation_to_student" value="<?= inputValue($row,'emergency_contact_relation_to_student',$FIELDS) ?>"></div>
          <div class="col-4"><label>Contact No.</label><input type="text" name="emergency_contact_number" value="<?= inputValue($row,'emergency_contact_number',$FIELDS) ?>"></div>
          <div class="col-12"><label>Address</label><input type="text" name="emergency_contact_address" value="<?= inputValue($row,'emergency_contact_address',$FIELDS) ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Registrant</legend>
        <div class="grid">
          <div class="col-4">
            <label>Relationship to Student</label>
            <select name="registrant_relationship_to_student">
              <?php foreach (['','Mother','Father','Other'] as $v): ?>
                <option value="<?= h($v) ?>" <?= inputValue($row,'registrant_relationship_to_student',$FIELDS)===$v?'selected':''; ?>><?= $v?:'—' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4"><label>Registrant Name (if Other)</label><input type="text" name="name_of_registrant" value="<?= inputValue($row,'name_of_registrant',$FIELDS) ?>"></div>
          <div class="col-2"><label>ID Type</label><input type="text" name="registrant_identification_type" value="<?= inputValue($row,'registrant_identification_type',$FIELDS) ?>"></div>
          <div class="col-2"><label>ID Number</label><input type="text" name="registrant_identification_number" value="<?= inputValue($row,'registrant_identification_number',$FIELDS) ?>"></div>
          <div class="col-4"><label>Date of Registration</label><input type="date" name="registration_date" value="<?= inputValue($row,'registration_date',$FIELDS) ?>"></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Passport Photo</legend>
        <div class="grid">
          <div class="col-4">
            <?php
              $currentPhoto = v($row, $FIELDS['student_passport_photo']);
              if ($currentPhoto) {
                echo '<img class="thumb" src="'.h($currentPhoto).'" alt="passport">';
              } else {
                echo '<div class="thumb" style="display:flex;align-items:center;justify-content:center;color:#999;">No image</div>';
              }
            ?>
          </div>
          <div class="col-8">
            <label>Upload New Photo (optional)</label>
            <input type="file" name="student_passport_photo" accept="image/*">
            <div class="muted">JPG/PNG/GIF/WEBP. If you upload, it replaces the stored path.</div>
          </div>
        </div>
      </fieldset>

      <div class="actions">
        <a class="btn ghost" href="javascript:history.back()">Cancel</a>
        <button class="btn" type="submit">Save Changes</button>
      </div>
    </form>
  </main>
</body>
</html>
