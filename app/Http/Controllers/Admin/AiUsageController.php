<?php

namespace App\Http\Controllers\Admin;

use App\AI\Costs\AiCostCalculator;
use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;

class AiUsageController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);

        $analysis = $this->analysis(
            $this->baseQuery($filters),
            $filters['date_from'],
            $filters['date_to'],
        );

        $logs = $this->baseQuery($filters)
            ->with(['user', 'observation'])
            ->latest('created_at')
            ->paginate($request->integer('per_page', 30))
            ->withQueryString();

        return view('admin.ai-usage.index', [
            'filters' => $filters,
            'options' => $this->options(),
            'analysis' => $analysis,
            'logs' => $logs,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $filters = $this->filters($request);
        $filters['user_id'] = $user->id;

        $analysis = $this->analysis(
            $this->baseQuery($filters),
            $filters['date_from'],
            $filters['date_to'],
        );

        $logs = $this->baseQuery($filters)
            ->with(['user', 'observation'])
            ->latest('created_at')
            ->paginate($request->integer('per_page', 30))
            ->withQueryString();

        return view('admin.ai-usage.show', [
            'user' => $user,
            'filters' => $filters,
            'analysis' => $analysis,
            'logs' => $logs,
        ]);
    }

    /**
     * All aggregates over the filtered query: overview stats, cost by
     * provider, top models, top stages/features, top users and the daily
     * cost/call time series. Aggregates group by (provider, model) in SQL and
     * fold token sums into cost in PHP so we never iterate rows one-by-one.
     */
    protected function analysis(Builder $query, ?string $dateFrom, ?string $dateTo): array
    {
        $calculator = app(AiCostCalculator::class);

        $stats = [
            'calls' => 0,
            'successful' => 0,
            'tokens' => 0,
            'cost' => 0.0,
            'avg_response_ms' => round((float) (clone $query)->avg('response_time_ms'), 1),
            'success_rate' => 0.0,
            'avg_cost_per_call' => 0.0,
        ];

        $modelRows = (clone $query)
            ->select('provider', 'model')
            ->selectRaw('SUM(prompt_tokens) AS prompt_tokens')
            ->selectRaw('SUM(response_tokens) AS response_tokens')
            ->selectRaw('SUM(total_tokens) AS total_tokens')
            ->selectRaw('COUNT(*) AS calls')
            ->selectRaw('SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) AS successful')
            ->groupBy('provider', 'model')
            ->get();

        $providerMap = [];
        $modelList = [];

        foreach ($modelRows as $row) {
            $cost = $calculator->estimate($row->provider, $row->model, $row->prompt_tokens, $row->response_tokens);

            $stats['calls'] += (int) $row->calls;
            $stats['successful'] += (int) $row->successful;
            $stats['tokens'] += (int) $row->total_tokens;
            $stats['cost'] += $cost;

            $provider = $row->provider ?: 'unknown';
            $providerMap[$provider]['cost'] = ($providerMap[$provider]['cost'] ?? 0.0) + $cost;
            $providerMap[$provider]['calls'] = ($providerMap[$provider]['calls'] ?? 0) + (int) $row->calls;

            $modelList[] = [
                'provider' => $row->provider,
                'model' => $row->model ?: 'unknown',
                'calls' => (int) $row->calls,
                'tokens' => (int) $row->total_tokens,
                'cost' => $cost,
            ];
        }

        $stats['success_rate'] = $stats['calls'] > 0
            ? round($stats['successful'] / $stats['calls'] * 100, 1)
            : 0.0;
        $stats['avg_cost_per_call'] = $stats['calls'] > 0
            ? round($stats['cost'] / $stats['calls'], 4)
            : 0.0;

        uasort($providerMap, fn ($a, $b) => $b['cost'] <=> $a['cost']);

        $models = collect($modelList)->sortByDesc('cost')->values();
        $top_models = $models->take(10)->values()->all();

        $stageRows = (clone $query)
            ->select('stage', 'provider', 'model')
            ->selectRaw('SUM(prompt_tokens) AS prompt_tokens')
            ->selectRaw('SUM(response_tokens) AS response_tokens')
            ->selectRaw('SUM(total_tokens) AS total_tokens')
            ->selectRaw('COUNT(*) AS calls')
            ->groupBy('stage', 'provider', 'model')
            ->get();

        $stageMap = [];
        foreach ($stageRows as $row) {
            $stage = $row->stage ?: 'unknown';
            $stageMap[$stage]['cost'] = ($stageMap[$stage]['cost'] ?? 0.0)
                + $calculator->estimate($row->provider, $row->model, $row->prompt_tokens, $row->response_tokens);
            $stageMap[$stage]['calls'] = ($stageMap[$stage]['calls'] ?? 0) + (int) $row->calls;
            $stageMap[$stage]['tokens'] = ($stageMap[$stage]['tokens'] ?? 0) + (int) $row->total_tokens;
        }
        uasort($stageMap, fn ($a, $b) => $b['cost'] <=> $a['cost']);
        $top_stages = collect($stageMap)
            ->map(fn ($row, $stage) => array_merge(['stage' => $stage], $row))
            ->values()
            ->all();

        $userRows = (clone $query)
            ->whereNotNull('user_id')
            ->select('user_id', 'provider', 'model')
            ->selectRaw('SUM(prompt_tokens) AS prompt_tokens')
            ->selectRaw('SUM(response_tokens) AS response_tokens')
            ->selectRaw('SUM(total_tokens) AS total_tokens')
            ->selectRaw('COUNT(*) AS calls')
            ->groupBy('user_id', 'provider', 'model')
            ->get();

        $userAgg = [];
        foreach ($userRows as $row) {
            $userId = (int) $row->user_id;
            $userAgg[$userId]['cost'] = ($userAgg[$userId]['cost'] ?? 0.0)
                + $calculator->estimate($row->provider, $row->model, $row->prompt_tokens, $row->response_tokens);
            $userAgg[$userId]['calls'] = ($userAgg[$userId]['calls'] ?? 0) + (int) $row->calls;
            $userAgg[$userId]['tokens'] = ($userAgg[$userId]['tokens'] ?? 0) + (int) $row->total_tokens;
        }
        uasort($userAgg, fn ($a, $b) => $b['cost'] <=> $a['cost']);

        $topUserIds = array_slice(array_keys($userAgg), 0, 10, true);
        $users = User::whereIn('id', $topUserIds)->get()->keyBy('id');

        $top_users = collect($userAgg)
            ->take(10)
            ->map(function (array $row, int $userId) use ($users) {
                $user = $users->get($userId);

                return [
                    'user_id' => $userId,
                    'name' => $user ? $user->name : 'Deleted user #'.$userId,
                    'role' => $user?->role,
                    'calls' => $row['calls'],
                    'tokens' => $row['tokens'],
                    'cost' => $row['cost'],
                ];
            })
            ->values()
            ->all();

        $daily = $this->dailySeries($query, $dateFrom, $dateTo);

        return compact(
            'stats',
            'providerMap',
            'top_models',
            'top_stages',
            'top_users',
            'daily',
        );
    }

    /**
     * Cost and call totals per day within the requested range (defaults to the
     * last 30 days). Grouped by (date, provider, model) in SQL and folded in
     * PHP; missing days are zero-filled so the chart axis stays contiguous.
     *
     * @return array{labels: array<int, string>, costs: array<int, float>, calls: array<int, int>}
     */
    protected function dailySeries(Builder $query, ?string $dateFrom, ?string $dateTo): array
    {
        $calculator = app(AiCostCalculator::class);

        $end = $dateTo ? Carbon::parse($dateTo) : now();
        $start = $dateFrom ? Carbon::parse($dateFrom) : $end->copy()->subDays(29);

        $rows = (clone $query)
            ->selectRaw('DATE(created_at) AS day')
            ->select('provider', 'model')
            ->selectRaw('SUM(prompt_tokens) AS prompt_tokens')
            ->selectRaw('SUM(response_tokens) AS response_tokens')
            ->selectRaw('COUNT(*) AS calls')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy('day', 'provider', 'model')
            ->get();

        $byDay = [];
        foreach ($rows as $row) {
            $day = $row->day;
            $byDay[$day]['cost'] = ($byDay[$day]['cost'] ?? 0.0)
                + $calculator->estimate($row->provider, $row->model, $row->prompt_tokens, $row->response_tokens);
            $byDay[$day]['calls'] = ($byDay[$day]['calls'] ?? 0) + (int) $row->calls;
        }

        $labels = [];
        $costs = [];
        $calls = [];

        foreach (CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $labels[] = $day->format('M j');
            $costs[] = round($byDay[$key]['cost'] ?? 0.0, 2);
            $calls[] = (int) ($byDay[$key]['calls'] ?? 0);
        }

        return compact('labels', 'costs', 'calls');
    }

    protected function baseQuery(array $filters): Builder
    {
        $query = AiUsageLog::query();

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (! empty($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }
        if (! empty($filters['model'])) {
            $query->where('model', $filters['model']);
        }
        if (! empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if ($filters['success'] !== null) {
            $query->where('success', $filters['success']);
        }
        if ($filters['fallback_used'] !== null) {
            $query->where('fallback_used', $filters['fallback_used']);
        }

        return $query;
    }

    protected function filters(Request $request): array
    {
        $success = $request->input('success');
        $fallback = $request->input('fallback_used');

        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'provider' => $request->input('provider'),
            'model' => $request->input('model'),
            'stage' => $request->input('stage'),
            'user_id' => $request->integer('user_id') ?: null,
            'success' => in_array($success, ['0', '1'], true) ? (int) $success : null,
            'fallback_used' => in_array($fallback, ['0', '1'], true) ? (int) $fallback : null,
        ];
    }

    /**
     * Distinct values present in the log table, for the filter dropdowns.
     */
    protected function options(): array
    {
        $userIds = AiUsageLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return [
            'providers' => $this->distinctValues('provider'),
            'models' => $this->distinctValues('model'),
            'stages' => $this->distinctValues('stage'),
            'user_ids' => $userIds,
            'users' => $users,
        ];
    }

    protected function distinctValues(string $column): array
    {
        return AiUsageLog::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }
}