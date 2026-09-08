<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One entry in the audit trail: who did what, to which record, from where.
 */
class ActivityLog extends Model
{
    use Prunable;

    protected $table = 'activity_logs';

    public const UPDATED_AT = null;

    /** How long audit entries are kept (see model:prune in routes/console.php). */
    public const RETENTION_MONTHS = 24;

    /** Categories and their labels, in display order. */
    public const CATEGORIES = [
        'auth'      => 'Sign-ins',
        'student'   => 'Student records',
        'export'    => 'Exports',
        'print'     => 'Printing',
        'document'  => 'Document views',
        'import'    => 'Imports & uploads',
        'user'      => 'User management',
        'promotion' => 'Promotion',
        'system'    => 'System',
    ];

    /** Action codes and their labels. */
    public const ACTIONS = [
        // auth
        'login'          => 'Signed in',
        'login-failed'   => 'Sign-in failed',
        'logout'         => 'Signed out',
        // student
        'created'  => 'Created',
        'updated'  => 'Updated',
        'deleted'  => 'Deleted',
        'restored' => 'Restored',
        'promoted' => 'Promoted',
        'status'   => 'Status changed',
        'photo'    => 'Photo uploaded',
        'document' => 'Document stored',
        // export / print
        'spreadsheet' => 'Spreadsheet export',
        'pdf-batch'   => 'PDF batch export',
        'download'    => 'Download',
        'pdf'         => 'PDF',
        'print'       => 'Printed record',
        'print-batch' => 'Printed batch',
        'printable'   => 'Printed sheet',
        // document
        'viewed' => 'Viewed',
        // import
        'csv'    => 'CSV import',
        'photos' => 'Bulk photos',
        'failed' => 'Failed',
        // user
        'user-created'    => 'User created',
        'user-updated'    => 'User updated',
        'user-deleted'    => 'User deleted',
        'password-reset'  => 'Password reset',
        // promotion / system
        'run'    => 'Run',
        'deploy' => 'Deployment',
    ];

    protected $fillable = [
        'student_id', 'user_id', 'user_name', 'category', 'action', 'subject_type', 'subject_id', 'subject_label',
        'summary', 'changes', 'ip', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function prunable()
    {
        return static::where('created_at', '<', now()->subMonths(self::RETENTION_MONTHS));
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? ucfirst(str_replace('-', ' ', (string) $this->action));
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    /**
     * Record an activity.
     *
     * @param  string       $category  one of CATEGORIES
     * @param  string       $action    one of ACTIONS
     * @param  string|null  $summary   one-line description
     * @param  array        $options   subject (Model|null), changes (array|null), user (User|null, overrides the signed-in user), user_name (string)
     */
    public static function log(string $category, string $action, ?string $summary = null, array $options = []): self
    {
        $user = $options['user'] ?? auth()->user();
        $subject = $options['subject'] ?? null;

        // artisan commands have no request; the test runner does
        $console = app()->runningInConsole() && !app()->runningUnitTests();

        $userName = $options['user_name']
            ?? $user?->name
            ?? ($console ? 'System (console)' : 'Registration form');

        $subjectType = $subject ? Str::snake(class_basename($subject)) : ($options['subject_type'] ?? null);
        $subjectLabel = $options['subject_label']
            ?? ($subject instanceof Student ? $subject->student_name : ($subject instanceof User ? $subject->name : null));

        return self::create([
            'student_id' => $subject instanceof Student ? $subject->id : null,
            'user_id' => $user?->id,
            'user_name' => Str::limit((string) $userName, 118, ''),
            'category' => $category,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subject?->getKey() ?? ($options['subject_id'] ?? null),
            'subject_label' => $subjectLabel !== null ? Str::limit((string) $subjectLabel, 158, '') : null,
            'summary' => $summary !== null ? Str::limit($summary, 250) : null,
            'changes' => !empty($options['changes']) ? $options['changes'] : null,
            'ip' => $console ? null : request()?->ip(),
            'user_agent' => $console ? null : Str::limit((string) request()?->userAgent(), 250, ''),
            'created_at' => now(),
        ]);
    }

    /**
     * Link to the record this entry is about, when it still exists.
     */
    public function subjectUrl(): ?string
    {
        if ($this->subject_type === 'student' && $this->subject_id) {
            return route('students.show', $this->subject_id);
        }
        if ($this->subject_type === 'user' && $this->subject_id && $this->user_id !== null) {
            return route('users.edit', $this->subject_id);
        }

        return null;
    }
}
