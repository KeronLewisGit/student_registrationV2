<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

class ImportOldStudentData extends Command
{
    protected $signature = 'students:import-old-data
                            {--file= : Path to a MySQL dump (.sql) of the legacy student-portal database; when omitted, reads the student_registration_data table in the current database}
                            {--dry-run : Report what would be imported without writing anything}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Import students from the legacy student_registration_data table (or a MySQL dump of it) into the students table, skipping ones already present';

    /**
     * Legacy column => students column, for the columns whose names differ.
     */
    private const COLUMN_RENAMES = [
        'student_birth_certficate_pin'  => 'student_birth_certificate_pin',
        'student_recieving_counselling' => 'student_receiving_counselling',
        'student_physical_disibilities' => 'student_physical_disabilities',
    ];

    private const DATE_COLUMNS = [
        'student_dob', 'student_sea_date', 'student_transfer_date', 'registration_date',
    ];

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $file = $this->option('file');

        $this->info('Starting data migration from old system...');

        $oldStudents = $file !== null
            ? $this->rowsFromDump($file)
            : $this->rowsFromTable();

        if ($oldStudents === null) {
            return 1;
        }

        $oldCount = count($oldStudents);
        $this->info("Found {$oldCount} students in the legacy data.");

        if ($oldCount === 0) {
            $this->warn('No students to import.');
            return 0;
        }

        $existingCount = Student::withTrashed()->count();
        $this->info("The students table currently holds {$existingCount} records (including soft-deleted).");

        if ($dryRun) {
            $this->warn('Dry run: nothing will be written.');
        } elseif (!$this->option('force') && !$this->confirm('Do you want to proceed with the import?', true)) {
            $this->warn('Import cancelled.');
            return 0;
        }

