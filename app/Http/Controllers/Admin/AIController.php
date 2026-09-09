<?php

namespace App\Http\Controllers\Admin;

use App\AI\Providers\AIProviderManager;
use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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

        $maskedKeys = $this->getMaskedApiKeys();

        return view('admin.ai.index', compact('config', 'usageStats', 'providerStatus', 'maskedKeys'));
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

        $effectiveProvider = $request->has('ai_provider')
            ? (string) $request->input('ai_provider')
            : (string) config('ai.provider', 'gemini');

        $effectiveModel = $this->resolveModelValue(
            $request->input('ai_model_default'),
            $request->input('ai_model_default_custom'),
            (string) config('ai.models.default', 'gemini-3.6-flash'),
        );

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

        // Connectivity guard: never ship a configuration that cannot currently
        // serve requests, unless the admin explicitly opts out or is disabling AI.
        $disablingAi = $request->has('ai_enabled') && $request->boolean('ai_enabled') === false;
        if (! $request->boolean('ai_skip_verify') && ! $disablingAi) {
            $verifyError = $this->verifyProviderBeforeSave($request, $effectiveProvider, $effectiveModel);
            if ($verifyError !== null) {
                return back()->with('error', $verifyError)->withInput();
            }
        }

        // Keep a recoverable snapshot of the last good configuration.
        $this->backupEnvFile($envFile);

        $written = file_put_contents($envFile, $envContent);
        if ($written === false) {
            return back()->with('error', 'Failed to write environment file. Check file permissions.');
        }

        // Reflect the saved provider + default model in the running process so
        // the admin page (and subsequent AI calls in this process) immediately
        // use the new selection instead of a stale boot-time value.
        config(['ai.provider' => $effectiveProvider]);
        config(['ai.models.default' => $effectiveModel]);

        // Purge a cached configuration if one exists: otherwise a cached
        // baseline could be served instead of the freshly saved .env values
        // after the next process/system restart.
        if (app()->configurationIsCached()) {
            Artisan::call('config:clear');
        }

        return back()->with('success', 'AI settings updated successfully (provider/model verified). Changes take effect on the next page load.');
    }

    /**
     * One-click recovery: revert .env to the most recent pre-save backup.
     */
    public function restore()
    {
        $dir = storage_path('app/ai/env-backups');
        $backups = $this->listEnvBackups($dir);
        if ($backups === []) {
            return back()->with('error', 'No AI settings backups are available to restore from.');
        }

        $envFile = base_path('.env');
        if (! file_exists($envFile)) {
            return back()->with('error', 'Environment file not found.');
        }

        // Snapshot the current (possibly broken) state so this restore is reversible.
        $this->backupEnvFile($envFile, 'ai-snapshot-');

        $latest = $backups[0];
        if (! copy($latest['path'], $envFile)) {
            return back()->with('error', 'Failed to restore AI settings from backup.');
        }

        return back()->with('success', 'AI settings restored from '.basename($latest['path']).'.');
    }

    /**
     * Emergency kill switch: disable/re-enable all AI processing immediately,
     * always keeping rule-based fallback enabled so users never see errors.
     */
    public function emergency(Request $request)
    {
        $action = $request->input('action') === 'enable' ? 'enable' : 'disable';

        $envFile = base_path('.env');
        if (! file_exists($envFile)) {
            return back()->with('error', 'Environment file not found.');
        }

        $envContent = file_get_contents($envFile);
        $this->setEnvValue($envContent, 'AI_ENABLED', $action === 'enable' ? 'true' : 'false');
        $this->setEnvValue($envContent, 'AI_FALLBACK_ENABLED', 'true');

        // Snapshot the pre-emergency state so it can be reverted precisely.
        $this->backupEnvFile($envFile, 'ai-snapshot-');

        if (file_put_contents($envFile, $envContent) === false) {
            return back()->with('error', 'Failed to write environment file. Check file permissions.');
        }

        return back()->with(
            'success',
            $action === 'enable'
                ? 'AI processing re-enabled. New requests will use AI again.'
                : 'AI processing is now DISABLED. Rule-based fallback will serve users until re-enabled.'
        );
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
            'provider' => 'required|in:gemini,openai,claude,deepseek,openrouter,ollama',
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

    private function getMaskedApiKeys(): array
    {
        $providers = ['gemini', 'openai', 'claude', 'deepseek', 'openrouter'];
        $masked = [];

        foreach ($providers as $provider) {
            $key = config("services.{$provider}.api_key", '');
            if (! empty($key)) {
                $len = strlen($key);
                if ($len <= 8) {
                    $masked[$provider] = str_repeat('*', $len);
                } else {
                    $masked[$provider] = substr($key, 0, 4) . str_repeat('*', $len - 8) . substr($key, -4);
                }
            } else {
                $masked[$provider] = '';
            }
        }

        return $masked;
    }

    /**
     * Run a real connectivity check on the exact provider/model that is about
     * to be saved, using any newly-submitted API key. Returns null on success
     * or a user-facing error string on failure.
     */
    private function verifyProviderBeforeSave(Request $request, string $provider, string $model): ?string
    {
        try {
            // Reflect a newly-submitted API key or Ollama URL before testing so we
            // verify exactly what will be stored, not the previous configuration.
            $keyField = 'ai_'.$provider.'_api_key';
            if ($provider !== 'ollama' && $request->filled($keyField)) {
                config(["services.{$provider}.api_key" => (string) $request->input($keyField)]);
            }
            if ($provider === 'gemini' && $request->filled($keyField)) {
                config(['services.gemini.api_key' => (string) $request->input($keyField)]);
            }
            if ($provider === 'ollama' && $request->filled('ai_ollama_url')) {
                config(['services.ollama.url' => (string) $request->input('ai_ollama_url')]);
            }

            $instance = app(AIProviderManager::class)->createProvider($provider, $model);
        } catch (\Throwable $e) {
            return 'Cannot create the '.ucfirst($provider).' provider: '.$e->getMessage().' Settings were not saved.';
        }

        try {
            $response = $instance->generate('Reply with the single word: OK', [
                'timeout' => 15,
                'max_output_tokens' => 16,
                'temperature' => 0,
            ]);
        } catch (\Throwable $e) {
            return 'Connectivity check failed for '.ucfirst($provider).' / "'.$model.'": '.$e->getMessage()
                .' Settings were not saved. Check the API key, model id and quota, or tick "Save without connectivity check".';
        }

        if ($response === null || trim((string) $response) === '') {
            $reason = method_exists($instance, 'getLastError') && $instance->getLastError()
                ? ' Reason: '.$instance->getLastError().'.'
                : '';

            return 'Connectivity check for '.ucfirst($provider).' / "'.$model.'" did not succeed.'.$reason
                .' Settings were not saved. Check the API key, model id and quota, or tick "Save without connectivity check".';
        }

        return null;
    }

    /**
     * Resolve a model dropdown value, handling the "Custom model…" sentinel and
     * blank choices the same way the write-loop does.
     */
    private function resolveModelValue(mixed $value, mixed $custom, string $existing): string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === '__custom__') {
            $custom = is_string($custom) ? trim($custom) : '';

            return $custom !== '' ? $custom : $existing;
        }
        if ($value === null || $value === '') {
            return $existing;
        }

        return (string) $value;
    }

    /**
     * Snapshot the current .env before it is overwritten, keeping the newest
     * five snapshots.
     */
    private function backupEnvFile(string $envFile, string $prefix = 'ai-'): void
    {
        // Tests exercise update()/emergency()/restore() against the real .env;
        // never leave trace snapshot files behind in the storage backup dir.
        if (app()->environment('testing')) {
            return;
        }

        $dir = storage_path('app/ai/env-backups');
        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            Log::warning('Could not create AI env backup directory: '.$dir);

            return;
        }

        $target = $dir.'/'.$prefix.now()->format('Ymd-His-u').'.env';
        if (! copy($envFile, $target)) {
            Log::warning('Could not back up .env to '.$target);

            return;
        }

        foreach (array_slice($this->listEnvBackups($dir), 5) as $old) {
            @unlink($old['path']);
        }
    }

    /**
     * Return auto-save backups newest-first. On-the-fly snapshots produced by
     * restore()/emergency() use the "ai-snapshot-" prefix and are excluded so
     * they never become restore points themselves.
     *
     * @return array<int, array{path: string, time: int}>
     */
    private function listEnvBackups(string $dir): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $out = [];
        foreach (glob($dir.'/ai-*.env') ?: [] as $file) {
            if (str_starts_with(basename($file), 'ai-snapshot-')) {
                continue;
            }
            $out[] = ['path' => $file, 'time' => filemtime($file)];
        }
        usort($out, fn ($a, $b) => $b['time'] <=> $a['time']);

        return $out;
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
