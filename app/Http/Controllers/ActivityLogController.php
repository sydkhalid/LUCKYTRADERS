<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        return $this->report($request, 'Activity Log List');
    }

    public function users(Request $request): View
    {
        return $this->report($request, 'User Activity Report', 'user');
    }

    public function modules(Request $request): View
    {
        return $this->report($request, 'Module Activity Report', 'module');
    }

    public function dates(Request $request): View
    {
        return $this->report($request, 'Date-wise Activity Report', 'date');
    }

    public function destroy(Request $request, Activity $activity): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('Super Admin'), 403);

        $activity->delete();

        return back()->with('success', 'Activity log deleted successfully.');
    }

    private function report(Request $request, string $title, string $reportMode = 'list'): View
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);

        $activities = (clone $query)
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('activity-logs.index', [
            'activities' => $activities,
            'filters' => $filters,
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'role']),
            'modules' => $this->modulesList(),
            'actions' => $this->actionsList(),
            'title' => $title,
            'reportMode' => $reportMode,
            'totalCount' => (clone $query)->count(),
            'userSummary' => $reportMode === 'user' ? $this->userSummary($filters) : collect(),
            'moduleSummary' => $reportMode === 'module' ? $this->moduleSummary($filters) : collect(),
            'dateSummary' => $reportMode === 'date' ? $this->dateSummary($filters) : collect(),
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        return [
            'user_id' => $validated['user_id'] ?? null,
            'module' => $validated['module'] ?? null,
            'action' => $validated['action'] ?? null,
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ];
    }

    private function filteredQuery(array $filters)
    {
        return Activity::query()
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('causer_type', User::class)->where('causer_id', $userId))
            ->when($filters['module'], fn ($query, $module) => $query->where('log_name', $module))
            ->when($filters['action'], fn ($query, $action) => $query->where('event', $action))
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
    }

    private function modulesList()
    {
        return Activity::query()
            ->whereNotNull('log_name')
            ->where('log_name', '<>', '')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');
    }

    private function actionsList()
    {
        return Activity::query()
            ->whereNotNull('event')
            ->where('event', '<>', '')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');
    }

    private function userSummary(array $filters)
    {
        return $this->filteredQuery(array_merge($filters, ['user_id' => null]))
            ->selectRaw('causer_id, count(*) as total')
            ->where('causer_type', User::class)
            ->whereNotNull('causer_id')
            ->groupBy('causer_id')
            ->with('causer')
            ->orderByDesc('total')
            ->limit(25)
            ->get();
    }

    private function moduleSummary(array $filters)
    {
        return $this->filteredQuery(array_merge($filters, ['module' => null]))
            ->selectRaw('log_name, count(*) as total')
            ->whereNotNull('log_name')
            ->groupBy('log_name')
            ->orderByDesc('total')
            ->limit(25)
            ->get();
    }

    private function dateSummary(array $filters)
    {
        return $this->filteredQuery(array_merge($filters, ['from_date' => null, 'to_date' => null]))
            ->selectRaw('date(created_at) as activity_date, count(*) as total')
            ->groupBy('activity_date')
            ->orderByDesc('activity_date')
            ->limit(31)
            ->get();
    }
}
