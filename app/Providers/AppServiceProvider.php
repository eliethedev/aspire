<?php

namespace App\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Providers\GeminiProvider;
use App\AI\RAG\CotIndicatorRepository;
use App\AI\RAG\PPSTRubricRepository;
use App\AI\Services\AIFeedbackService;
use App\AI\Services\FinalReportService;
use App\AI\Services\ObservationGuidanceService;
use App\AI\Services\PostConferenceService;
use App\AI\Services\PreObservationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the AI provider interface
        $this->app->singleton(AIServiceInterface::class, function ($app) {
            $provider = config('ai.provider', 'gemini');

            return match ($provider) {
                default => new GeminiProvider(),
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
