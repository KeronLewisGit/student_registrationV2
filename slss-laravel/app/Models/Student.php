<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The valid Form 1 class values.
     *
     * @var array<int, string>
     */
    public const FORM_CLASSES = ['1A', '1B', '1C', '1D', '1E', '1F'];

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
    public static function documentUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || strcasecmp($value, 'N/A') === 0) {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
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

    // Scope for filtering by name
    public function scopeByName($query, $name)
    {
        if ($name && $name !== '0') {
            return $query->where('student_name', $name);
        }
        return $query;
    }

    // Scope for searching
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_birth_certificate_pin', 'like', "%{$search}%")
                  ->orWhere('student_sea_number', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    // Get all available registration years (portable across MySQL and SQLite)
    public static function getRegistrationYears(): array
    {
        return self::whereNotNull('registration_date')
            ->pluck('registration_date')
            ->map(fn ($date) => (int) $date->format('Y'))
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
