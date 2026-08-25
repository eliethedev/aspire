<?php

namespace App\Http\Controllers\Admin;

use App\AI\Providers\AIProviderManager;
use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AIController extends Controller
{
    public function index()
    {
        $config = config('ai');

        $providerStatus = $this->getProviderStatus();

        $usageStats = [
            'total_calls' => AiUsageLog::count(),
            'successful' => AiUsageLog::where('success', true)->count(),
            'failed' => AiUsageLog::where('success', false)->count(),
            'success_rate' => $this->calculateSuccessRate(),
            'average_response_time' => number_format(AiUsageLog::where('success', true)->avg('response_time_ms') ?? 0, 0),
            'total_tokens' => (int) AiUsageLog::sum('total_tokens') ?: 0,
            'calls_by_stage' => AiUsageLog::selectRaw('stage, count(*) as total, sum(case when success then 1 else 0 end) as successful')
                ->groupBy('stage')
                ->get(),
            'recent_calls' => AiUsageLog::latest()->take(10)->get(),
        ];

        return view('admin.ai.index', compact('config', 'usageStats', 'providerStatus'));
    }

    public function update(Request $request)
    {
        $rules = [
            'ai_enabled' => 'boolean',
            'ai_fallback_enabled' => 'boolean',
            'ai_logging_enabled' => 'boolean',
            'ai_provider' => 'in:gemini,openai,claude,deepseek,openrouter,ollama',

            'ai_gemini_enabled' => 'boolean',
            'ai_gemini_api_key' => 'nullable|string|max:500',
            'ai_gemini_model' => 'nullable|string|max:100',

            'ai_openai_enabled' => 'boolean',
            'ai_openai_api_key' => 'nullable|string|max:500',
            'ai_openai_model' => 'nullable|string|max:100',

            'ai_claude_enabled' => 'boolean',
            'ai_claude_api_key' => 'nullable|string|max:500',
            'ai_claude_model' => 'nullable|string|max:100',

            'ai_deepseek_enabled' => 'boolean',
            'ai_deepseek_api_key' => 'nullable|string|max:500',
            'ai_deepseek_model' => 'nullable|string|max:100',

            'ai_openrouter_enabled' => 'boolean',
            'ai_openrouter_api_key' => 'nullable|string|max:500',
            'ai_openrouter_model' => 'nullable|string|max:100',

            'ai_ollama_enabled' => 'boolean',
            'ai_ollama_url' => 'nullable|string|max:255',
            'ai_ollama_model' => 'nullable|string|max:100',

            'ai_model_default' => 'nullable|string|max:100',

            'ai_model_pre_observation_provider' => 'nullable|string|max:50',
            'ai_model_pre_observation' => 'nullable|string|max:100',
            'ai_model_observation_guidance_provider' => 'nullable|string|max:50',
            'ai_model_observation_guidance' => 'nullable|string|max:100',
            'ai_model_feedback_provider' => 'nullable|string|max:50',
            'ai_model_feedback' => 'nullable|string|max:100',
            'ai_model_post_conference_provider' => 'nullable|string|max:50',
            'ai_model_post_conference' => 'nullable|string|max:100',
            'ai_model_final_report_provider' => 'nullable|string|max:50',
            'ai_model_final_report' => 'nullable|string|max:100',

            'ai_python_bridge_enabled' => 'boolean',
        ];

        // Free-text inputs revealed by the "Custom model…" dropdown option.
        $modelEnvKeys = [
            'ai_gemini_model' => 'GEMINI_MODEL',
            'ai_openai_model' => 'OPENAI_MODEL',
            'ai_claude_model' => 'CLAUDE_MODEL',
            'ai_deepseek_model' => 'DEEPSEEK_MODEL',
            'ai_openrouter_model' => 'OPENROUTER_MODEL',
            'ai_ollama_model' => 'OLLAMA_MODEL',
            'ai_model_default' => 'AI_MODEL_DEFAULT',
            'ai_model_pre_observation' => 'AI_MODEL_PRE_OBSERVATION',
            'ai_model_observation_guidance' => 'AI_MODEL_OBSERVATION_GUIDANCE',
            'ai_model_feedback' => 'AI_MODEL_FEEDBACK',
            'ai_model_post_conference' => 'AI_MODEL_POST_CONFERENCE',
            'ai_model_final_report' => 'AI_MODEL_FINAL_REPORT',
        ];
        foreach (array_keys($modelEnvKeys) as $field) {
            $rules[$field.'_custom'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        $envFile = base_path('.env');
        if (! file_exists($envFile)) {
            return back()->with('error', 'Environment file not found.');
        }

        $envContent = file_get_contents($envFile);

        $mappings = [
            'ai_enabled' => 'AI_ENABLED',
            'ai_fallback_enabled' => 'AI_FALLBACK_ENABLED',
            'ai_logging_enabled' => 'AI_LOGGING_ENABLED',
            'ai_provider' => 'AI_PROVIDER',

            'ai_gemini_enabled' => 'AI_GEMINI_ENABLED',
            'ai_gemini_model' => 'GEMINI_MODEL',
            'ai_openai_enabled' => 'AI_OPENAI_ENABLED',
            'ai_openai_api_key' => 'OPENAI_API_KEY',
            'ai_openai_model' => 'OPENAI_MODEL',
            'ai_claude_enabled' => 'AI_CLAUDE_ENABLED',
            'ai_claude_api_key' => 'CLAUDE_API_KEY',
            'ai_claude_model' => 'CLAUDE_MODEL',
            'ai_deepseek_enabled' => 'AI_DEEPSEEK_ENABLED',
            'ai_deepseek_api_key' => 'DEEPSEEK_API_KEY',
            'ai_deepseek_model' => 'DEEPSEEK_MODEL',
            'ai_openrouter_enabled' => 'AI_OPENROUTER_ENABLED',
            'ai_openrouter_api_key' => 'OPENROUTER_API_KEY',
            'ai_openrouter_model' => 'OPENROUTER_MODEL',
            'ai_ollama_enabled' => 'AI_OLLAMA_ENABLED',
            'ai_ollama_url' => 'OLLAMA_URL',
            'ai_ollama_model' => 'OLLAMA_MODEL',

            'ai_model_default' => 'AI_MODEL_DEFAULT',
            'ai_model_pre_observation_provider' => 'AI_MODEL_PRE_OBSERVATION_PROVIDER',
            'ai_model_pre_observation' => 'AI_MODEL_PRE_OBSERVATION',
            'ai_model_observation_guidance_provider' => 'AI_MODEL_OBSERVATION_GUIDANCE_PROVIDER',
            'ai_model_observation_guidance' => 'AI_MODEL_OBSERVATION_GUIDANCE',
            'ai_model_feedback_provider' => 'AI_MODEL_FEEDBACK_PROVIDER',
            'ai_model_feedback' => 'AI_MODEL_FEEDBACK',
            'ai_model_post_conference_provider' => 'AI_MODEL_POST_CONFERENCE_PROVIDER',
            'ai_model_post_conference' => 'AI_MODEL_POST_CONFERENCE',
            'ai_model_final_report_provider' => 'AI_MODEL_FINAL_REPORT_PROVIDER',
            'ai_model_final_report' => 'AI_MODEL_FINAL_REPORT',

            'ai_python_bridge_enabled' => 'AI_PYTHON_BRIDGE_ENABLED',
        ];

        foreach ($mappings as $field => $envKey) {
            if ($request->has($field)) {
                $value = $request->input($field);
                $emptyApiKeys = ['ai_gemini_api_key', 'ai_openai_api_key', 'ai_claude_api_key', 'ai_deepseek_api_key', 'ai_openrouter_api_key'];
                if (in_array($field, $emptyApiKeys) && empty($value)) {
                    continue;
                }
                // Model dropdowns: never persist the "Custom model…" sentinel or
                // an untouched empty choice over the existing configuration.
                if (array_key_exists($field, $modelEnvKeys)) {
                    $value = is_string($value) ? trim($value) : $value;
                    if ($value === '__custom__') {
                        $custom = trim((string) $request->input($field.'_custom', ''));
                        if ($custom === '') {
                            continue;
                        }
                        $value = $custom;
                    } elseif ($value === null || $value === '') {
                        continue;
                    }
                }
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $this->setEnvValue($envContent, $envKey, $value);
            }
        }

        // Also write Gemini API key to GEMINI_API_KEY if provided
        if ($request->has('ai_gemini_api_key') && ! empty($request->input('ai_gemini_api_key'))) {
            $this->setEnvValue($envContent, 'GEMINI_API_KEY', $request->input('ai_gemini_api_key'));
            $this->setEnvValue($envContent, 'GOOGLE_GEMINI_API_KEY', $request->input('ai_gemini_api_key'));
        }

        $written = file_put_contents($envFile, $envContent);
        if ($written === false) {
            return back()->with('error', 'Failed to write environment file. Check file permissions.');
        }

        try {
            Artisan::call('config:clear');
        } catch (\Throwable) {
            // Config clear may fail in shared environments; proceed gracefully.
        }

        return back()->with('success', 'AI settings updated successfully.');
    }

    public function test()
    {
        try {
            Artisan::call('ai:test', ['--no-interaction' => true]);
            $output = Artisan::output();

            if (str_contains($output, 'AI service is operational')) {
                return back()->with('success', 'AI connectivity test passed. '.$output);
            }

            return back()->with('warning', 'AI test completed with issues: '.$output);
        } catch (\Exception $e) {
            return back()->with('error', 'AI test failed: '.$e->getMessage());
        }
    }

    public function testProvider(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:gemini,openai,claude,deepseek,ollama',
        ]);

        $provider = $request->input('provider');

        try {
            $instance = app(AIProviderManager::class)->createProvider($provider);

            if (! $instance->isAvailable()) {
                return back()->with('error', ucfirst($provider).' is not configured or unavailable. Check API key/connection.');
            }

            $result = $instance->generate('Say "hello" in one word.', ['timeout' => 15]);

            if ($result) {
                return back()->with('success', ucfirst($provider).' connectivity test passed. Response: '.substr($result, 0, 100));
            }

            return back()->with('warning', ucfirst($provider).' responded but returned empty content.');
        } catch (\Exception $e) {
            return back()->with('error', ucfirst($provider).' test failed: '.$e->getMessage());
        }
    }

    private function getProviderStatus(): array
    {
        $providers = config('ai.providers', []);
        $status = [];

        foreach ($providers as $key => $providerConfig) {
            $apiKey = $providerConfig['api_key'] ?? '';
            $enabled = $providerConfig['enabled'] ?? false;

            $configured = false;
            $instance = null;

            try {
                $instance = app(AIProviderManager::class)->createProvider($key);
                $configured = $instance->isAvailable();
            } catch (\Exception $e) {
                $configured = false;
            }

            $status[$key] = [
                'name' => $providerConfig['name'] ?? ucfirst($key),
                'enabled' => $enabled,
                'configured' => $configured,
                'default_model' => $providerConfig['default_model'] ?? '',
                'model' => $instance?->getModelName() ?? '',
            ];
        }

        return $status;
    }

    private function calculateSuccessRate(): float
    {
        $total = AiUsageLog::count();
        if ($total === 0) {
            return 0;
        }
        $successful = AiUsageLog::where('success', true)->count();

        return round(($successful / $total) * 100, 1);
    }

    private function setEnvValue(string &$content, string $key, ?string $value): void
    {
        // ConvertEmptyStringsToNull middleware can turn empty inputs into null.
        $value = $value ?? '';

        $escapedKey = preg_quote($key, '/');
        $pattern = "/^{$escapedKey}=.*/m";
        $replacement = "{$key}=" . str_replace(['\\', '$'], ['\\\\', '\\$'], $value);

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= PHP_EOL.$key.'='.$value;
        }
    }
}
