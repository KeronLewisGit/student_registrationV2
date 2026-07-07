<?php
// records_pdf.php — generate filtered student profiles as a PDF via Dompdf
// Images are embedded as base64 for reliability (local files). Remote http(s) kept as-is.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

@ini_set('memory_limit', '1024M');
@set_time_limit(120);

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = require __DIR__ . '/connect.php';

/* ----------- Filters ----------- */
$selectedYear  = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$selectedClass = isset($_GET['student_class']) ? trim($_GET['student_class']) : '0';
$selectedName  = isset($_GET['student_name']) ? trim($_GET['student_name']) : '0';
$classLetter   = ($selectedClass !== '0') ? strtoupper(substr($selectedClass, -1)) : '0';

/* Batch controls */
$limit  = isset($_GET['limit'])  ? max(1, (int)$_GET['limit'])  : 120;
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

/* ----------- Query ----------- */
$sqlBase = "FROM `gkblvzmy_student-portal`.student_registration_data WHERE 1";
$params  = [];

if ($selectedYear > 0) { $sqlBase .= " AND registration_date IS NOT NULL AND YEAR(registration_date) = :yr"; $params[':yr'] = $selectedYear; }
if ($classLetter !== '0') { $sqlBase .= " AND form_1_class = :cls"; $params[':cls'] = $classLetter; }
if ($selectedName !== '0') { $sqlBase .= " AND student_name = :sname"; $params[':sname'] = $selectedName; }

$sql = "SELECT * $sqlBase ORDER BY student_name ASC LIMIT :lim OFFSET :off";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':off',  $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
  header('Content-Type: text/plain; charset=utf-8');
  echo "No student records found for the selected filters.";
  exit;
}

/* ----------- Helpers ----------- */
function showDateDMY(?string $v): string {
  if (!$v) return 'No record provided';
  $ts = strtotime($v);
  if ($ts === false || $ts <= 0) return 'No record provided';
  return date('d/m/Y', $ts);
}
function h(?string $v): string {
  return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Embed local image as base64 (remote http(s) left as-is). */
function img_src(?string $src, ?string $fallbackLocal = null): string {
  $src = trim((string)$src);
  if ($src !== '' && preg_match('~^(https?:)?//|data:~i', $src)) return $src;

  $candidates = [];
  if ($src !== '') {
    $candidates[] = __DIR__ . '/' . ltrim($src, '/');
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
      $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($src, '/');
    }
  }
  if ($fallbackLocal) {
    $candidates[] = __DIR__ . '/' . ltrim($fallbackLocal, '/');
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
      $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($fallbackLocal, '/');
    }
  }

  foreach ($candidates as $path) {
    $real = realpath($path);
    if ($real && is_file($real) && is_readable($real)) {
      $data = @file_get_contents($real);
      if ($data === false) continue;
      $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
      $mime  = $finfo ? finfo_file($finfo, $real) : null;
      if ($finfo) @finfo_close($finfo);
      if (!$mime) {
        $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $mime = match ($ext) {
          'png' => 'image/png', 'jpg','jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp',
          default => 'application/octet-stream'
        };
      }
      return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
  }
  return '';
}

/* Base assets (embedded for reliability) */
$logoSrc      = img_src('successlogo.png', 'successlogo.png');
$watermarkSrc = img_src('Official Document1.png', 'OfficialDocument1.png'); // try both names
$noImageSrc   = img_src('noimage.jpg', 'noimage.jpg');

