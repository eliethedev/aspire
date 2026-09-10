# Plan: AI Usage and Cost Monitoring (Admin Panel)

## Goal

Give administrators a dedicated page to monitor how much the system spends on AI,
and which AI features, models, providers and users consume the most resources.

## Context (what already exists)

- Every AI generation attempt is recorded in `ai_usage_logs` via
  `AIProviderManager::recordUsage()` (`app/AI/Providers/AIProviderManager.php:347-374`).
  Columns: `stage`, `provider`, `model`, `prompt_tokens`, `response_tokens`,
  `total_tokens`, `response_time_ms`, `success`, `fallback_used`, `error_message`,
  `observation_id`, `user_id`, `created_at`.
- The Admin AI Settings page (`admin.ai.index`) already shows tiny read-only usage
  stats (total calls, success rate, avg response, total tokens, calls by stage,
  recent calls) inside a tab.
- The Admin layout (`layouts/admin.blade.php`) provides Alpine.js + Tailwind +
  Chart.js (via CDN, same as the dashboard). Sidebar menu lives in
  `partials/admin/sidebar.blade.php` ("Other" section).
- Route conventions: `Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(...)`.

## Design decisions

### 1. No database migration
`ai_usage_logs` already captures everything needed. `cost` is *not* stored —
instead it is **estimated** from a configurable pricing catalog and computed on
the fly. This keeps the DB schema unchanged (repo convention) and makes price
edits immediate. Cost is an estimate, clearly labelled "estimated".

### 2. Pricing catalog in `config/ai.php`
A new `ai.pricing` block:
- `currency = 'PHP'`, `currency_symbol = '₱'`
- `usd_to_php_rate` (env `AI_USD_TO_PHP_RATE`, default `58`) — providers bill in
  USD, so catalog prices are USD-per-1M-tokens and the estimate is converted to peso.
- `providers` → per-provider fallback `{input, output}` per 1M tokens
- `models` → per-model overrides (finer prices for the curated `model_catalog` ids)

Prices are editable estimates; free/local models (openrouter `:free`, ollama) are `0`.
Unknown/custom models fall back to their provider default, or `0` when the provider
is unpriced.

**Decision (confirmed):** cost is displayed in **PHP (₱)**, computed **on-the-fly**
from config — no DB migration.

### 3. Dedicated admin page, separate controller + route group
- Route group `admin.ai-usage.*` (`GET /admin/ai-usage`, `GET /admin/ai-usage/users/{user}`)
- Controller `App\Http\Controllers\Admin\AiUsageController`
- Views `resources/views/admin/ai-usage/{index,show}.blade.php`
- Sidebar item "AI Usage & Cost" under **Other**, highlighted via
  `request()->routeIs('admin.ai-usage.*')`. (`admin.ai.*` wildcard does NOT match
  `admin.ai-usage.*`, so the existing AI Settings item stays unhighlighted.)

## Files to modify / create

### New files
| File | Purpose |
|------|---------|
| `app/AI/Costs/AiCostCalculator.php` | Estimate cost per call from tokens + pricing config; `estimate()`, `priceFor()`, `currency()`. |
| `app/Http/Controllers/Admin/AiUsageController.php` | Index (filters + aggregates + charts + paginated log) and `show(User $user)` drill-down. |
| `resources/views/admin/ai-usage/index.blade.php` | Main dashboard page. |
| `resources/views/admin/ai-usage/show.blade.php` | Per-user usage page. |
| `tests/Unit/AI/AiCostCalculatorTest.php` | Unit tests for the calculator. |
| `tests/Feature/AdminAiUsageTest.php` | Feature tests: admin access, filters, per-user drill-down. |

### Modified files
| File | Change |
|------|--------|
| `config/ai.php` | Add `pricing` block (currency, provider fallbacks, model overrides). |
| `app/Models/AiUsageLog.php` | Add `estimatedCost()` and `stageLabel()`/`currency()` convenience helpers (computed, no schema change). |
| `routes/web.php` | Add `admin.ai-usage` route group inside the admin group. |
| `resources/views/partials/admin/sidebar.blade.php` | Add "AI Usage & Cost" item under Other. |

