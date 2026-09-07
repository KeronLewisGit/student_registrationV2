<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry in a student's audit trail.
 */
class StudentActivity extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'student_id', 'user_id', 'user_name', 'action', 'summary', 'changes', 'ip', 'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Human labels for the action codes.
     */
    public const ACTIONS = [
        'created'  => 'Created',
        'updated'  => 'Updated',
        'deleted'  => 'Deleted',
        'restored' => 'Restored',
        'promoted' => 'Promoted',
        'status'   => 'Status changed',
        'photo'    => 'Photo uploaded',
        'document' => 'Document stored',
    ];

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
        return self::ACTIONS[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Record an activity against a student, attributing it to the signed-in
     * user, or to the console / registration form when there is none.
     */
    public static function record(Student $student, string $action, ?string $summary = null, ?array $changes = null): self
    {
        $user = auth()->user();

        $userName = $user?->name
            ?? (app()->runningInConsole() ? 'System (console)' : 'Registration form');

        return self::create([
            'student_id' => $student->id,
            'user_id' => $user?->id,
            'user_name' => $userName,
            'action' => $action,
            'summary' => $summary,
            'changes' => $changes ?: null,
            'ip' => app()->runningInConsole() ? null : request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
