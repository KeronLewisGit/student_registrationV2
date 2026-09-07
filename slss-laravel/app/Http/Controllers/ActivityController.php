<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin view of the audit trail across all students.
 */
class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'student' => 'nullable|integer',
            'user' => 'nullable|integer',
            'action' => 'nullable|string|max:30',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $activities = StudentActivity::query()
            ->with(['student' => fn ($q) => $q->withTrashed()])
            ->when($filters['student'] ?? null, fn ($q, $id) => $q->where('student_id', $id))
            ->when($filters['user'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filters['action'] ?? null, fn ($q, $a) => $q->where('action', $a))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('activity.index', [
            'activities' => $activities,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'actions' => StudentActivity::ACTIONS,
            'filters' => $filters,
            'student' => ($filters['student'] ?? null) ? Student::withTrashed()->find($filters['student']) : null,
        ]);
    }
}
