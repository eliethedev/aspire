<?php

namespace App\AI\Services;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Exceptions\AIRateLimitException;
use App\AI\RAG\PPSTRubricRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Smalot\PdfParser\Parser;

abstract class AIService
{
    protected AIServiceInterface $provider;

    protected PPSTRubricRepository $rubrics;

    protected string $stage;

    public function __construct(AIServiceInterface $provider, PPSTRubricRepository $rubrics)
    {
        $this->provider = $provider;
        $this->rubrics = $rubrics;
    }

    public function isAvailable(): bool
    {
        if (! config('ai.enabled', true)) {
            return false;
        }

        return $this->provider->isAvailable();
    }

    protected function getModelForStage(): string
    {
        $model = config("ai.models.{$this->stage}", config('ai.models.default', 'gemini-2.0-flash'));

        if (is_array($model)) {
            $model = $model['model'] ?? config('ai.models.default', 'gemini-2.0-flash');
        }

        return $model;
    }

    protected function getOptions(): array
    {
        $stageConfig = config("ai.stages.{$this->stage}", []);

        return [
            'temperature' => $stageConfig['temperature'] ?? config('ai.generation.temperature', 0.5),
            'max_output_tokens' => $stageConfig['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024),
        ];
    }

    protected function checkRateLimit(): void
    {
        $userId = Auth::id() ?? 'guest';
        $key = "ai:service:{$this->stage}:{$userId}";
        $limits = config("ai.rate_limits.operations.{$this->stage}", ['limit' => 20, 'decay' => 60]);

        if (RateLimiter::tooManyAttempts($key, $limits['limit'])) {
            $availableIn = RateLimiter::availableIn($key);
            throw new AIRateLimitException($this->stage, $availableIn);
        }

        RateLimiter::hit($key, $limits['decay']);
    }

    protected function generate(string $prompt, array $overrideOptions = []): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $this->checkRateLimit();

        $stageModel = $this->getModelForStage();
        $this->provider->setModel($stageModel);

        $options = array_merge($this->getOptions(), $overrideOptions);

        $this->log('sending', $prompt, $options);
        $result = $this->provider->generate($prompt, $options);
        $this->log('response', $result);

        return $result;
    }

    protected function generateJson(string $prompt, array $overrideOptions = []): ?array
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $this->checkRateLimit();

        $stageModel = $this->getModelForStage();
        $this->provider->setModel($stageModel);

        $options = array_merge($this->getOptions(), $overrideOptions);

        $this->log('sending', $prompt, $options);
        $result = $this->provider->generateJson($prompt, $options);
        $this->log('response', $result ? json_encode($result) : null);

        return $result;
    }

    protected function log(string $direction, ?string $content = null, array $metadata = []): void
    {
        if (! config('ai.logging.enabled', true)) {
            return;
        }

        $level = $direction === 'sending' ? 'debug' : 'info';
        Log::channel(config('ai.logging.channel', 'stack'))->log($level, "AI.{$this->stage}.{$direction}", array_filter([
            'stage' => $this->stage,
            'provider' => $this->provider->getProviderName(),
            'model' => $this->getModelForStage(),
            'content' => $content,
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
SYSTEM;
    }
}
