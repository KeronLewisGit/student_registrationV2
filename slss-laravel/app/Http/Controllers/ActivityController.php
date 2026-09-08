<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin view of the whole audit trail: sign-ins, student changes, exports,
 * prints, document views, imports, user management and system events.
 */
class ActivityController extends Controller
{
    /**
     * One-click views of the log.
     */
    public const PRESETS = [
        'all'      => ['label' => 'Everything',        'filters' => []],
        'signins'  => ['label' => 'Sign-ins',          'filters' => ['category' => 'auth']],
        'failed'   => ['label' => 'Failed sign-ins',   'filters' => ['category' => 'auth', 'action' => 'login-failed']],
        'students' => ['label' => 'Student changes',   'filters' => ['category' => 'student']],
        'exports'  => ['label' => 'Exports & prints',  'filters' => ['category' => 'export,print']],
        'documents'=> ['label' => 'Document views',    'filters' => ['category' => 'document']],
        'imports'  => ['label' => 'Imports & uploads', 'filters' => ['category' => 'import']],
        'users'    => ['label' => 'User management',   'filters' => ['category' => 'user']],
        'system'   => ['label' => 'System',            'filters' => ['category' => 'promotion,system']],
    ];

    public function __construct()
    {
        $this->middleware('can:admin');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'preset' => ['nullable', Rule::in(array_keys(self::PRESETS))],
            'category' => 'nullable|string|max:120',
            'action' => 'nullable|string|max:40',
            'user' => 'nullable|integer',
            'student' => 'nullable|integer',
            'ip' => 'nullable|string|max:45',
            'q' => 'nullable|string|max:120',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        if (!empty($filters['preset'])) {
            $filters = array_merge($filters, self::PRESETS[$filters['preset']]['filters']);
        }

        $categories = array_values(array_filter(array_map('trim', explode(',', (string) ($filters['category'] ?? '')))));

        $activities = ActivityLog::query()
            ->with(['student' => fn ($q) => $q->withTrashed()])
            ->when($categories, fn ($q) => $q->whereIn('category', $categories))
            ->when($filters['action'] ?? null, fn ($q, $a) => $q->where('action', $a))
            ->when($filters['user'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filters['student'] ?? null, fn ($q, $id) => $q->where('student_id', $id))
            ->when($filters['ip'] ?? null, fn ($q, $ip) => $q->where('ip', 'like', $ip . '%'))
            ->when($filters['q'] ?? null, fn ($q, $text) => $q->where(fn ($w) => $w
                ->where('summary', 'like', "%{$text}%")
                ->orWhere('subject_label', 'like', "%{$text}%")
                ->orWhere('user_name', 'like', "%{$text}%")))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        // Action choices narrow to the chosen categories
        $actions = ActivityLog::query()
            ->when($categories, fn ($q) => $q->whereIn('category', $categories))
            ->toBase()->distinct()->orderBy('action')->pluck('action')
            ->mapWithKeys(fn ($a) => [$a => ActivityLog::ACTIONS[$a] ?? ucfirst(str_replace('-', ' ', $a))])
            ->all();

        return view('activity.index', [
            'activities' => $activities,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'categories' => ActivityLog::CATEGORIES,
            'actions' => $actions,
            'presets' => self::PRESETS,
            'filters' => $filters,
            'student' => ($filters['student'] ?? null) ? Student::withTrashed()->find($filters['student']) : null,
            'counts' => ActivityLog::query()->toBase()
                ->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category')->all(),
        ]);
    }
}
