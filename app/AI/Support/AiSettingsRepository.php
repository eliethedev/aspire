<?php

namespace App\AI\Support;

use App\Models\AiSetting;

/**
 * Database-backed override store for the AI configuration.
 *
 * Administrator changes to the AI runtime settings (default provider,
 * default model, provider flags, per-provider models, Ollama URL) are
 * persisted here instead of being written into .env, so saving a new
 * model from the Admin AI Settings page never touches the env file or
 * forces a config:clear on a live server.
 *
 * Keys mirror the config paths they override (e.g. "ai.provider",
 * "ai.models.default", "ai.providers.gemini.enabled"). Missing keys
 * fall back to the regular config() value, so .env remains the baseline
 * and this store is a thin, always-current override layer.
 */
class AiSettingsRepository
{
    /**
     * Per-process cache of overrides: key => string value.
     *
     * @var array<string, string>|null
     */
    protected ?array $overrides = null;

    public function all(): array
    {
        if ($this->overrides === null) {
            try {
                $this->overrides = AiSetting::pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                // DB unavailable during early boot / console bootstrap: treat as empty.
                $this->overrides = [];
            }
        }

        return $this->overrides;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOL);
    }

    /**
     * Set one or more overrides. Empty string values delete the key so the
     * baseline config() value takes over again.
     *
     * @param  array<string, string|bool|null>  $values
     */
    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value === null || $value === '') {
                $this->forget($key);
                continue;
            }

            $stored = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
            AiSetting::updateOrCreate(['key' => $key], ['value' => $stored]);
            $this->overrides[$key] = $stored;
        }
    }

    public function forget(string $key): void
    {
        AiSetting::where('key', $key)->delete();
        if ($this->overrides !== null) {
            unset($this->overrides[$key]);
        }
    }

    /**
     * Remove every override, reverting the whole AI runtime configuration to
     * the .env baseline. Used by "Restore Last Save".
     */
    public function clear(): void
    {
        AiSetting::query()->delete();
        $this->overrides = [];
    }

    // ------------------------------------------------------------------
    // Effective-value helpers (override → config → default)
    // ------------------------------------------------------------------

    public function defaultProvider(): string
    {
        return (string) $this->get('ai.provider', config('ai.provider', 'gemini'));
    }

    public function defaultModel(): string
    {
        return (string) $this->get('ai.models.default', config('ai.models.default', 'gemini-3.6-flash'));
    }

    public function aiEnabled(): bool
    {
        return $this->bool('ai.enabled', (bool) config('ai.enabled', true));
    }

    public function fallbackEnabled(): bool
    {
        return $this->bool('ai.fallback', (bool) config('ai.fallback', true));
    }

    public function loggingEnabled(): bool
    {
        return $this->bool('ai.logging.enabled', (bool) config('ai.logging.enabled', true));
    }

    public function providerEnabled(string $provider): bool
    {
        $key = "ai.providers.{$provider}.enabled";

        return $this->bool($key, (bool) config("ai.providers.{$provider}.enabled", false));
    }

    public function providerModel(string $provider): string
    {
        $key = "ai.providers.{$provider}.model";

        return (string) $this->get(
            $key,
            config("ai.providers.{$provider}.default_model", config("services.{$provider}.model", ''))
        );
    }

    public function ollamaUrl(): string
    {
        return (string) $this->get('ai.providers.ollama.url', config('ai.providers.ollama.url', config('services.ollama.url', 'http://localhost:11434')));
    }

    public function pythonBridgeEnabled(): bool
    {
        return $this->bool('ai.python_bridge.enabled', (bool) config('ai.python_bridge.enabled', false));
    }
}