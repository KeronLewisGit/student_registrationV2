<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'job_title',
        'phone',
        'email',
        'password',
        'role',
    ];

    /**
     * Keep the display name and the first/last name in step whichever one
     * a form supplies (the admin form edits `name`, the profile page edits
     * first/last).
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty(['first_name', 'last_name']) && (filled($user->first_name) || filled($user->last_name))) {
                $user->name = trim($user->first_name . ' ' . $user->last_name);
            } elseif (($user->isDirty('name') || !filled($user->first_name)) && filled($user->name)) {
                $words = preg_split('/\s+/', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $user->first_name = $words ? array_shift($words) : null;
                $user->last_name = $words ? implode(' ', $words) : null;
            }
        });
    }

    public function getInitialsAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->last_name]);
        if (!$parts) {
            $parts = preg_split('/\s+/', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];
        }

        return strtoupper(implode('', array_map(fn ($p) => mb_substr($p, 0, 1), array_slice(array_values($parts), 0, 2))));
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function canEdit(): bool
    {
        return in_array($this->role, ['admin', 'staff']);
    }
}
