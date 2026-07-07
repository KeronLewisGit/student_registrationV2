<?php
// records.php
require __DIR__ . '/auth_bootstrap.php';
// CSRF for logout
if (empty($_SESSION['csrf_logout'])) {
    $_SESSION['csrf_logout'] = bin2hex(random_bytes(32));
}
$logoutToken = $_SESSION['csrf_logout'];

requireLogin($auth);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/') ?>">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <link rel="icon" type="image/x-icon" href="successlogo.png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" crossorigin="anonymous"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <title>Success Student Management System</title>
    <style>
      :root{
        --bg: #0f172a;        /* slate-900 */
        --bg2:#111827;        /* gray-900 */
        --card:#ffffff;       /* white */
        --text:#0f172a;       /* slate-900 */
        --muted:#6b7280;      /* gray-500 */
        --line:#e5e7eb;       /* gray-200 */
        --primary:#0b5fff;    /* brand */
        --primary-600:#0a53e8;
        --danger:#ef4444;
      }

      /* ---------- Base ---------- */
      *{ box-sizing:border-box; font-family:"Lato",system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
      html,body{ height:100%; }
      body{
        margin:0; background:
          radial-gradient(1200px 600px at 10% 10%, #1f2937 0%, transparent 60%),
          radial-gradient(1200px 600px at 90% 90%, #0b5fff22 0%, transparent 60%),
          linear-gradient(160deg,var(--bg),var(--bg2));
        color:var(--text);
      }
      .page{ min-height:100%; padding:24px; }
      .container-wide{ width:100%; max-width:1200px; margin:0 auto; }

      /* ---------- App Bar ---------- */
      .appbar{
        background: var(--card);
        border:1px solid var(--line);
        border-radius:16px;
        padding:12px 16px;
        display:flex; align-items:center; justify-content:space-between;
        box-shadow:0 20px 60px rgba(0,0,0,.20);
        margin-bottom:16px;
      }
      .brand{ display:flex; align-items:center; gap:12px; }
      .brand img{ width:40px; height:40px; object-fit:contain; }
      .brand h1{ font-size:18px; line-height:1.2; margin:0; color:var(--text); }
      .brand .sub{ font-size:13px; color:var(--muted); margin:0; }

      /* ---------- Buttons ---------- */
      .btn-primary{ background:var(--primary); border-color:var(--primary); }
      .btn-primary:hover{ background:var(--primary-600); border-color:var(--primary-600); }
      .btn-outline-danger{ border-color:var(--danger); color:var(--danger); }
      .btn-outline-danger:hover{ background:var(--danger); color:#fff; }
      .btn, .form-select{ border-radius:12px; }

      /* ---------- Filter Card (compact & responsive) ---------- */
      .filters-card{
        background:var(--card);
        border:1px solid var(--line);
        border-radius:16px;
        padding:12px;
        box-shadow:0 12px 32px rgba(0,0,0,.12);
        margin-bottom:20px;
      }
      /* Button on the left, filters on the right */
      .filters-layout{
        display:grid;
        grid-template-columns:auto 1fr;
        align-items:center;
        gap:12px;
      }
      /* Year | Class | Student Name (grows) */
      .filters-group{
        display:grid;
        grid-template-columns:
          minmax(120px,160px)
          minmax(140px,200px)
          minmax(260px,1fr);
        gap:12px;
      }
      .filters-card .form-select{
        width:100%;
        max-width:100%;
        min-height:42px;
      }
      label.visually-hidden{
        position:absolute !important; height:1px; width:1px; overflow:hidden;
        clip:rect(1px,1px,1px,1px); white-space:nowrap;
      }
      #loader{ display:none; margin-top:12px; }
      /* Tablet: give Name extra weight but allow wrapping nicely */
      @media (max-width: 992px){
        .filters-group{
          grid-template-columns:
            minmax(120px,1fr)
            minmax(140px,1fr)
            minmax(240px,2fr);
        }
      }
      /* Mobile: stack */
      @media (max-width: 640px){
        .filters-layout{ grid-template-columns:1fr; row-gap:10px; }
        .filters-group{ grid-template-columns:1fr; }
      }

      /* ---------- Profile Card ---------- */
      .profile{
        position: relative;
        margin: 24px auto;
        width: 100%;
        max-width: 1200px;
        min-height: 1056px;
        background:#fff;
        border:1px solid var(--line);
        border-radius:16px;
        box-shadow: 0 20px 60px rgba(0,0,0,.18);
        overflow: visible;
      }
      .profile + .profile { border-top: 12px solid transparent; }

      .profile-actions{
        position:absolute; top:14px; right:14px; z-index:5;
        display:flex; gap:8px; flex-wrap:wrap;
      }
      .profile::after{
        content:"";
        position:absolute; inset:120px 80px 120px 80px;
        background:url('Official Document1.png') center/contain no-repeat;
        opacity:.06; pointer-events:none; z-index:0;
      }
      .profile .profile-inner{ position:relative; z-index:1; padding:40px 48px 24px; }
      .profile .toprow { align-items:flex-start; }
      .profile .toprow h2{ font-size:32px; margin:0; line-height:1.25; }
      .profile .toprow p { margin:10px 0 0; color:#374151; }
      .profile img.logo { width:160px; height:auto; }
      .profile .passport { width:150px; height:150px; object-fit:cover; background:#f3f3f3; border-radius:12px; border:1px solid var(--line); }

      .card{ margin-top:20px; border:1px solid var(--line); border-radius:14px; }
      .card-header{
        background:#f9fafb; border-bottom:1px solid var(--line);
        padding:.75rem 1rem; font-weight:700; color:#111827; border-top-left-radius:14px; border-top-right-radius:14px;
      }
      .card-body{ padding:1rem; }
      .card-title{ font-size:14px; color:#111827; margin:0 0 4px; }
      .card-text{ font-size:13px; color:#1f2937; }

      .section-end { height:1px; }

      @media (max-width: 992px){
        .profile .profile-inner{ padding:28px 24px; }
        .profile .toprow h2{ font-size:28px; }
        .profile img.logo{ width:140px; }
        .profile .passport{ width:120px; height:120px; }
        .profile::after{ inset:100px 48px 100px 48px; }
      }
      @media (max-width: 768px){
        .profile{ margin:16px auto; border-radius:12px; min-height:auto; }
        .profile .profile-inner{ padding:20px 16px; }
        .profile .toprow > [class^="col-"],
        .card .row > [class^="col-"]{ width:100%; flex:0 0 100%; max-width:100%; }
        .profile .toprow{ row-gap:12px; }
        .profile img.logo{ width:120px; margin-left:auto; display:block; }
        .profile .passport{ width:110px; height:110px; }
        .profile-actions{ position:static; margin:0 0 8px auto; justify-content:flex-end; }
        .profile::after{ inset:80px 24px 80px 24px; opacity:.07; }
      }
      @media (max-width: 480px){
        .card{ margin-top:14px; }
        .card-header{ padding:.5rem .75rem; }
        .card-body{ padding:.75rem; }
        .card-text{ font-size:13px; }
      }
    </style>
  </head>
  <body>

<?php
require 'vendor/autoload.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    require __DIR__ . '/connect.php';
}

function showDateDMY(?string $v): string {
  if (!$v) return 'No record provided';
  $ts = strtotime($v);
  if ($ts === false || $ts <= 0) return 'No record provided';
  return date('d/m/Y', $ts);
}
function h(?string $v): string {
  return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ------------ READ FILTERS ------------ */
$selectedYear  = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$selectedClass = isset($_GET['student_class']) ? trim($_GET['student_class']) : '0';
$selectedName  = isset($_GET['student_name']) ? trim($_GET['student_name']) : '0';
$classLetter   = ($selectedClass !== '0') ? strtoupper(substr($selectedClass, -1)) : '0';

/* ------------ BASE QUERY + WHERE ------------ */
$sqlBase = "FROM `gkblvzmy_student-portal`.student_registration_data WHERE 1";
$params  = [];

if ($selectedYear > 0) {
  $sqlBase .= " AND registration_date IS NOT NULL AND YEAR(registration_date) = :yr";
  $params[':yr'] = $selectedYear;
}
if ($classLetter !== '0') {
  $sqlBase .= " AND form_1_class = :cls";
  $params[':cls'] = $classLetter;
}
if ($selectedName !== '0') {
  $sqlBase .= " AND student_name = :sname";
  $params[':sname'] = $selectedName;
}

/* ------------ FETCH DATA (filtered) ------------ */
$stmt = $pdo->prepare("SELECT * $sqlBase ORDER BY student_name ASC");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ------------ YEARS (for dropdown) ------------ */
$yearsStmt = $pdo->query("
  SELECT DISTINCT YEAR(registration_date) AS yr
  FROM `gkblvzmy_student-portal`.student_registration_data
  WHERE registration_date IS NOT NULL
  ORDER BY yr DESC
");
$years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

/* ------------ NAMES (for dropdown; keep class/year scope even if one student selected) ------------ */
$nameParams = [];
$sqlNames = "SELECT DISTINCT student_name FROM `gkblvzmy_student-portal`.student_registration_data WHERE 1";
if ($selectedYear > 0) {
  $sqlNames .= " AND registration_date IS NOT NULL AND YEAR(registration_date) = :nyr";
  $nameParams[':nyr'] = $selectedYear;
}
if ($classLetter !== '0') {
  $sqlNames .= " AND form_1_class = :ncls";
  $nameParams[':ncls'] = $classLetter;
}
$sqlNames .= " ORDER BY student_name ASC";
$nameStmt = $pdo->prepare($sqlNames);
$nameStmt->execute($nameParams);
$nameOptions = array_fill_keys($nameStmt->fetchAll(PDO::FETCH_COLUMN), true);
?>

<div class="page">
  <div class="container-wide">

    <!-- App Bar -->
    <header class="appbar">
      <div class="brand">
        <img src="successlogo.png" alt="SLSS Logo">
        <div>
          <h1>Success Student Management System</h1>
          <p class="sub">Student Records</p>
        </div>
      </div>
      <form method="post" action="logout.php">
        <input type="hidden" name="csrf" value="<?= h($logoutToken) ?>">
        <button type="submit" class="btn btn-outline-danger">Log out</button>
      </form>
    </header>

    <!-- Filters Card -->
    <section class="filters-card">
      <div class="filters-layout">
        <div>
          <a id="btn-bulk-pdf" class="btn btn-primary">Generate Bulk PDF</a>
        </div>

        <form class="filters-group" method="get" action="">
          <!-- Year -->
          <div>
            <label class="visually-hidden" for="year">Select Year:</label>
            <select name="year" id="year" class="form-select" onchange="this.form.submit()">
              <option value="0" <?= $selectedYear===0?'selected':''; ?>>All Years</option>
              <?php foreach ($years as $yr): ?>
                <option value="<?= h($yr) ?>" <?= ($selectedYear===(int)$yr)?'selected':''; ?>>
                  <?= h($yr) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Class -->
          <div>
            <label class="visually-hidden" for="student_class">Select Student Class:</label>
            <select name="student_class" id="student_class" class="form-select" onchange="this.form.submit()">
              <option value="0"  <?= $selectedClass==='0'?'selected':'';  ?>>All Students</option>
              <option value="1A" <?= $selectedClass==='1A'?'selected':''; ?>>Form 1A</option>
              <option value="1B" <?= $selectedClass==='1B'?'selected':''; ?>>Form 1B</option>
              <option value="1C" <?= $selectedClass==='1C'?'selected':''; ?>>Form 1C</option>
              <option value="1D" <?= $selectedClass==='1D'?'selected':''; ?>>Form 1D</option>
              <option value="1E" <?= $selectedClass==='1E'?'selected':''; ?>>Form 1E</option>
            </select>
          </div>

          <!-- Student Name (expands to fill remaining space) -->
          <div>
            <label class="visually-hidden" for="student_name">Select Student Name:</label>
            <select
              name="student_name"
              id="student_name"
              class="form-select"
              onchange="this.form.submit()"
              title="<?= $selectedName && $selectedName!=='0' ? h($selectedName) : 'Student Name' ?>"
            >
              <option value="0" <?= $selectedName==='0'?'selected':''; ?>>Student Name</option>
              <?php foreach ($nameOptions as $nm => $_): ?>
                <option
                  value="<?= h($nm) ?>"
                  <?= ($selectedName===$nm)?'selected':''; ?>
                  title="<?= h($nm) ?>"
                >
                  <?= h(ucwords(strtolower($nm))) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>

      <div id="loader">
        <div class="d-flex justify-content-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </section>

<?php
// ----------- RENDER STUDENT CARDS -----------
foreach ($rows as $row) {
  $headerimg = '';
  if (!empty($row['student_passport_photo'])) {
    $info = pathinfo($row['student_passport_photo']);
    $headerimg = isset($info['extension']) ? strtolower($info['extension']) : '';
  }

  $safeClass = preg_replace('~\s+~', '', (string)$row['form_1_class']);
  $safeName  = preg_replace('~\s+~', '', (string)$row['student_name']);

  // Safer JSON-encoded name for JS call
  $jsStudentName = json_encode((string)$row['student_name'], JSON_HEX_APOS | JSON_HEX_QUOT);

  echo '<div class="profile '.$safeName.' '.$safeClass.'">';

  // Actions: Edit + Generate PDF
  echo '<div class="profile-actions">';
  echo '  <a class="btn btn-sm btn-outline-secondary" href="edit_student.php?id='.(int)$row['id'].'">Edit</a>';
  echo '  <button class="btn btn-sm btn-outline-primary" onclick="openProfilePdf('.h($jsStudentName).')">Generate PDF</button>';
  echo '</div>';

  echo '<div class="profile-inner">';
  echo '<div class="row toprow">';
  if(!empty($row['student_passport_photo']) && $headerimg !== 'pdf'){
    echo '<div class="col-12 col-md-3"><h6>Passport Size Photo</h6><img class="passport" src="'.h($row['student_passport_photo']).'" alt="Passport"></div>';
  } else {
    echo '<div class="col-12 col-md-3"><h6>Passport Size Photo</h6><img class="passport" src="noimage.jpg" alt="No Image"></div>';
  }
  echo '<div class="col-12 col-md-6"><h2>Success Laventille Secondary School Eastern Main Road</h2><br><p>Official Student Record.</p></div>';
  echo '<div class="col-12 col-md-3 text-md-end"><img class="logo" src="successlogo.png" alt="Logo"></div>';
  echo '</div>';

  // Student Information
  echo '<div class="card"><div class="card-header">Student Information </div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Form Class: </h5><p class="card-text">'.h($row['form_1_class']).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Student Name: </h5><p class="card-text">'.h($row['student_name']).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Gender: </h5><p class="card-text">'.h($row['student_gender']).'</p></div>';
  echo '</div>';

  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Student Address: </h5><p class="card-text">'.ucwords(strtolower($row['student_current_address'] ?? '')).'</p></div>';

  $resCol = 'Residential (Permanent) Address, IF DIFFERENT from Current Address provided above.';
  $hasCurrent = !empty($row['student_current_address']);
  $hasResidential = !empty($row[$resCol] ?? '');
  if ($hasCurrent && !$hasResidential) {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Residential Address</h5><p class="card-text">Same as Current Address.</p></div>';
  } else {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Residential Address</h5><p class="card-text">'.ucwords(strtolower($row[$resCol] ?? '')).'</p></div>';
  }

  echo '<div class="col-12 col-md-4"><h5 class="card-title">Date of Birth</h5><p class="card-text">'.showDateDMY($row['student_dob'] ?? null).'</p></div>';
  echo '</div>';

  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Birth Certificate Pin</h5><p class="card-text">'.h($row['student_birth_certficate_pin']).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Religion</h5><p class="card-text">'.h($row['student_religion']).'</p></div>';
  echo '</div>';

  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Country of Birth</h5><p class="card-text">'.h($row['student_country_of_birth']).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Nationality</h5><p class="card-text">'.h($row['student_nationality']).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Student Ethnicity</h5><p class="card-text">'.h($row['student_ethnicity']).'</p></div>';
  echo '</div>';

  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Student Contact</h5><p class="card-text">'.(!empty($row['student_contact']) ? h($row['student_contact']) : 'None Provided.').'</p></div>';
  echo '<div class="col-12 col-md-8"><h5 class="card-title">Student Email</h5><p class="card-text">'.(!empty($row['student_email']) ? h($row['student_email']) : 'None Provided.').'</p></div>';
  echo '</div>';

  echo '</div></div>';

  // SEA Information
  echo '<div class="card"><div class="card-header">SEA Information</div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">SEA Exam Date</h5><p class="card-text">'.showDateDMY($row['student_sea_date'] ?? null).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Primary School:</h5><p class="card-text">'.ucwords(strtolower($row['student_primary_school'] ?? '')).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">S.E.A Number:</h5><p class="card-text">'.h($row['student_sea_number'] ?? '').'</p></div>';
  echo '</div>';
  echo '</div></div>';

  // Medical
  echo '<div class="card"><div class="card-header">Medical Information</div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-6"><h5 class="card-title">Medical Complications</h5><p class="card-text">'.h($row['student_medical_condition'] ?? '').'</p></div>';
  if (!empty($row['student_medical_condition'])) {
    echo '<div class="col-12 col-md-6"><h5 class="card-title">Complication Type</h5><p class="card-text">'.h($row['student_medical_condition']).'</p></div>';
  } else {
    echo '<div class="col-12 col-md-6"><h5 class="card-title">Complication Type</h5><p class="card-text">No record provided</p></div>';
  }
  echo '<div class="col-12 col-md-6"><h5 class="card-title">Blood Group:</h5><p class="card-text">'.h($row['student_bloodtype']).'</p></div>';
  if (!empty($row['student_allergies'])) {
    echo '<div class="col-12 col-md-6"><h5 class="card-title">Allergies:</h5><p class="card-text">'.h($row['student_allergies']).'</p></div>';
  } else {
    echo '<div class="col-12 col-md-6"><h5 class="card-title">Allergies:</h5><p class="card-text">No record provided</p></div>';
  }
  echo '</div></div></div>';

  // Personal
  echo '<div class="card"><div class="card-header">Personal Information</div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Boxlunch Preference</h5><p class="card-text">'.h($row['student_school_feeding_option']).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Social Welfare</h5><p class="card-text">'.h($row['student_social_welfare_status']).'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Mode of Transport</h5><p class="card-text">'.h($row['student_mode_of_transport']).'</p></div>';
  echo '</div>';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Immunized</h5><p class="card-text">'.h($row['student_immunization_status']).'</p></div>';
  echo '<div class="col-12 col-md-8"><h5 class="card-title">Continuous Access to Device</h5><p class="card-text">'.h($row['student_access_to_device']).'</p></div>';
  echo '</div>';
  echo '</div></div>';

  // Mother
  echo '<div class="card"><div class="card-header">Parent/Guardian Information (Mother)</div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Mother&#39;s Name:</h5><p class="card-text">'.(!empty($row['mother_name']) ? ucwords(strtolower($row['mother_name'])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Status</h5><p class="card-text">'.(($row['is_mother_active_or_deceased'] ?? '') === "Deceased" ? 'Deceased' : 'Alive').'</p></div>';
  if(!empty($row['mother_identification_number'])){
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">('.h($row['mother_identification_type']).')<br>'.h($row['mother_identification_number']).'</p></div>';
  } else {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">No record provided</p></div>';
  }
  echo '</div>';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Home Address</h5><p class="card-text">'.(!empty($row['mother_home_address']) ? ucwords(strtolower($row['mother_home_address'])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Contact</h5><p class="card-text">'.(!empty($row['mother_contact']) ? h($row['mother_contact']) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Profession</h5><p class="card-text">'.(!empty($row['mother_profession']) ? ucwords(strtolower($row['mother_profession'])) : 'No record provided').'</p></div>';
  echo '</div>';
  $mWorkAddrCol = "Mother's Work Address"; $mEmailCol = "Mother's Email Address";
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Work Address</h5><p class="card-text">'.(!empty($row[$mWorkAddrCol] ?? '') ? ucwords(strtolower($row[$mWorkAddrCol])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-8"><h5 class="card-title">Email Address</h5><p class="card-text">'.(!empty($row[$mEmailCol] ?? '') ? h($row[$mEmailCol]) : 'No record provided').'</p></div>';
  echo '</div>';
  echo '</div></div>';

  // Father
  echo '<div class="card"><div class="card-header">Parent/Guardian Information (Father)</div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Father&#39;s Name:</h5><p class="card-text">'.(!empty($row['father_name']) ? ucwords(strtolower($row['father_name'])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Status</h5><p class="card-text">'.(($row['is_father_active_or_deceased'] ?? '') === "Deceased" ? 'Deceased' : 'Alive').'</p></div>';
  if(!empty($row['father_identification_number'])){
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">('.h($row['father_identification_type']).')<br>'.h($row['father_identification_number']).'</p></div>';
  } else {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">No record provided</p></div>';
  }
  echo '</div>';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Home Address</h5><p class="card-text">'.(!empty($row['father_home_address']) ? ucwords(strtolower($row['father_home_address'])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Contact</h5><p class="card-text">'.(!empty($row['father_contact']) ? h($row['father_contact']) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Profession</h5><p class="card-text">'.(!empty($row['father_profession']) ? ucwords(strtolower($row['father_profession'])) : 'No record provided').'</p></div>';
  echo '</div>';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Work Address</h5><p class="card-text">'.(!empty($row['father_work_address']) ? ucwords(strtolower($row['father_work_address'])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-8"><h5 class="card-title">Email Address</h5><p class="card-text">'.(!empty($row['father_email_address']) ? h($row['father_email_address']) : 'No record provided').'</p></div>';
  echo '</div>';
  echo '</div></div>';

  // Emergency
  echo '<div class="card"><div class="card-header">Emergency Contact Information</div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Contact Name</h5><p class="card-text">'.(!empty($row['emergency_contact_name']) ? ucwords(strtolower($row['emergency_contact_name'])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Relation</h5><p class="card-text">'.(!empty($row['emergency_contact_relation_to_student']) ? ucwords(strtolower($row['emergency_contact_relation_to_student'])) : 'No record provided').'</p></div>';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Contact No.</h5><p class="card-text">'.(!empty($row['emergency_contact_number']) ? h($row['emergency_contact_number']) : 'No record provided').'</p></div>';
  echo '</div>';
  echo '<div class="row">';
  echo '<div class="col-12"><h5 class="card-title">Address</h5><p class="card-text">'.(!empty($row['emergency_contact_address']) ? ucwords(strtolower($row['emergency_contact_address'])) : 'No record provided').'</p></div>';
  echo '</div>';
  echo '</div></div>';

  // Registrant
  echo '<div class="card"><div class="card-header">Registrant Information</div><div class="card-body">';
  echo '<div class="row">';
  echo '<div class="col-12 col-md-4"><h5 class="card-title">Date of Registration</h5><p class="card-text">'.showDateDMY($row['registration_date'] ?? null).'</p></div>';

  $rel = $row['registrant_relationship_to_student'] ?? '';
  if ($rel === "Mother") {
    $regName = ucwords(strtolower($row['mother_name'] ?? ''));
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Registrant (Mother)</h5><p class="card-text">'.$regName.'</p></div>';
  } elseif ($rel === "Father") {
    $regName = ucwords(strtolower($row['father_name'] ?? ''));
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Registrant (Father)</h5><p class="card-text">'.$regName.'</p></div>';
  } elseif ($rel === "Other") {
    $otherNameCol = "Name of Registrant";
    $regName = ucwords(strtolower($row[$otherNameCol] ?? ''));
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Registrant (Other)</h5><p class="card-text">'.$regName.'</p></div>';
  } else {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Registrant Name</h5><p class="card-text">No record provided</p></div>';
  }

  if ($rel === "Mother") {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">('.
          h($row['mother_identification_type']).')<br>'.
          h($row['mother_identification_number']).'</p></div>';
  } elseif ($rel === "Father") {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">('.
          h($row['father_identification_type']).')<br>'.
          h($row['father_identification_number']).'</p></div>';
  } elseif ($rel === "Other") {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">('.
          h($row['registrant_identification_type']).')<br>'.
          h($row['registrant_identification_number']).'</p></div>';
  } else {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Identification</h5><p class="card-text">No record provided</p></div>';
  }

  echo '</div>';
  echo '<div class="row">';
  if ($rel === "Mother") {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Profession</h5><p class="card-text">'.(!empty($row['mother_profession']) ? ucwords(strtolower($row['mother_profession'])) : 'No record provided').'</p></div>';
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Home Address</h5><p class="card-text">'.(!empty($row['mother_home_address']) ? ucwords(strtolower($row['mother_home_address'])) : 'No record provided').'</p></div>';
  } elseif ($rel === "Father") {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Profession</h5><p class="card-text">'.(!empty($row['father_profession']) ? ucwords(strtolower($row['father_profession'])) : 'No record provided').'</p></div>';
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Home Address</h5><p class="card-text">'.(!empty($row['father_home_address']) ? ucwords(strtolower($row['father_home_address'])) : 'No record provided').'</p></div>';
  } else {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Profession</h5><p class="card-text">No record provided</p></div>';
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Home Address</h5><p class="card-text">No record provided</p></div>';
  }

  if ($rel === "Mother") {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Contact</h5><p class="card-text">'.(!empty($row['mother_contact']) ? h($row['mother_contact']) : 'No record provided').'</p></div>';
  } elseif ($rel === "Father") {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Contact</h5><p class="card-text">'.(!empty($row['father_contact']) ? h($row['father_contact']) : 'No record provided').'</p></div>';
  } else {
    echo '<div class="col-12 col-md-4"><h5 class="card-title">Contact</h5><p class="card-text">No record provided</p></div>';
  }
  echo '</div>';
  echo '</div></div>'; // card

  echo '</div>'; // profile-inner
  echo '</div>'; // profile

  echo '<div class="section-end"></div>';
}
?>

  </div> <!-- /.container-wide -->
</div> <!-- /.page -->

<script>
(function(){
  function q(param){ return encodeURIComponent(param ?? ''); }
  function currentFiltersToQuery(){
    const yr   = document.getElementById('year')?.value || '0';
    const cls  = document.getElementById('student_class')?.value || '0';
    const name = document.getElementById('student_name')?.value || '0';
    const parts = [];
    if (yr && yr !== '0')   parts.push('year=' + q(yr));
    if (cls && cls !== '0') parts.push('student_class=' + q(cls));
    if (name && name !== '0') parts.push('student_name=' + q(name));
    return parts.length ? ('?' + parts.join('&')) : '';
  }

  const bulkBtn = document.getElementById('btn-bulk-pdf');
  if (bulkBtn){
    bulkBtn.addEventListener('click', function(e){
      e.preventDefault();
      const url = 'records_pdf.php' + currentFiltersToQuery();
      window.open(url, '_blank', 'noopener');
    });
  }
})();

// Per-profile PDF: reuse current filters but force student_name to the clicked one
function openProfilePdf(studentName){
  const yr   = document.getElementById('year')?.value || '0';
  const cls  = document.getElementById('student_class')?.value || '0';
  const qs = [];
  if (yr !== '0')  qs.push('year=' + encodeURIComponent(yr));
  if (cls !== '0') qs.push('student_class=' + encodeURIComponent(cls));
  qs.push('student_name=' + encodeURIComponent(studentName));
  const url = 'records_pdf.php' + (qs.length ? '?' + qs.join('&') : '');
  window.open(url, '_blank', 'noopener');
}
</script>

  </body>
</html>
