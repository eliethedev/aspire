<?php

namespace App\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Providers\AIProviderManager;
use App\AI\RAG\CotIndicatorRepository;
use App\AI\RAG\PPSTRubricRepository;
use App\AI\Services\AIFeedbackService;
use App\AI\Services\FinalReportService;
use App\AI\Services\ObservationGuidanceService;
use App\AI\Services\PostConferenceService;
use App\AI\Services\PreObservationService;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Central AI provider router: task routing, fallback chain, usage tracking.
        $this->app->singleton(AIProviderManager::class);

        // Bind the default AI provider (used as fallback)
        $this->app->singleton(AIServiceInterface::class, function ($app) {
            return $app->make(AIProviderManager::class)->createProvider(
                config('ai.provider', 'gemini'),
                config('ai.models.default', 'gemini-3.6-flash')
            );
        });

        // Bind a provider factory for per-stage resolution
        $this->app->singleton('ai.provider.factory', function ($app) {
            return function (?string $stage = null) use ($app): AIServiceInterface {
                return $app->make(AIProviderManager::class)->resolveForTask($stage ?? '');
            };
        });

        // Bind RAG repositories
        $this->app->singleton(CotIndicatorRepository::class);
        $this->app->singleton(PPSTRubricRepository::class);

        // Bind AI services (singletons for performance)
        $this->app->singleton(AIFeedbackService::class);
        $this->app->singleton(PreObservationService::class);
        $this->app->singleton(ObservationGuidanceService::class);
        $this->app->singleton(PostConferenceService::class);
        $this->app->singleton(FinalReportService::class);
        $this->app->singleton(\App\AI\Services\LessonPlanSuggestionService::class);
        $this->app->singleton(\App\AI\Services\LessonPlanSummaryService::class);
        $this->app->singleton(\App\AI\Services\CotIndicatorAnalysisService::class);
        $this->app->singleton(\App\AI\Services\OverallRecommendationService::class);
    }

    public static function createProvider(string $provider, string $model = ''): AIServiceInterface
    {
        return app(AIProviderManager::class)->createProvider($provider, $model);
    }

    public function boot(): void
    {
        Event::listen(Failed::class, function (Failed $event) {
            app(AuditLogService::class)->logFailedLogin(
                $event->user,
                $event->credentials['email'] ?? 'unknown',
                'Invalid credentials'
            );
        });

        $this->configureRateLimiters();
    }

    protected function configureRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by('login:' . $key);
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $key = Str::lower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinutes(15, 3)->by('password-reset:' . $key);
        });

        RateLimiter::for('verification', function (Request $request) {
            $id = $request->user()?->id ?? $request->route('id');

            return Limit::perHour(5)->by('verification:' . ($id ?? $request->ip()));
        });

        RateLimiter::for('invitations', function (Request $request) {
            $actor = $request->user()?->id ?? $request->ip();

            return Limit::perHour(10)->by('invitations:' . $actor . '|' . $request->ip());
        });

        RateLimiter::for('uploads', function (Request $request) {
            $user = $request->user()?->id ?? $request->ip();

            return Limit::perHour(20)->by('uploads:' . $user . '|' . $request->ip());
        });

        RateLimiter::for('exports', function (Request $request) {
            $user = $request->user()?->id ?? $request->ip();

            return Limit::perMinutes(10, 10)->by('exports:' . $user . '|' . $request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            $user = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(60)->by('search:' . $user . '|' . $request->ip());
        });
    }
}
