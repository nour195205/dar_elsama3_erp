<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user')->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('action')) {
            $term = '%' . addcslashes((string) $request->action, '%_\\') . '%';
            $query->where('action', 'like', $term);
        }

        $logs = $query->paginate(40)->withQueryString();

        $actors = User::query()
            ->whereIn('role', ['employee', 'manager', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('web.activity_logs.index', [
            'title' => 'سجل النشاط',
            'subtitle' => 'تتبع إجراءات المستخدمين على النظام',
            'logs' => $logs,
            'actors' => $actors,
            'filterUserId' => $request->user_id,
            'filterAction' => $request->action,
        ]);
    }
}
