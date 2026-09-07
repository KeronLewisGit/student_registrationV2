<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Moves photos and documents out of the web-served storage/app/public folder
 * into private storage, so they can only be fetched through the app's
 * authenticated /documents route.
 */
class SecureStudentDocuments extends Command
{
    protected $signature = 'students:secure-documents
                            {--dry-run : Show what would be moved without changing anything}';

    protected $description = 'Move student photos and documents from public storage into private storage and update the records';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $students = Student::withTrashed()->orderBy('id')->get();

        $moved = $missing = 0;

        foreach ($students as $student) {
            foreach (Student::FILE_FIELDS as $field) {
                $value = trim((string) $student->{$field});

                if (!str_starts_with($value, 'storage/')) {
                    continue;
                }

                $source = storage_path('app/public/' . substr($value, strlen('storage/')));
                if (!is_file($source)) {
                    $missing++;
                    $this->line("  ! #{$student->id} {$field}: file not found ({$value})");
                    continue;
                }

                $dir = Student::FILE_DIRECTORIES[$field];
                $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'bin';
                $target = "private/{$dir}/{$field}_{$student->id}_" . Str::random(12) . ".{$ext}";
                $absoluteTarget = storage_path('app/' . $target);

                if ($dryRun) {
                    $this->line("  #{$student->id} {$field}: {$value} -> {$target}");
                    $moved++;
                    continue;
                }

                if (!is_dir(dirname($absoluteTarget))) {
                    mkdir(dirname($absoluteTarget), 0755, true);
                }

                if (!rename($source, $absoluteTarget)) {
                    $this->error("  ✗ #{$student->id} {$field}: could not move {$source}");
                    continue;
                }

                Student::withoutEvents(fn () => $student->update([$field => $target]));
                StudentActivity::record($student, 'document', Student::fieldLabel($field) . ' moved into private storage');
                $moved++;
            }
        }

        $this->info(($dryRun ? 'Would move' : 'Moved') . ": {$moved} file(s)");
        if ($missing) {
            $this->warn("Records pointing at files that no longer exist: {$missing} (left unchanged)");
        }

        return 0;
    }
}
