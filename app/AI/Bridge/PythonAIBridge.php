<?php

namespace App\AI\Bridge;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PythonAIBridge
{
    protected string $scriptPath;

    protected string $pythonPath;

    protected int $timeout;

    public function __construct()
    {
        $this->scriptPath = config('ai.python_bridge.script_path', base_path('python/ai_bridge.py'));
        $this->pythonPath = config('ai.python_bridge.python_path', 'python3');
        $this->timeout = config('ai.python_bridge.timeout', 60);
    }

    public function isEnabled(): bool
    {
        return config('ai.python_bridge.enabled', false) && file_exists($this->scriptPath);
    }

    public function generate(string $provider, string $model, string $prompt, array $options = []): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $args = [
            $this->pythonPath,
            $this->scriptPath,
            '--action', 'generate',
            '--provider', $provider,
            '--model', $model,
            '--prompt', $prompt,
            '--temperature', (string) ($options['temperature'] ?? 0.5),
            '--max-tokens', (string) ($options['max_output_tokens'] ?? 1024),
        ];

        if (! empty($options['api_key'])) {
            $args[] = '--api-key';
            $args[] = $options['api_key'];
        }

        return $this->execute($args);
    }

    public function generateJson(string $provider, string $model, string $prompt, array $options = []): ?array
    {
        $result = $this->generate($provider, $model, $prompt, $options);

        if ($result && $result['success'] && isset($result['json'])) {
            return $result['json'];
        }

        return null;
    }

    public function batch(array $items, array $fallbackChain = []): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'ai_batch_');
        file_put_contents($tempFile, json_encode([
            'items' => $items,
            'fallback_chain' => $fallbackChain,
        ]));

        try {
            $args = [
                $this->pythonPath,
                $this->scriptPath,
                '--action', 'batch',
                '--config', $tempFile,
            ];

            return $this->execute($args) ?? [];
        } finally {
            @unlink($tempFile);
        }
    }

    public function status(): array
    {
        if (! $this->isEnabled()) {
            return ['error' => 'Python bridge not enabled or script not found'];
        }

        $args = [
            $this->pythonPath,
            $this->scriptPath,
            '--action', 'status',
        ];

        return $this->execute($args) ?? [];
    }

    protected function execute(array $args): ?array
    {
        try {
            $process = new Process($args);
            $process->setTimeout($this->timeout);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('Python AI Bridge error', [
                    'stderr' => $process->getErrorOutput(),
                    'exit_code' => $process->getExitCode(),
                ]);

                return null;
            }

            $output = trim($process->getOutput());
            $decoded = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Python AI Bridge: invalid JSON output', ['output' => $output]);

                return null;
            }

            return $decoded;
        } catch (\Exception $e) {
            Log::error('Python AI Bridge exception: '.$e->getMessage());

            return null;
        }
    }
}
