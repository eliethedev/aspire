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