## Index page contents

1. **Filter bar** (GET form, preserves query string): `date_from`, `date_to`,
   `provider`, `model`, `stage`, `user`, `success`, `fallback_used`. Providers /
   models / stages / users are sourced from the distinct values actually present.
   Clear-filters link when any filter is active. Filtering by user answers
   "which users consume the most".
2. **Stat cards** (scoped to the active filter): Estimated Spend, Total Calls,
   Total Tokens, Success Rate, Avg Response (ms), Avg Cost / Call.
3. **Charts** (Chart.js):
   - Cost per day — bar, within the selected range (default last 30 days)
   - Cost by provider — doughnut
   - Cost by model (top 10) — bar
   - Calls by stage — doughnut
4. **Breakdown tables**:
   - Top models by cost (model, provider, calls, tokens, cost)
   - Top users by cost (user, role, calls, tokens, cost, link to drill-down)
   - Top features/stages by cost
5. **Detailed call log** — paginated (30/page): when, user, provider, model,
   stage, in/out tokens, estimated cost, duration, status badge (success /
   failed / fallback), observation link when present.

## Show (user) page

Reuses the same query helpers scoped to one user: stat cards, cost-by-model
breakdown, cost-per-day chart, and that user's paginated call log. Link back to
the main page keeping filters.

## Query strategy (performance)

Cost per row is cheap (2 multiplications), but aggregating millions of rows
per-row in PHP is not. So:
- Aggregates (top models / users / stages / provider chart / time series) are
  computed in **SQL**: `GROUP BY` on the dimension + `(provider, model)` (photo
  model groups are few), then `AiCostCalculator` folds per-group token sums into
  cost in PHP.
- Only the paginated call-log page computes cost per row (max 30 rows).

## Model helpers

```php
// AiUsageLog
public function estimatedCost(): float;          // delegates to AiCostCalculator
public function stageLabel(): string;            // human readable (str_replace) 
public static function currency(): string;       // from config('ai.pricing.currency')
```

## Routes

```php
// AI usage & cost monitoring (admin only)
Route::prefix('ai-usage')->name('ai-usage.')->group(function () {
    Route::get('/', [AiUsageController::class, 'index'])->name('index');
    Route::get('/users/{user}', [AiUsageController::class, 'show'])->name('users.show');
});
```

## Tests

- `AiCostCalculatorTest` (Unit):
  - exact known-model pricing math
  - provider fallback when model unpriced
  - unknown provider/model → 0
  - zero tokens → 0
  - currency read
- `AdminAiUsageTest` (Feature, `RefreshDatabase`):
  - non-admin is blocked from `/admin/ai-usage`
  - admin sees the dashboard (200 + "Estimated Spend" label)
  - provider filter narrows results (assert provider shown / other absent)
  - `/admin/ai-usage/users/{id}` renders and is scoped to that user

## Verification

1. `php artisan route:list --name=admin.ai-usage` → 2 routes present.
2. `vendor\bin\phpunit --filter 'AiUsage|AiCostCalculator'` → green.
3. Manual: log in as admin → `/admin/ai-usage` → charts render, filters work,
   top-breakdown tables populate, pagination works, user drill-down works.
4. Seed (if needed for a quick look): a few `AiUsageLog::create([...])` rows with
   different providers/models/users.

## Notes / open questions

- Prices in the catalog are **estimates** meant to mirror public list prices and
  are fully editable in `config/ai.php` / env. The reality that some model ids
  in this prototype are fictional means prices are placeholders that admins
  should tune.
- Cost is computed on the fly (no migration). If the user later wants
  historically-frozen costs (price changes shouldn't rewrite the past), that is
  a follow-up requiring a `cost_estimate` column + writing it in `recordUsage()`.

## Decisions (confirmed by user)

- **Currency:** PHP (₱) via a configurable `usd_to_php_rate`.
- **Cost model:** on-the-fly estimation from `config/ai.php` — no schema change.