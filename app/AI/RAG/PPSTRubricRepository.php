<?php

namespace App\AI\RAG;

use Illuminate\Support\Facades\Cache;

class PPSTRubricRepository
{
    protected CotIndicatorRepository $indicators;

    public function __construct(CotIndicatorRepository $indicators)
    {
        $this->indicators = $indicators;
    }

    public function getSystemContext(): string
    {
        $cacheKey = config('ai.cache.key_prefix', 'ai_rag_') . 'system_context';
        $ttl = config('ai.cache.ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () {
            $domains = $this->indicators->getAllDomains();
            $lines = [];

            foreach ($domains as $domain => $indicators) {
                $lines[] = "- {$domain}:";
                foreach ($indicators as $indicator) {
                    $lines[] = "    {$indicator['code']}: {$indicator['description']}";
                }
            }

            return implode("\n", $lines);
        });
    }

    public function getRelevantForLessonPlan(string $subject, string $gradeLevel): string
    {
        $systemContext = $this->getSystemContext();
        return "Relevant PPST Standards for {$subject} Grade {$gradeLevel}:\n\n{$systemContext}";
    }

    public function getDomainContext(string $domain): string
    {
        $indicators = $this->indicators->getIndicatorsByDomain($domain);
        $lines = ["Domain: {$domain}"];

        foreach ($indicators as $indicator) {
            $lines[] = "- {$indicator['code']}: {$indicator['description']}";
        }

        return implode("\n", $lines);
    }

    /**
     * Targeted RAG context: only the requested indicators (with their
     * domain/strand grouping). Used to avoid sending the full rubric.
     *
     * @param  array<int, string>  $codes
     */
    public function getIndicatorsContext(array $codes): string
    {
        $indicators = $this->indicators->getIndicatorsByCodes($codes);

        if ($indicators === []) {
            return '';
        }

        $grouped = [];
        foreach ($indicators as $indicator) {
            $grouped[$indicator['domain']][] = $indicator;
        }

        $lines = ['Applicable PPST/COT Indicators:'];
        foreach ($grouped as $domain => $items) {
            $lines[] = "- {$domain}:";
            foreach ($items as $item) {
                $lines[] = "    {$item['code']}: {$item['description']}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Targeted RAG context for whole domains.
     *
     * @param  array<int, string>  $domains
     */
    public function getDomainsContext(array $domains): string
    {
        $parts = [];
        foreach ($domains as $domain) {
            $context = $this->getDomainContext($domain);
            if ($context !== '') {
                $parts[] = $context;
            }
        }

        return implode("\n\n", $parts);
    }

    public function getRatingScaleContext(): string
    {
        $scale = config('cot.rating_scale', []);
        $lines = ['COT Rating Scale (DepEd):'];

        foreach ($scale as $rating => $label) {
            $lines[] = "- {$rating} = {$label}";
        }

        return implode("\n", $lines);
    }
}
