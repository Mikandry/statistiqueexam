<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'user_id' => (int) $request->integer('user_id', 0),
            'action' => (string) $request->query('action', ''),
            'ip' => (string) $request->query('ip', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        $query = AuditLog::query()->with('user')->orderByDesc('created_at');

        if ($filters['user_id'] > 0) {
            $query->where('user_id', $filters['user_id']);
        }
        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }
        if ($filters['ip'] !== '') {
            $query->where('ip', $filters['ip']);
        }
        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = $query->paginate(30)->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => $actions,
            'users' => $users,
        ]);
    }
}
