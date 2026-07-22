<?php

namespace App\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Providers\ClaudeProvider;
use App\AI\Providers\GeminiProvider;
use App\AI\Providers\OllamaProvider;
use App\AI\Providers\OpenAIProvider;
use App\AI\RAG\CotIndicatorRepository;
use App\AI\RAG\PPSTRubricRepository;
use App\AI\Services\AIFeedbackService;
use App\AI\Services\FinalReportService;
use App\AI\Services\ObservationGuidanceService;
use App\AI\Services\PostConferenceService;
use App\AI\Services\PreObservationService;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the default AI provider (used as fallback)
        $this->app->singleton(AIServiceInterface::class, function ($app) {
            return self::createProvider(
                config('ai.provider', 'gemini'),
                config('ai.models.default', 'gemini-2.0-flash')
            );
        });

        // Bind a provider factory for per-stage resolution
        $this->app->singleton('ai.provider.factory', function () {
            return function (?string $stage = null): AIServiceInterface {
                $defaultProvider = config('ai.provider', 'gemini');
                $defaultModel = config('ai.models.default', 'gemini-2.0-flash');

                if ($stage && config("ai.models.{$stage}") !== null) {
                    $stageConfig = config("ai.models.{$stage}");
                    if (is_array($stageConfig)) {
                        $provider = $stageConfig['provider'] ?: $defaultProvider;
                        $model = $stageConfig['model'] ?: $defaultModel;
                    } else {
                        $provider = $defaultProvider;
                        $model = (string) $stageConfig;
                    }
                } else {
                    $provider = $defaultProvider;
                    $model = $defaultModel;
                }

                return self::createProvider($provider, $model);
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

    public static function createProvider(string $provider, string $model = ''): AIServiceInterface
    {
        return match ($provider) {
            'openai' => new OpenAIProvider(model: $model ?: null),
            'claude' => new ClaudeProvider(model: $model ?: null),
            'ollama' => new OllamaProvider(model: $model ?: null),
            default => new GeminiProvider,
        };
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
    }
}