        // Index existing students so each legacy row can be matched without a query.
        $byPin = [];
        $byNameDob = [];
        foreach (Student::withTrashed()->get(['id', 'student_name', 'student_dob', 'student_birth_certificate_pin']) as $student) {
            $pin = $this->normalizePin($student->student_birth_certificate_pin);
            if ($pin !== null) {
                $byPin[$pin] = $student->id;
            }
            $byNameDob[$this->nameDobKey($student->student_name, optional($student->student_dob)->format('Y-m-d'))] = $student->id;
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $skippedRows = [];
        $importedRows = [];

        $this->info($dryRun ? 'Checking students...' : 'Importing students...');
        $bar = $this->output->createProgressBar($oldCount);
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($oldStudents as $oldStudent) {
                $attributes = $this->mapRow($oldStudent);
                $pin = $attributes['student_birth_certificate_pin'];
                $nameDob = $this->nameDobKey($attributes['student_name'], $attributes['student_dob']);

                $matchedId = ($pin !== null ? ($byPin[$pin] ?? null) : null) ?? ($byNameDob[$nameDob] ?? null);

                if ($matchedId !== null) {
                    $skipped++;
                    $skippedRows[] = [$oldStudent->id, $attributes['student_name'], $pin ?? '', "already #{$matchedId}"];
                    $bar->advance();
                    continue;
                }

                try {
                    $newId = null;

                    if (!$dryRun) {
                        $newId = Student::create($attributes)->id;
                    }

                    // Track within-run duplicates too (e.g. the same child submitted twice).
                    if ($pin !== null) {
                        $byPin[$pin] = $newId ?? 'new';
                    }
                    $byNameDob[$nameDob] = $newId ?? 'new';

                    $imported++;
                    $importedRows[] = [$oldStudent->id, $attributes['student_name'], $attributes['form_1_class'] ?? '', $pin ?? ''];
                } catch (\Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("Error importing legacy #{$oldStudent->id} {$attributes['student_name']}: " . $e->getMessage());
                }

                $bar->advance();
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->newLine();
            $this->error('Import aborted, nothing was written: ' . $e->getMessage());
            return 1;
        }

        $bar->finish();
        $this->newLine(2);

        if ($this->output->isVerbose()) {
            if ($importedRows) {
                $this->info($dryRun ? 'Would import:' : 'Imported:');
                $this->table(['Legacy ID', 'Name', 'Class', 'PIN'], $importedRows);
            }
            if ($skippedRows) {
                $this->warn('Skipped (already present):');
                $this->table(['Legacy ID', 'Name', 'PIN', 'Matched'], $skippedRows);
            }
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info($dryRun ? 'Dry-run Summary:' : 'Import Summary:');
        $this->info("  ✓ " . ($dryRun ? 'Would import' : 'Imported') . ": {$imported} students");
        $this->warn("  ⊘ Skipped (already present): {$skipped}");
        if ($errors > 0) {
            $this->error("  ✗ Errors: {$errors}");
        }
        $this->info("  Σ Students table now: " . Student::withTrashed()->count());
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($file === null && !$dryRun && !$this->option('force')
            && $this->confirm('Do you want to drop the old student_registration_data table?', false)) {
            DB::statement('DROP TABLE student_registration_data');
            $this->info('Old table dropped successfully.');
        }

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Read legacy rows from the student_registration_data table in the current database.
     *
     * @return array<int, object>|null
     */
    private function rowsFromTable(): ?array
    {
        if (!DB::getSchemaBuilder()->hasTable('student_registration_data')) {
            $this->error('Old table student_registration_data not found!');
            $this->info('Either import the SQL file into this database first, or pass it directly:');
            $this->line('  php artisan students:import-old-data --file=/path/to/student-portal.sql');
            return null;
        }

        return DB::table('student_registration_data')->orderBy('id')->get()->all();
    }

    /**
     * Read legacy rows straight out of a MySQL dump file, so the dump never has
     * to be loaded into the application database (which may be SQLite).
     *
     * @return array<int, object>|null
     */
    private function rowsFromDump(string $path): ?array
    {
        if (!is_readable($path)) {
            $this->error("Cannot read dump file: {$path}");
            return null;
        }

        $sql = file_get_contents($path);
        $rows = [];
        $offset = 0;

        // phpMyAdmin dumps split the table across several INSERT statements.
        while (preg_match(
            '/INSERT INTO `student_registration_data`\s*\(([^)]*)\)\s*VALUES\s*/i',
            $sql,
            $m,
            PREG_OFFSET_CAPTURE,
            $offset
        )) {
            $columns = array_map(fn ($c) => trim($c, " `\r\n"), explode(',', $m[1][0]));
            $pos = $m[0][1] + strlen($m[0][0]);

            foreach ($this->parseTuples($sql, $pos) as $values) {
                if (count($values) !== count($columns)) {
                    $this->warn("\nSkipping malformed row near byte {$pos}: expected " . count($columns) . ' values, got ' . count($values));
                    continue;
                }
                $rows[] = (object) array_combine($columns, $values);
            }

            $offset = $pos;
        }

        if (!$rows) {
            $this->error('No student_registration_data rows found in the dump.');
            return null;
        }

        usort($rows, fn ($a, $b) => (int) $a->id <=> (int) $b->id);

        $this->info('Read ' . count($rows) . " rows from {$path}");

        return $rows;
    }

    /**
     * Parse the "(...), (...), ...;" tuple list of a MySQL INSERT statement.
     * Advances $pos to just past the terminating semicolon.
     *
     * @return array<int, array<int, string|null>>
     */
    private function parseTuples(string $sql, int &$pos): array
    {
        $tuples = [];
        $len = strlen($sql);

        while ($pos < $len) {
            // Skip whitespace and separators between tuples.
            while ($pos < $len && (ctype_space($sql[$pos]) || $sql[$pos] === ',')) {
                $pos++;
            }

            if ($pos >= $len || $sql[$pos] === ';') {
                $pos++;
                break;
            }

            if ($sql[$pos] !== '(') {
                throw new \RuntimeException("Unexpected '{$sql[$pos]}' at byte {$pos} while parsing dump");
            }
            $pos++; // (

            $values = [];
            while (true) {
                while ($pos < $len && ctype_space($sql[$pos])) {
                    $pos++;
                }

                $ch = $sql[$pos];

                if ($ch === "'") {
                    $pos++;
                    $buf = '';
                    while ($pos < $len) {
                        $c = $sql[$pos];
                        if ($c === '\\') {
                            $next = $sql[$pos + 1];
                            $buf .= match ($next) {
                                'n' => "\n", 'r' => "\r", 't' => "\t", '0' => "\0",
                                'Z' => "\x1a", 'b' => "\x08",
                                default => $next,
                            };
                            $pos += 2;
                        } elseif ($c === "'") {
                            if (($sql[$pos + 1] ?? '') === "'") { // doubled quote
                                $buf .= "'";
                                $pos += 2;
                            } else {
                                $pos++;
                                break;
                            }
                        } else {
                            $buf .= $c;
                            $pos++;
                        }
                    }
                    $values[] = $buf;
                } else {
                    $start = $pos;
                    while ($pos < $len && $sql[$pos] !== ',' && $sql[$pos] !== ')') {
                        $pos++;
                    }
                    $raw = trim(substr($sql, $start, $pos - $start));
                    $values[] = strcasecmp($raw, 'NULL') === 0 ? null : $raw;
                }

                while ($pos < $len && ctype_space($sql[$pos])) {
                    $pos++;
                }

                if ($sql[$pos] === ',') {
                    $pos++;
                    continue;
                }
                if ($sql[$pos] === ')') {
                    $pos++;
                    break;
                }
                throw new \RuntimeException("Unexpected '{$sql[$pos]}' at byte {$pos} while parsing dump");
            }

            $tuples[] = $values;
        }

        return $tuples;
    }

    /**
     * Map a legacy row onto students-table attributes, fixing renamed columns
     * and cleaning legacy placeholder values.
     *
     * @return array<string, mixed>
     */
    private function mapRow(object $old): array
    {
        $fillable = array_flip((new Student)->getFillable());
        $attributes = [];

        foreach ((array) $old as $column => $value) {
            $column = self::COLUMN_RENAMES[$column] ?? $column;

            if (!isset($fillable[$column])) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);

                // Elementor leaves its dropdown placeholder ("Select", "Select Gender",
                // "Select Religion", ...) in the column when nothing was chosen.
                if (preg_match('/^select(?: [a-z]+)?$/i', $value)) {
                    $value = null;
                }
            }

            if (in_array($column, self::DATE_COLUMNS, true)) {
                $value = $this->normalizeDate($value);
            }

            $attributes[$column] = $value;
        }

        $attributes['student_birth_certificate_pin'] = $this->normalizePin($attributes['student_birth_certificate_pin'] ?? null);
        $attributes['form_1_class'] = Student::canonicalClass($attributes['form_1_class'] ?? null) ?? ($attributes['form_1_class'] ?: null);

        // These columns are NOT NULL in the students table.
        foreach ([
            'student_family_crisis', 'student_receiving_counselling', 'student_physical_disabilities',
            'student_learning_disabilities', 'student_educational_aid', 'student_special_sea_concessions',
            'student_emotional_factors', 'student_other_intervention_information',
        ] as $required) {
            $attributes[$required] = $attributes[$required] ?? '';
        }

        return $attributes;
    }

    /**
     * MySQL's zero date and blanks become null; everything else is kept as Y-m-d.
     */
    private function normalizeDate($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        return substr($value, 0, 10);
    }

    /**
     * Normalize a legacy birth-certificate PIN: strip punctuation, uppercase,
     * and convert placeholder values ("N/A") to null so they don't trip the
     * unique constraint on the column.
     */
    private function normalizePin(?string $pin): ?string
    {
        $pin = preg_replace('/[^0-9A-Z]/', '', strtoupper(trim((string) $pin)));

        // A PIN always carries digits; anything else ("N/A", "None", a name typed
        // into the wrong box) is treated as missing.
        return preg_match('/[0-9]/', $pin) ? $pin : null;
    }

    private function nameDobKey(?string $name, ?string $dob): string
    {
        $name = preg_replace('/\s+/', ' ', strtolower(trim((string) $name)));

        return $name . '|' . ($dob ?: '');
    }
}
