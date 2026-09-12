<?php

namespace App\Console\Commands;

use App\AI\Contracts\AIServiceInterface;
use App\AI\RAG\CotIndicatorRepository;
use App\AI\RAG\PPSTRubricRepository;
use Illuminate\Console\Command;

class TestAIService extends Command
{
    protected $signature = 'ai:test
        {--prompt= : Custom prompt to send (optional)}';

    protected $description = 'Test the AI service connectivity and generation';

    public function handle(): int
    {
        $this->info('ASPIRE AI Service Test');
        $this->newLine();

        // Check config
        $this->line('Configuration:');
        $this->line("  AI Enabled: " . (config('ai.enabled') ? 'Yes' : 'No'));
        $this->line("  AI Provider: " . config('ai.provider'));
        $this->line("  Gemini API Key: " . (config('services.gemini.api_key') ? 'Set' : 'Not Set'));
        $this->line("  AI Fallback: " . (config('ai.fallback') ? 'Enabled' : 'Disabled'));
        $this->newLine();

        // Test RAG repositories
        $this->line('RAG Repositories:');
        $indicatorRepo = app(CotIndicatorRepository::class);
        $domains = $indicatorRepo->getAllDomains();
        $this->line("  COT Domains Loaded: " . count($domains));
        foreach ($domains as $domain => $indicators) {
            $this->line("    - {$domain}: " . count($indicators) . " indicators");
        }
        $this->newLine();

        $rubricRepo = app(PPSTRubricRepository::class);
        $contextLength = strlen($rubricRepo->getSystemContext());
        $this->line("  PPST System Context: {$contextLength} characters");
        $this->newLine();

        // Test provider
        $provider = app(AIServiceInterface::class);

        $this->line("Testing provider ({$provider->getProviderName()})...");

        if (!$provider->isAvailable()) {
            $this->error("{$provider->getProviderName()} provider is not available (API key may be missing).");
            return Command::FAILURE;
        }

        $this->line("  Provider: " . $provider->getProviderName());
        $this->line("  Model: " . $provider->getModelName());
        $this->newLine();

        // Send test prompt
        $prompt = $this->option('prompt') ?: 'Respond with a JSON object containing keys: status (string "ok"), message (string "Hello from ASPIRE AI"), and timestamp (current ISO date).';
        $this->line("Sending test prompt...");

        $result = $provider->generateJson($prompt);

        if ($result === null) {
            $this->warn("Provider returned null. Trying text generation...");
            $textResult = $provider->generate($prompt);
            if ($textResult) {
                $this->line('Text response:');
                $this->line($textResult);
            } else {
                $this->error("Provider returned null for both JSON and text generation.");
                return Command::FAILURE;
            }
        } else {
            $this->line('JSON response:');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        }

        $this->newLine();
        $this->info('AI Service test completed successfully.');
        return Command::SUCCESS;
    }
}
