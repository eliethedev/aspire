<?php

namespace App\AI\Services;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Exceptions\AIRateLimitException;
use App\AI\Providers\AIProviderManager;
use App\AI\RAG\PPSTRubricRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Smalot\PdfParser\Parser;

abstract class AIService
{
    protected AIServiceInterface $provider;

    protected PPSTRubricRepository $rubrics;

    protected AIProviderManager $manager;

    protected string $stage;

    public function __construct(
        AIServiceInterface $provider,
        PPSTRubricRepository $rubrics,
        ?AIProviderManager $manager = null,
    ) {
        $this->provider = $provider;
        $this->rubrics = $rubrics;
        $this->manager = $manager ?? app(AIProviderManager::class);
    }

    public function isAvailable(): bool
    {
        if (! config('ai.enabled', true)) {
            return false;
        }

        return $this->manager->fallbackChain($this->stage) !== [];
    }

    protected function getModelForStage(): string
    {
        [, $model] = $this->manager->resolveProviderAndModel($this->stage);

        return $model;
    }

    /**
     * Generation options resolved from task config, stage config, then defaults.
     */
    protected function getOptions(): array
    {
        $taskConfig = config("ai.tasks.{$this->stage}", []);
        $stageConfig = config("ai.stages.{$this->stage}", []);

        return [
            'temperature' => $taskConfig['temperature']
                ?? $stageConfig['temperature']
                ?? config('ai.generation.temperature', 0.5),
            'max_output_tokens' => $taskConfig['max_output_tokens']
                ?? $stageConfig['max_output_tokens']
                ?? config('ai.generation.max_output_tokens', 1024),
            'timeout' => $taskConfig['timeout']
                ?? config('ai.generation.timeout', 30),
        ];
    }

    protected function checkRateLimit(): void
    {
        $userId = Auth::id() ?? 'guest';
        $key = "ai:service:{$this->stage}:{$userId}";
        $limits = config("ai.tasks.{$this->stage}.rate_limit")
            ?? config("ai.rate_limits.operations.{$this->stage}", ['limit' => 20, 'decay' => 60]);

        if (RateLimiter::tooManyAttempts($key, $limits['limit'])) {
            $availableIn = RateLimiter::availableIn($key);
            throw new AIRateLimitException($this->stage, $availableIn);
        }

        RateLimiter::hit($key, $limits['decay']);
    }

    /**
     * Enforce the task's max_input_tokens budget. Oversized prompts are
     * truncated gracefully rather than silently sent in full.
     */
    protected function enforceInputLimit(string $prompt): string
    {
        $maxTokens = (int) (
            config("ai.tasks.{$this->stage}.max_input_tokens")
            ?? config('ai.generation.max_input_tokens', 8000)
        );

        $estimated = self::estimateTokens($prompt);

        if ($estimated <= $maxTokens) {
            return $prompt;
        }

        Log::channel(config('ai.logging.channel', 'stack'))->warning('AI input exceeded task token budget; truncating', [
            'task' => $this->stage,
            'estimated_tokens' => $estimated,
            'max_input_tokens' => $maxTokens,
        ]);

        $truncated = mb_substr($prompt, 0, $maxTokens * 4);

        return $truncated."\n\n[Content truncated due to input size limits]";
    }