/* ----------- HTML ----------- */
ob_start();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Student Profiles</title>
<style>
  @page { size: Letter; margin: 10mm; }
  * { box-sizing: border-box; font-family: "Lato", Arial, Helvetica, sans-serif; }
  body { margin: 0; color: #111; }

  /* Watermark that repeats on EVERY page */
  .wm-fixed{
    position: fixed;
    top: 28mm; left: 12mm; right: 12mm; bottom: 22mm;
    opacity: 0.65;                    /* subtle */
    z-index: 999;                       /* behind content */
    pointer-events: none;
  }
  .wm-fixed img{ width:100%; height:100%; object-fit: contain; }

  .page {
    position: relative;
    page-break-after: always;
    padding: 16mm 12mm 12mm 12mm;     /* header padding */
    background: #fff;
  }
  .page:last-child { page-break-after: auto; }

  .content { position: relative; z-index: 1; }

  /* Simple table-based grid */
  .row  { display: table; width: 100%; table-layout: fixed; }
  .col  { display: table-cell; vertical-align: top; padding-right: 6mm; }
  .col-4 { width: 25%; }
  .col-3 { width: 33.333%; }
  .col-2 { width: 50%; }
  .col-1 { width: 100%; }

  /* Header 25% / 55% / 20% */
  .row3{ display: table; width:100%; table-layout: fixed; }
  .row3 .left  { display: table-cell; width:25%; vertical-align: top; padding-right:6mm; }
  .row3 .middle{ display: table-cell; width:55%; vertical-align: top; padding-right:6mm; }
  .row3 .right { display: table-cell; width:20%; vertical-align: top; text-align:right; }

  .toprow { margin-bottom: 8mm; }
  .passport-box { width: 30mm; height: 30mm; border-radius: 3pt; overflow: hidden; background:#f0f0f0; }
  .passport-box img { width: 30mm; height: 30mm; object-fit: cover; display:block; }
  .logo { width: 30mm; height: auto; display:block; }

  .school-title h1 { font-size: 18pt; line-height: 1.25; margin: 0 0 3mm; font-weight: 800; }
  .school-title p  { margin: 0; font-size: 11pt; color: #333; }

  .card { border: 0.4pt solid #ddd; border-radius: 3pt; margin: 4mm 0; padding: 3mm; page-break-inside: avoid; background: transparent; }
  .card h3 { margin: 0 0 2mm 0; font-size: 12pt; border-bottom: 0.4pt solid #eee; padding-bottom: 1.5mm; }
  .label { font-weight: 700; font-size: 10pt; margin-bottom: 0.5mm; }
  .value { font-size: 10pt; color: #333; }
</style>
</head>
<body>

<!-- ONE fixed watermark for the whole document (repeats on every page) -->
<div class="wm-fixed"><img src="<?= h($watermarkSrc) ?>" alt=""></div>

<?php foreach ($rows as $row): ?>
<?php
  $passportSrc = $noImageSrc;
  $rawPassport = trim((string)($row['student_passport_photo'] ?? ''));
  if ($rawPassport !== '') {
    $passportSrc = preg_match('~^https?://~i', $rawPassport)
      ? $rawPassport
      : (img_src($rawPassport, 'noimage.jpg') ?: $noImageSrc);
  }
?>
<section class="page">
  <div class="content">

    <!-- Header: 25% / 55% / 20% -->
    <div class="row3 toprow">
      <div class="left">
        <div class="label" style="font-size:8pt;margin:0 0 2mm;">Passport Size Photo</div>
        <div class="passport-box">
          <img src="<?= h($passportSrc) ?>" alt="Passport">
        </div>
      </div>

      <div class="middle school-title">
        <h1>Success Laventille Secondary School Eastern Main Road</h1>
        <p>Official Student Record.</p>
      </div>

      <div class="right">
        <img src="<?= h($logoSrc) ?>" class="logo" alt="Logo">
      </div>
    </div>

    <div class="card">
      <h3>Student Information</h3>
      <div class="row">
        <div class="col col-3"><div class="label">Form Class</div><div class="value"><?= h($row['form_1_class'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Student Name</div><div class="value"><?= h($row['student_name'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Gender</div><div class="value"><?= h($row['student_gender'] ?? '') ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Student Address</div><div class="value"><?= h(ucwords(strtolower($row['student_current_address'] ?? ''))) ?></div></div>
        <div class="col col-3"><div class="label">Residential Address</div><div class="value">
          <?php
            $resCol="Residential (Permanent) Address, IF DIFFERENT from Current Address provided above.";
            $hasCur=!empty($row['student_current_address']); $hasRes=!empty($row[$resCol] ?? '');
            echo $hasCur && !$hasRes ? 'Same as Current Address.' : h(ucwords(strtolower($row[$resCol] ?? '')));
          ?>
        </div></div>
        <div class="col col-3"><div class="label">Date of Birth</div><div class="value"><?= h(showDateDMY($row['student_dob'] ?? null)) ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Birth Certificate Pin</div><div class="value"><?= h($row['student_birth_certficate_pin'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Religion</div><div class="value"><?= h($row['student_religion'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Student Contact</div><div class="value"><?= h(($row['student_contact'] ?? '') ?: 'None Provided.') ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Country of Birth</div><div class="value"><?= h($row['student_country_of_birth'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Nationality</div><div class="value"><?= h($row['student_nationality'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Student Ethnicity</div><div class="value"><?= h($row['student_ethnicity'] ?? '') ?></div></div>
      </div>
      <div class="row">
        <div class="col col-1"><div class="label">Student Email</div><div class="value"><?= h(($row['student_email'] ?? '') ?: 'None Provided.') ?></div></div>
      </div>
    </div>

    <div class="card">
      <h3>SEA Information</h3>
      <div class="row">
        <div class="col col-3"><div class="label">SEA Exam Date</div><div class="value"><?= h(showDateDMY($row['student_sea_date'] ?? null)) ?></div></div>
        <div class="col col-3"><div class="label">Primary School</div><div class="value"><?= h(ucwords(strtolower($row['student_primary_school'] ?? ''))) ?></div></div>
        <div class="col col-3"><div class="label">S.E.A Number</div><div class="value"><?= h($row['student_sea_number'] ?? '') ?></div></div>
      </div>
    </div>

    <div class="card">
      <h3>Medical Information</h3>
      <div class="row">
        <div class="col col-2"><div class="label">Medical Complications</div><div class="value"><?= h($row['student_medical_condition'] ?? '') ?></div></div>
        <div class="col col-2"><div class="label">Complication Type</div><div class="value"><?= !empty($row['student_medical_condition']) ? h($row['student_medical_condition']) : 'No record provided' ?></div></div>
      </div>
      <div class="row">
        <div class="col col-2"><div class="label">Blood Group</div><div class="value"><?= h($row['student_bloodtype'] ?? '') ?></div></div>
        <div class="col col-2"><div class="label">Allergies</div><div class="value"><?= !empty($row['student_allergies']) ? h($row['student_allergies']) : 'No record provided' ?></div></div>
      </div>
    </div>

    <div class="card">
      <h3>Personal Information</h3>
      <div class="row">
        <div class="col col-3"><div class="label">Boxlunch Preference</div><div class="value"><?= h($row['student_school_feeding_option'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Social Welfare</div><div class="value"><?= h($row['student_social_welfare_status'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Mode of Transport</div><div class="value"><?= h($row['student_mode_of_transport'] ?? '') ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Immunized</div><div class="value"><?= h($row['student_immunization_status'] ?? '') ?></div></div>
        <div class="col col-3"><div class="label">Continuous Access to Device</div><div class="value"><?= h($row['student_access_to_device'] ?? '') ?></div></div>
      </div>
    </div>

    <div class="card">
      <h3>Parent/Guardian Information (Mother)</h3>
      <div class="row">
        <div class="col col-3"><div class="label">Mother's Name</div><div class="value"><?= h(ucwords(strtolower($row['mother_name'] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Status</div><div class="value"><?= (($row['is_mother_active_or_deceased'] ?? '') === 'Deceased') ? 'Deceased' : 'Alive' ?></div></div>
        <div class="col col-3"><div class="label">Identification</div><div class="value"><?= !empty($row['mother_identification_number']) ? '(' . h($row['mother_identification_type'] ?? '') . ') ' . h($row['mother_identification_number']) : 'No record provided' ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Home Address</div><div class="value"><?= h(ucwords(strtolower($row['mother_home_address'] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Contact</div><div class="value"><?= h($row['mother_contact'] ?? 'No record provided') ?></div></div>
        <div class="col col-3"><div class="label">Profession</div><div class="value"><?= h(ucwords(strtolower($row['mother_profession'] ?? 'No record provided'))) ?></div></div>
      </div>
      <?php $mWorkAddrCol="Mother's Work Address"; $mEmailCol="Mother's Email Address"; ?>
      <div class="row">
        <div class="col col-3"><div class="label">Work Address</div><div class="value"><?= h(ucwords(strtolower($row[$mWorkAddrCol] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Email Address</div><div class="value"><?= h($row[$mEmailCol] ?? 'No record provided') ?></div></div>
      </div>
    </div>

    <div class="card">
      <h3>Parent/Guardian Information (Father)</h3>
      <div class="row">
        <div class="col col-3"><div class="label">Father's Name</div><div class="value"><?= h(ucwords(strtolower($row['father_name'] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Status</div><div class="value"><?= (($row['is_father_active_or_deceased'] ?? '') === 'Deceased') ? 'Deceased' : 'Alive' ?></div></div>
        <div class="col col-3"><div class="label">Identification</div><div class="value"><?= !empty($row['father_identification_number']) ? '(' . h($row['father_identification_type'] ?? '') . ') ' . h($row['father_identification_number']) : 'No record provided' ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Home Address</div><div class="value"><?= h(ucwords(strtolower($row['father_home_address'] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Contact</div><div class="value"><?= h($row['father_contact'] ?? 'No record provided') ?></div></div>
        <div class="col col-3"><div class="label">Profession</div><div class="value"><?= h(ucwords(strtolower($row['father_profession'] ?? 'No record provided'))) ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Work Address</div><div class="value"><?= h(ucwords(strtolower($row['father_work_address'] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Email Address</div><div class="value"><?= h($row['father_email_address'] ?? 'No record provided') ?></div></div>
      </div>
    </div>

    <div class="card">
      <h3>Emergency Contact Information</h3>
      <div class="row">
        <div class="col col-3"><div class="label">Contact Name</div><div class="value"><?= h(ucwords(strtolower($row['emergency_contact_name'] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Relation</div><div class="value"><?= h(ucwords(strtolower($row['emergency_contact_relation_to_student'] ?? 'No record provided'))) ?></div></div>
        <div class="col col-3"><div class="label">Contact No.</div><div class="value"><?= h($row['emergency_contact_number'] ?? 'No record provided') ?></div></div>
      </div>
      <div class="row">
        <div class="col col-1"><div class="label">Address</div><div class="value"><?= h(ucwords(strtolower($row['emergency_contact_address'] ?? 'No record provided'))) ?></div></div>
      </div>
    </div>

    <div class="card">
      <h3>Registrant Information</h3>
      <div class="row">
        <div class="col col-3"><div class="label">Date of Registration</div><div class="value"><?= h(showDateDMY($row['registration_date'] ?? null)) ?></div></div>
        <?php
          $rel = $row['registrant_relationship_to_student'] ?? '';
          if ($rel === 'Mother') {
            $regName = ucwords(strtolower($row['mother_name'] ?? ''));
            $idText  = '(' . h($row['mother_identification_type'] ?? '') . ') ' . h($row['mother_identification_number'] ?? '');
            $prof = $row['mother_profession'] ?? ''; $addr = $row['mother_home_address'] ?? ''; $ctct = $row['mother_contact'] ?? '';
          } elseif ($rel === 'Father') {
            $regName = ucwords(strtolower($row['father_name'] ?? ''));
            $idText  = '(' . h($row['father_identification_type'] ?? '') . ') ' . h($row['father_identification_number'] ?? '');
            $prof = $row['father_profession'] ?? ''; $addr = $row['father_home_address'] ?? ''; $ctct = $row['father_contact'] ?? '';
          } elseif ($rel === 'Other') {
            $otherNameCol = 'Name of Registrant';
            $regName = ucwords(strtolower($row[$otherNameCol] ?? ''));
            $idText  = '(' . h($row['registrant_identification_type'] ?? '') . ') ' . h($row['registrant_identification_number'] ?? '');
            $prof = ''; $addr = ''; $ctct = '';
          } else {
            $regName = 'No record provided';
            $idText  = 'No record provided';
            $prof = $addr = $ctct = '';
          }
        ?>
        <div class="col col-3"><div class="label">Registrant (<?= h($rel ?: 'Unknown') ?>)</div><div class="value"><?= h($regName) ?></div></div>
        <div class="col col-3"><div class="label">Identification</div><div class="value"><?= h($idText) ?></div></div>
      </div>
      <div class="row">
        <div class="col col-3"><div class="label">Profession</div><div class="value"><?= $prof ? h(ucwords(strtolower($prof))) : 'No record provided' ?></div></div>
        <div class="col col-3"><div class="label">Home Address</div><div class="value"><?= $addr ? h(ucwords(strtolower($addr))) : 'No record provided' ?></div></div>
        <div class="col col-3"><div class="label">Contact</div><div class="value"><?= $ctct ? h($ctct) : 'No record provided' ?></div></div>
      </div>
    </div>

  </div>
</section>
<?php endforeach; ?>
</body>
</html>
<?php
$html = ob_get_clean();

/* ----------- Dompdf options ----------- */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('dpi', 96);
$options->set('isFontSubsettingEnabled', true);
$options->set('fontCache', __DIR__ . '/storage/dompdf_font_cache');
$options->set('tempDir',   __DIR__ . '/storage/dompdf_tmp');
if (!is_dir(__DIR__ . '/storage/dompdf_font_cache')) @mkdir(__DIR__ . '/storage/dompdf_font_cache', 0775, true);
if (!is_dir(__DIR__ . '/storage/dompdf_tmp'))        @mkdir(__DIR__ . '/storage/dompdf_tmp', 0775, true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->setPaper('letter', 'portrait');
$dompdf->loadHtml($html);
$dompdf->render();

/* filename reflects filters */
$fname = 'student-profiles';
if ($selectedYear)  $fname .= "-$selectedYear";
if ($selectedClass && $selectedClass !== '0') $fname .= "-$selectedClass";
if ($selectedName && $selectedName !== '0')   $fname .= "-" . preg_replace('~\s+~','_',$selectedName);

$dompdf->stream($fname . '.pdf', ['Attachment' => false]);
