<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Copies photos and documents that still live on the old WordPress site into
 * this app's own storage, so the records no longer depend on another host.
 */
class LocalizeStudentDocuments extends Command
{
    protected $signature = 'students:localize-documents
                            {--dry-run : Show what would be downloaded without changing anything}
                            {--clear-dead : Blank out links whose file no longer exists (HTTP 404/410)}
                            {--limit=0 : Stop after this many downloads (0 = no limit)}';

    protected $description = 'Download externally hosted student photos and documents into local storage and update the records';

    /**
     * Column => storage directory.
     */
    private const FIELDS = [
        'student_passport_photo' => 'passports',
        'student_birth_certificate' => 'birth_certificates',
        'student_sea_slip' => 'sea_slips',
        'student_transfer_slip' => 'transfer_slips',
        'mother_death_certificate' => 'death_certificates',
        'father_death_certificate' => 'death_certificates',
    ];

    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
        'application/pdf' => 'pdf',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $clearDead = (bool) $this->option('clear-dead');
        $limit = (int) $this->option('limit');

        $students = Student::withTrashed()->orderBy('id')->get();
        $todo = [];

        foreach ($students as $student) {
            foreach (self::FIELDS as $field => $dir) {
                $value = trim((string) $student->{$field});
                if (!preg_match('#^https?://#i', $value)) {
                    continue;
                }
                // Only the school's own legacy site is fetched; anything else is a stray link
                $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                if (!in_array($host, Student::allowedDocumentHosts(), true)) {
                    $this->line("  - #{$student->id} {$field}: skipped link on {$host} (not an allowed host)");
                    continue;
                }
                $todo[] = [$student, $field, $dir, $value];
            }
        }

        $this->info(count($todo) . ' external file link(s) found across ' . $students->count() . ' students.');
        if (!$todo) {
            return 0;
        }
        if ($dryRun) {
            $this->warn('Dry run: nothing will be downloaded or changed.');
        }

        $done = $failed = $dead = $skipped = 0;
        $bar = $this->output->createProgressBar(count($todo));
        $bar->start();

        foreach ($todo as [$student, $field, $dir, $url]) {
            if ($limit && $done >= $limit) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if ($dryRun) {
                $done++;
                $bar->advance();
                continue;
            }

            try {
                $response = Http::timeout(20)->withOptions(['verify' => true, 'allow_redirects' => false])->get($url);
            } catch (\Throwable $e) {
                $failed++;
                $this->line("\n  ✗ #{$student->id} {$field}: " . $e->getMessage());
                $bar->advance();
                continue;
            }

            if (in_array($response->status(), [404, 410], true)) {
                $dead++;
                if ($clearDead) {
                    $student->update([$field => null]);
                    StudentActivity::record($student, 'document', Student::fieldLabel($field) . ' link removed (file no longer exists at ' . $url . ')');
                }
                $bar->advance();
                continue;
            }

            $type = strtolower(explode(';', $response->header('Content-Type') ?? '')[0]);
            if (!$response->successful() || !isset(self::ALLOWED_TYPES[$type])) {
                $failed++;
                $this->line("\n  ✗ #{$student->id} {$field}: HTTP {$response->status()} " . ($type ?: 'unknown type'));
                $bar->advance();
                continue;
            }

            $ext = self::ALLOWED_TYPES[$type];
            $filename = "{$field}_{$student->id}_" . Str::random(12) . ".{$ext}";
            Storage::disk('local')->put("private/{$dir}/{$filename}", $response->body());

            $student->update([$field => "private/{$dir}/{$filename}"]);
            StudentActivity::record($student, 'document', Student::fieldLabel($field) . ' copied into local storage from ' . parse_url($url, PHP_URL_HOST));
            $done++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info(($dryRun ? 'Would download' : 'Downloaded') . ": {$done}");
        if ($dead) {
            $this->warn("Dead links (404/410): {$dead}" . ($clearDead ? ' — cleared' : ' — left as-is; re-run with --clear-dead to blank them'));
        }
        if ($failed) {
            $this->error("Failed: {$failed}");
        }
        if ($skipped) {
            $this->line("Not attempted (limit reached): {$skipped}");
        }

        return $failed ? 1 : 0;
    }
}