    public static function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Validate that a decoded JSON response contains the required keys.
     */
    protected function validateJsonResponse(?array $data, array $requiredKeys = []): bool
    {
        if ($data === null) {
            return false;
        }

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $data)) {
                Log::channel(config('ai.logging.channel', 'stack'))->warning('AI response failed structure validation', [
                    'task' => $this->stage,
                    'missing_key' => $key,
                ]);

                return false;
            }
        }

        return true;
    }

    protected function generate(string $prompt, array $overrideOptions = []): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $this->checkRateLimit();

        $prompt = $this->enforceInputLimit($prompt);

        $this->log('sending', $prompt, ['options' => array_merge($this->getOptions(), $overrideOptions)]);

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $options = array_merge($this->getOptions(), $overrideOptions);

            // A length-truncated response (e.g. Gemini MAX_TOKENS) comes back
            // partially written. Retry with a larger output budget so the
            // response can complete, instead of storing a cut-off result.
            if ($attempt > 1) {
                $current = (int) ($options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024));
                $options['max_output_tokens'] = min($current * 2, 8192);
            }

            $result = $this->manager->run($this->stage, $prompt, $options, json: false);

            $this->log('response', $result['text'], [
                'provider' => $result['provider'],
                'fallback_used' => $result['fallback_used'],
            ]);

            if ($result['text'] === null) {
                return null;
            }

            if ($result['finish_reason'] === null) {
                return $result['text'];
            }

            Log::warning('AI response truncated; retrying with a larger output budget', [
                'stage' => $this->stage,
                'attempt' => $attempt,
                'finish_reason' => $result['finish_reason'],
            ]);
        }

        return $result['text'];
    }

    protected function generateJson(string $prompt, array $overrideOptions = []): ?array
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $this->checkRateLimit();

        $prompt = $this->enforceInputLimit($prompt);

        $this->log('sending', $prompt, ['options' => array_merge($this->getOptions(), $overrideOptions)]);

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $options = array_merge($this->getOptions(), $overrideOptions);

            // A length-truncated response (e.g. Gemini MAX_TOKENS) leaves the
            // JSON payload incomplete — sometimes still partially decodable.
            // Retry with a larger output budget so the response can complete.
            if ($attempt > 1) {
                $current = (int) ($options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024));
                $options['max_output_tokens'] = min($current * 2, 8192);
            }

            $result = $this->manager->run($this->stage, $prompt, $options, json: true);

            $this->log('response', $result['json'] ? json_encode($result['json']) : null, [
                'provider' => $result['provider'],
                'fallback_used' => $result['fallback_used'],
            ]);

            if ($result['json'] === null) {
                if ($attempt < $maxAttempts) {
                    Log::channel(config('ai.logging.channel', 'stack'))->warning('AI JSON response failed; retrying with a larger output budget', [
                        'stage' => $this->stage,
                        'attempt' => $attempt,
                    ]);

                    continue;
                }

                return null;
            }

            if ($result['finish_reason'] !== null && $attempt < $maxAttempts) {
                Log::channel(config('ai.logging.channel', 'stack'))->warning('AI JSON response truncated; retrying with a larger output budget', [
                    'stage' => $this->stage,
                    'attempt' => $attempt,
                    'finish_reason' => $result['finish_reason'],
                ]);

                continue;
            }

            return $result['json'];
        }

        return null;
    }

    protected function log(string $direction, ?string $content = null, array $metadata = []): void
    {
        if (! config('ai.logging.enabled', true)) {
            return;
        }

        $level = $direction === 'sending' ? 'debug' : 'info';

        // Only a short preview of content is logged; never full prompts or
        // responses (they may contain lesson-plan content).
        $preview = $content !== null ? mb_substr($content, 0, 150) : null;

        Log::channel(config('ai.logging.channel', 'stack'))->log($level, "AI.{$this->stage}.{$direction}", array_filter([
            'stage' => $this->stage,
            'provider' => $metadata['provider'] ?? $this->provider->getProviderName(),
            'model' => $this->getModelForStage(),
            'content_preview' => $preview,
            'metadata' => $metadata ?: null,
        ]));
    }

    protected function extractText(string $fullPath): string
    {
        if (! file_exists($fullPath) || ! is_readable($fullPath)) {
            return '';
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'pdf' => $this->extractPdfText($fullPath),
                'docx' => $this->extractDocxText($fullPath),
                'txt', 'md' => file_get_contents($fullPath) ?: '',
                default => '',
            };
        } catch (\Exception $e) {
            Log::warning("AIService: Failed to extract text from {$extension} file: {$e->getMessage()}");

            return '';
        }
    }

    protected function extractPdfText(string $fullPath): string
    {
        if (! class_exists(Parser::class)) {
            return '';
        }

        $parser = new Parser;
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        return mb_substr($text, 0, 8000);
    }

    protected function extractDocxText(string $fullPath): string
    {
        $zip = new \ZipArchive;
        if ($zip->open($fullPath) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $xml) {
            return '';
        }

        $xml = simplexml_load_string($xml);
        if (! $xml) {
            return '';
        }

        $namespaces = $xml->getNamespaces(true);
        $ns = $namespaces['w'] ?? '';
        $body = $xml->children($ns);

        $textParts = [];
        $paragraphs = $body->children($ns)->p ?? [];
        foreach ($paragraphs as $paragraph) {
            $parts = [];
            $runs = $paragraph->children($ns)->r ?? [];
            foreach ($runs as $run) {
                $t = $run->children($ns)->t ?? null;
                if ($t !== null) {
                    $parts[] = (string) $t;
                }
            }
            if ($parts) {
                $textParts[] = implode('', $parts);
            }
        }

        $text = implode("\n", $textParts);

        return mb_substr($text, 0, 8000);
    }

    protected function buildSystemPrompt(string $role): string
    {
        $rubricContext = $this->rubrics->getSystemContext();

        return <<<SYSTEM
You are SuperviseBot, an expert instructional coach and classroom observation analyst for the Department of Education (DepEd) Philippines.

Your role: {$role}

Core Responsibilities:
- Analyze COT (Classroom Observation Tool) ratings and provide actionable feedback
- Generate professional coaching language aligned with PPST standards
- Identify instructional gaps and suggest targeted interventions
- Support supervisors in drafting constructive post-observation conference forms

PPST Context:
{$rubricContext}

Guidelines:
- Be specific, actionable, and professional
- Base all recommendations on the actual COT data provided
- Use DepEd-aligned terminology
- Prioritize teacher growth and development
- Keep responses concise and focused
- You assist the supervisor; the supervisor remains responsible for all final decisions, including ratings
SYSTEM;
    }
}
