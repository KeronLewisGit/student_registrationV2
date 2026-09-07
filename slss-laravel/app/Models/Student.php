<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory, SoftDeletes, \Illuminate\Database\Eloquent\Prunable;

    /** Soft-deleted records are permanently removed after this long (model:prune). */
    public const TRASH_RETENTION_DAYS = 365;

    public function prunable()
    {
        return static::onlyTrashed()->where('deleted_at', '<', now()->subDays(self::TRASH_RETENTION_DAYS));
    }

    /**
     * The valid Form 1 class values.
     *
     * @var array<int, string>
     */
    public const FORM_CLASSES = ['1A', '1B', '1C', '1D', '1E', '1F'];

    /** Stream letters used for every form. */
    public const STREAMS = ['A', 'B', 'C', 'D', 'E', 'F'];

    /** Highest form taught. */
    public const MAX_FORM = 6;

    /** Enrolment status codes and their labels. */
    public const ENROLMENT_STATUSES = [
        'active'      => 'Active',
        'left'        => 'Left school',
        'graduated'   => 'Graduated',
        'transferred' => 'Transferred out',
    ];

    /**
     * Fields that make up an "essential" record. A student missing any of
     * these shows up in the outstanding-items report. Each entry is a label
     * plus a check; a check may look at more than one column.
     */
    public const ESSENTIAL_ITEMS = [
        'photo'      => ['label' => 'Passport photo',            'fields' => ['student_passport_photo']],
        'dob'        => ['label' => 'Date of birth',             'fields' => ['student_dob']],
        'gender'     => ['label' => 'Gender',                    'fields' => ['student_gender']],
        'pin'        => ['label' => 'Birth certificate PIN',     'fields' => ['student_birth_certificate_pin']],
        'birth_cert' => ['label' => 'Birth certificate copy',    'fields' => ['student_birth_certificate']],
        'address'    => ['label' => 'Current address',           'fields' => ['student_current_address']],
        'class'      => ['label' => 'Current class',             'fields' => ['current_class']],
        'sea'        => ['label' => 'SEA number',                'fields' => ['student_sea_number']],
        'parent'     => ['label' => 'Parent/guardian contact',   'fields' => ['mother_contact', 'father_contact'], 'any' => true],
        'emergency'  => ['label' => 'Emergency contact',         'fields' => ['emergency_contact_name', 'emergency_contact_number']],
        'medical'    => ['label' => 'Medical information',       'fields' => ['student_bloodtype', 'student_allergies', 'student_medical_condition'], 'any' => true],
    ];

    /**
     * Every data field counted towards the completeness percentage.
     */
    public const TRACKED_FIELDS = [
        'form_1_class', 'current_class', 'student_name', 'student_gender', 'citizen_type', 'student_current_address',
        'student_dob', 'student_birth_certificate', 'student_birth_certificate_pin', 'student_religion',
        'student_country_of_birth', 'student_nationality', 'student_ethnicity', 'student_contact', 'student_email',
        'student_passport_photo', 'student_sea_date', 'student_primary_school', 'student_sea_number',
        'student_transfer_status', 'student_medical_condition', 'student_bloodtype', 'student_allergies',
        'student_immunization_status', 'student_school_feeding_option', 'student_social_welfare_status',
        'student_mode_of_transport', 'student_access_to_device', 'mother_name', 'is_mother_active_or_deceased',
        'mother_identification_type', 'mother_identification_number', 'mother_home_address', 'mother_contact',
        'mother_profession', 'mother_email', 'father_name', 'is_father_active_or_deceased',
        'father_identification_type', 'father_identification_number', 'father_home_address', 'father_contact',
        'father_profession', 'father_email_address', 'emergency_contact_name', 'emergency_contact_address',
        'emergency_contact_relation_to_student', 'emergency_contact_number', 'registration_date',
        'registrant_relationship_to_student', 'registrant_name', 'registrant_identification_type',
        'registrant_identification_number', 'registrant_nationality', 'registrant_email',
    ];

    /**
     * Human label for a column name ("mother_contact" -> "Mother contact").
     */
    public static function fieldLabel(string $field): string
    {
        static $special = [
            'student_dob' => 'Date of birth',
            'student_birth_certificate_pin' => 'Birth certificate PIN',
            'student_passport_photo' => 'Passport photo',
            'form_1_class' => 'Form 1 class',
            'current_class' => 'Current class',
            'intake_year' => 'Intake year',
            'enrolment_status' => 'Enrolment status',
            'is_mother_active_or_deceased' => 'Mother status',
            'is_father_active_or_deceased' => 'Father status',
            'student_sea_number' => 'SEA number',
            'student_sea_date' => 'SEA date',
            'student_sea_slip' => 'SEA slip',
            'student_bloodtype' => 'Blood type',
        ];

        if (isset($special[$field])) {
            return $special[$field];
        }

        $label = preg_replace('/^(student|registrant)_/', '', $field);

        return ucfirst(str_replace('_', ' ', $label));
    }

    /**
     * The academic year that is running now, as its starting calendar year
     * (September 2026 to August 2027 is 2026).
     */
    public static function currentAcademicYear(?Carbon $on = null): int
    {
        $on = $on ?? now();

        return (int) $on->format('Y') - ($on->month < 9 ? 1 : 0);
    }

    /**
     * The academic year a year-end promotion moves students INTO: the one
     * starting this coming September (or the one that just started, when the
     * promotion is run late, from September onwards).
     */
    public static function promotionTargetYear(?Carbon $on = null): int
    {
        $on = $on ?? now();

        return $on->month >= 9 ? self::currentAcademicYear($on) : self::currentAcademicYear($on) + 1;
    }

    public static function academicYearLabel(?int $start = null): string
    {
        $start = $start ?? self::currentAcademicYear();

        return $start . '/' . ($start + 1);
    }

    /**
     * All class codes for every form: 1A ... 6F.
     *
     * @return array<int, string>
     */
    public static function allClasses(): array
    {
        $classes = [];
        for ($form = 1; $form <= self::MAX_FORM; $form++) {
            foreach (self::STREAMS as $stream) {
                $classes[] = $form . $stream;
            }
        }

        return $classes;
    }

    /**
     * Stream letter of the intake class ("1C" / "C" / "Form 1c" -> "C").
     */
    public function stream(): ?string
    {
        $canonical = self::canonicalClass($this->form_1_class);

        return $canonical ? substr($canonical, -1) : null;
    }

    /**
     * Form number the student would be in this academic year, from intake year.
     */
    public function expectedForm(): ?int
    {
        if (!$this->intake_year) {
            return null;
        }

        return min(self::MAX_FORM, max(1, self::currentAcademicYear() - $this->intake_year + 1));
    }

    /**
     * Form number of the current class ("3C" -> 3).
     */
    public function currentForm(): ?int
    {
        return preg_match('/^(\d)/', (string) $this->current_class, $m) ? (int) $m[1] : null;
    }

    /**
     * The class this student moves to at the year-end promotion, or null when
     * they are in the top form (and should be marked graduated instead).
     */
    public function nextClass(): ?string
    {
        $form = $this->currentForm();
        if (!$form || $form >= self::MAX_FORM || !preg_match('/^[1-6][A-F]$/', (string) $this->current_class)) {
            return null;
        }

        return ($form + 1) . substr($this->current_class, 1);
    }

    public function getEnrolmentStatusLabelAttribute(): string
    {
        return self::ENROLMENT_STATUSES[$this->enrolment_status] ?? ucfirst((string) $this->enrolment_status);
    }

    public function isActive(): bool
    {
        return ($this->enrolment_status ?? 'active') === 'active';
    }

    /**
     * How complete this record is.
     *
     * @return array{percent:int, recorded:int, total:int, missing:array<string,string>}
     *         missing is keyed by ESSENTIAL_ITEMS key => label
     */
    public function completeness(): array
    {
        $recorded = 0;
        foreach (self::TRACKED_FIELDS as $field) {
            if (self::hasValue($this->{$field})) {
                $recorded++;
            }
        }

        $missing = [];
        foreach (self::ESSENTIAL_ITEMS as $key => $item) {
            $present = array_map(fn ($f) => self::hasValue($this->{$f}), $item['fields']);
            $ok = ($item['any'] ?? false) ? in_array(true, $present, true) : !in_array(false, $present, true);
            if (!$ok) {
                $missing[$key] = $item['label'];
            }
        }

        $total = count(self::TRACKED_FIELDS);

        return [
            'percent' => (int) round($recorded / $total * 100),
            'recorded' => $recorded,
            'total' => $total,
            'missing' => $missing,
        ];
    }

    public function isComplete(): bool
    {
        return $this->completeness()['missing'] === [];
    }

    /**
     * True when a stored value is real data rather than blank or a legacy placeholder.
     */
    public static function hasValue($value): bool
    {
        if ($value === null) {
            return false;
        }
        if ($value instanceof \DateTimeInterface) {
            return (int) $value->format('Y') > 1900;
        }
        $text = trim((string) $value);

        return $text !== '' && !self::isPlaceholder($text);
    }

    public function activities()
    {
        return $this->hasMany(StudentActivity::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Build the list of stored values that are equivalent to a filter class.
     *
     * The form_1_class column holds several historical formats: legacy imports
     * wrote the bare stream ("A"), newer ones write the full class ("1A"), and
     * hand-entered rows may carry spacing or lower case ("form 1a", "1 a").
     * The filter always presents the canonical "1A" form, so map it back to
     * every variant that means the same class.
     *
     * @param  string  $class
     * @return array<int, string>
     */
    public static function classVariants($class): array
    {
        $normalized = strtoupper(trim((string) $class));

        // Reduce "FORM 1A", "1 A", "F1A" and friends down to the bare stream letter.
        $stream = preg_replace('/[^A-Z0-9]/', '', $normalized);

        // A bare stream letter ("F") would otherwise be consumed as the "Form"
        // prefix by the regex below and produce no variants.
        if (!preg_match('/^[A-Z]$/', $stream)) {
            $stream = preg_replace('/^(?:FORM|F)?1?/', '', $stream, 1);
        }

        if ($stream === '' || $stream === null) {
            return array_values(array_filter([$normalized]));
        }

        return array_values(array_unique([
            '1' . $stream,   // 1A
            $stream,         // A
            '1 ' . $stream,  // 1 A
            'Form 1' . $stream,
            'F1' . $stream,  // F1A
        ]));
    }

    /**
     * Map any stored form_1_class variant ("A", "1 a", "Form 1A") to its
     * canonical FORM_CLASSES value, or null when it isn't recognizable.
     */
    public static function canonicalClass($value): ?string
    {
        $normalized = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $value)));

        if ($normalized === '') {
            return null;
        }

        // Bare stream letter ("A") — don't let the prefix regex eat "F".
        $stream = preg_match('/^[A-Z]$/', $normalized)
            ? $normalized
            : preg_replace('/^(?:FORM|F)?1?/', '', $normalized, 1);

        $canonical = '1' . $stream;

        return in_array($canonical, self::FORM_CLASSES, true) ? $canonical : null;
    }

    protected $fillable = [
        // Enrolment / progression
        'intake_year', 'current_class', 'enrolment_status', 'status_changed_at', 'status_note',

        // Student Basic Information
        'form_1_class', 'student_name', 'student_gender', 'citizen_type',
        'student_current_address', 'student_dob', 'student_birth_certificate',
        'student_birth_certificate_pin', 'student_religion', 'student_country_of_birth',
        'student_nationality', 'student_ethnicity', 'student_contact', 'student_email',
        'student_passport_photo',

        // SEA Information
        'student_sea_date', 'student_primary_school', 'student_sea_slip', 'student_sea_number',

        // Transfer Information
        'student_transfer_status', 'student_transfer_slip', 'student_transfer_reason',
        'student_transfer_date', 'student_previous_form_class',
        'student_previous_secondary_school', 'student_previous_school_location',

        // Medical Information
        'student_medical_condition', 'student_bloodtype', 'student_allergies',
        'student_immunization_status',

        // Special Needs & Intervention
        'student_family_crisis', 'student_receiving_counselling', 'student_physical_disabilities',
        'student_learning_disabilities', 'student_educational_aid', 'student_special_sea_concessions',
        'student_emotional_factors', 'student_other_intervention_information',

        // Personal Preferences
        'student_school_feeding_option', 'student_social_welfare_status', 'student_social_welfare_detail',
        'student_mode_of_transport', 'student_access_to_device', 'student_device_shared',
        'student_reliable_internet', 'student_internet_provider', 'student_online_tools',

        // Mother Information
        'mother_name', 'is_mother_active_or_deceased', 'mother_death_certificate', 'mother_identification_type',
        'mother_identification_number', 'mother_home_address', 'mother_contact',
        'mother_profession', 'mother_work_address', 'mother_email',

        // Father Information
        'father_name', 'is_father_active_or_deceased', 'father_death_certificate', 'father_identification_type',
        'father_identification_number', 'father_home_address', 'father_contact',
        'father_profession', 'father_work_address', 'father_email_address',

        // Emergency Contact
        'emergency_contact_name', 'emergency_contact_address',
        'emergency_contact_relation_to_student', 'emergency_contact_number',

        // Registrant Information
        'registration_date', 'registrant_relationship_to_student', 'registrant_name',
        'registrant_identification_type', 'registrant_identification_number',
        'registrant_nationality', 'registrant_email',
    ];

    protected $casts = [
        'status_changed_at' => 'date',
        'student_dob' => 'date',
        'student_sea_date' => 'date',
        'student_transfer_date' => 'date',
        'registration_date' => 'date',
    ];

    // Accessor for formatted date of birth
    public function getFormattedDobAttribute(): string
    {
        return $this->student_dob ? $this->student_dob->format('d/m/Y') : 'No record provided';
    }

    // Accessor for formatted SEA date
    public function getFormattedSeaDateAttribute(): string
    {
        return $this->student_sea_date ? $this->student_sea_date->format('d/m/Y') : 'No record provided';
    }

    // Accessor for formatted registration date
    public function getFormattedRegistrationDateAttribute(): string
    {
        return $this->registration_date ? $this->registration_date->format('d/m/Y') : 'No record provided';
    }

    /**
     * Resolve a stored document value (SEA slip, transfer slip, certificates)
     * to a browsable URL, or null when the value isn't an actual file
     * reference (legacy rows hold plain text like "N/A" or "Yes").
     */
    /**
     * A copy of this student with legacy placeholder values blanked out, for
     * printed documents. Records imported from the old portal carry Elementor
     * dropdown defaults ("Select Blood Type"), strings made only of "N/A"
     * tokens, and 1900-01-01 as an "empty" date; none of those should print
     * as if they were real data. Empty strings become null too, so every
     * missing value falls through to the template's "N/A".
     */
    public function forPrint(): static
    {
        $clean = clone $this;
        $attributes = $this->getAttributes();

        foreach ($attributes as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $trimmed = trim($value);

            if ($trimmed === '' || self::isPlaceholder($trimmed)) {
                $attributes[$key] = null;
            }
        }

        $clean->setRawAttributes($attributes, true);

        return $clean;
    }

    /**
     * Prepare one value for a printed record. Returns null when nothing usable
     * was recorded (null, blank, whitespace, or a legacy placeholder), so the
     * template can print a uniform "Not recorded" marker instead of a gap.
     *
     * @param  mixed        $value
     * @param  string|null  $format  'name' | 'date' | 'document' | 'yesno' | null
     */
    public static function printValue($value, ?string $format = null): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '' || self::isPlaceholder($text)) {
            return null;
        }

        return match ($format) {
            'name'  => ucwords(strtolower($text)),
            'date'  => (($ts = strtotime($text)) !== false) ? date('d/m/Y', $ts) : $text,
            'yesno' => match ($text) { '0' => 'No', '1' => 'Yes', default => $text },
            default => $text,
        };
    }

    /**
     * Printable label for a document field: "On file" when a file is stored,
     * otherwise the cleaned text value (null when nothing is recorded).
     */
    public static function documentLabel($value): ?string
    {
        if (self::documentUrl(is_string($value) ? $value : null)) {
            return 'On file';
        }

        return self::printValue($value);
    }

    /**
     * Legacy "empty" markers that should never be printed as data.
     */
    public static function isPlaceholder(string $text): bool
    {
        return preg_match('/^select(?: [a-z ]+)?$/i', $text)                                   // "Select", "Select Blood Type"
            || preg_match('/^(?:citizenship type|blood type|gender|religion|nationality|ethnicity|relationship)$/i', $text) // a dropdown's own label

            || preg_match('/^(?:n\/?a|none|null|nil|-)(?:[\s,.]+(?:n\/?a|none|null|nil|-))*$/i', $text) // "N/A", "N/a N/a N/a"
            || preg_match('/^(?:1900|0000)-01-01/', $text);                                   // 1900-01-01 "empty" date
    }

    /** Upload field => storage directory. */
    public const FILE_DIRECTORIES = [
        'student_passport_photo' => 'passports',
        'student_birth_certificate' => 'birth_certificates',
        'student_sea_slip' => 'sea_slips',
        'student_transfer_slip' => 'transfer_slips',
        'mother_death_certificate' => 'death_certificates',
        'father_death_certificate' => 'death_certificates',
    ];

    public const FILE_FIELDS = [
        'student_passport_photo', 'student_birth_certificate', 'student_sea_slip',
        'student_transfer_slip', 'mother_death_certificate', 'father_death_certificate',
    ];

    /**
     * Shape of a stored file reference: "private/<dir>/<file>" (private disk)
     * or the legacy "storage/<dir>/<file>" (public disk, until moved).
     */
    public const STORED_FILE_PATTERN = '#^(?<prefix>private|storage)/(?<dir>passports|birth_certificates|sea_slips|transfer_slips|death_certificates)/(?<file>[A-Za-z0-9_.-]+\.(?:pdf|jpe?g|png|gif|webp))$#i';

    /**
     * Hosts whose links may be kept as document references (the old WordPress
     * registration site). Anything else is treated as not recorded.
     */
    public static function allowedDocumentHosts(): array
    {
        return array_filter(array_map('trim', explode(',', (string) config('services.legacy_documents.hosts', 'slss.edu.tt,www.slss.edu.tt'))));
    }

    /**
     * Absolute filesystem path of a stored file reference, or null.
     */
    public static function documentPath(?string $value): ?string
    {
        $value = trim((string) $value);

        if (!preg_match(self::STORED_FILE_PATTERN, $value, $m)) {
            return null;
        }

        // Resolve through the disks so tests (Storage::fake) and custom roots both work
        return strtolower($m['prefix']) === 'private'
            ? \Illuminate\Support\Facades\Storage::disk('local')->path($value)
            : \Illuminate\Support\Facades\Storage::disk('public')->path(substr($value, strlen('storage/')));
    }

    /**
     * Remove a stored file from disk (no-op for links and blanks).
     */
    public static function deleteStoredFile(?string $value): void
    {
        $path = self::documentPath($value);

        if ($path !== null && is_file($path)) {
            @unlink($path);
        }
    }

    public static function documentUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || strcasecmp($value, 'N/A') === 0) {
            return null;
        }

        if (preg_match(self::STORED_FILE_PATTERN, $value)) {
            return route('documents.show', ['path' => $value]);
        }

        if (preg_match('#^https?://#i', $value)) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));

            return in_array($host, self::allowedDocumentHosts(), true) ? $value : null;
        }

        if (str_starts_with($value, 'storage/') && preg_match('/\.(pdf|jpe?g|png|gif|webp)$/i', $value)) {
            return asset($value);
        }

        return null;
    }

    // Accessor for registrant name based on relationship
    public function getRegistrantDisplayNameAttribute(): string
    {
        return match($this->registrant_relationship_to_student) {
            'Mother' => $this->mother_name ? ucwords(strtolower($this->mother_name)) : '',
            'Father' => $this->father_name ? ucwords(strtolower($this->father_name)) : '',
            'Other' => $this->registrant_name ? ucwords(strtolower($this->registrant_name)) : '',
            default => 'No record provided'
        };
    }

    // Accessor for registrant identification based on relationship
    public function getRegistrantDisplayIdAttribute(): array
    {
        return match($this->registrant_relationship_to_student) {
            'Mother' => [
                'type' => $this->mother_identification_type ?? '',
                'number' => $this->mother_identification_number ?? ''
            ],
            'Father' => [
                'type' => $this->father_identification_type ?? '',
                'number' => $this->father_identification_number ?? ''
            ],
            'Other' => [
                'type' => $this->registrant_identification_type ?? '',
                'number' => $this->registrant_identification_number ?? ''
            ],
            default => ['type' => '', 'number' => '']
        };
    }

    // Scope for filtering by year
    public function scopeByYear($query, $year)
    {
        if ($year) {
            return $query->whereYear('registration_date', $year);
        }
        return $query;
    }

    // Scope for filtering by class
    public function scopeByClass($query, $class)
    {
        if ($class && $class !== '0') {
            // Compare on a normalized column (upper case, no spaces/punctuation)
            // so "1A", "1 a" and "Form 1A" all match the same stored rows.
            $variants = array_map(
                fn ($variant) => preg_replace('/[^A-Z0-9]/', '', strtoupper($variant)),
                self::classVariants($class)
            );

            return $query->whereIn(
                DB::raw("UPPER(REPLACE(REPLACE(REPLACE(form_1_class, ' ', ''), '-', ''), '.', ''))"),
                array_values(array_unique($variants))
            );
        }
        return $query;
    }

    // Scope for filtering by the class the student is in now
    public function scopeByCurrentClass($query, $class)
    {
        if ($class && $class !== '0') {
            return $query->where('current_class', strtoupper(trim($class)));
        }
        return $query;
    }

    // Scope for filtering by enrolment status ("all" disables the filter)
    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('enrolment_status', $status);
        }
        return $query;
    }

    // Scope for filtering by name
    public function scopeByName($query, $name)
    {
        if ($name && $name !== '0') {
            return $query->where('student_name', $name);
        }
        return $query;
    }

    /**
     * Columns covered by the search box.
     */
    public const SEARCH_COLUMNS = [
        'student_name', 'student_birth_certificate_pin', 'student_sea_number', 'current_class', 'form_1_class',
        'student_contact', 'student_email', 'student_current_address',
        'mother_name', 'mother_contact', 'mother_email', 'mother_identification_number',
        'father_name', 'father_contact', 'father_email_address', 'father_identification_number',
        'emergency_contact_name', 'emergency_contact_number',
        'registrant_name', 'registrant_email', 'registrant_identification_number',
        'student_primary_school',
    ];

    /**
     * Search across the student, parents, emergency contact and registrant.
     * Every word must match somewhere, so "smith 868-555" finds a Smith with
     * that phone number. Phone-like terms also match with punctuation removed.
     */
    public function scopeSearch($query, $search)
    {
        $terms = preg_split('/\s+/', trim((string) $search), -1, PREG_SPLIT_NO_EMPTY);
        if (!$terms) {
            return $query;
        }

        foreach ($terms as $term) {
            $query->where(function ($q) use ($term) {
                foreach (self::SEARCH_COLUMNS as $column) {
                    $q->orWhere($column, 'like', "%{$term}%");
                }

                $digits = preg_replace('/\D/', '', $term);
                if (strlen($digits) >= 4) {
                    foreach (['student_contact', 'mother_contact', 'father_contact', 'emergency_contact_number'] as $phone) {
                        $q->orWhere(DB::raw("REPLACE(REPLACE(REPLACE({$phone}, '-', ''), ' ', ''), '+', '')"), 'like', "%{$digits}%");
                    }
                }

                if (preg_match('/^\d+$/', $term)) {
                    $q->orWhere('id', (int) $term);
                }
            });
        }

        return $query;
    }

    // Get all available registration years (portable across MySQL and SQLite)
    public static function getRegistrationYears(): array
    {
        return self::query()
            ->whereNotNull('registration_date')
            ->toBase()
            ->distinct()
            ->pluck('registration_date')
            ->map(fn ($date) => (int) substr((string) $date, 0, 4))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
    }

    // Get all available student names for filtering
    public static function getStudentNames(): array
    {
        return self::whereNotNull('student_name')
            ->orderBy('student_name')
            ->pluck('student_name')
            ->unique()
            ->values()
            ->toArray();
    }
}
