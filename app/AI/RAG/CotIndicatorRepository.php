<?php

namespace App\AI\RAG;

use App\Services\CotIndicatorService;
use Illuminate\Support\Facades\Cache;

class CotIndicatorRepository
{
    protected CotIndicatorService $service;

    public function __construct(CotIndicatorService $service)
    {
        $this->service = $service;
    }

    public function getAllDomains(): array
    {
        $cacheKey = config('ai.cache.key_prefix', 'ai_rag_') . 'all_domains';
        $ttl = config('ai.cache.ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () {
            $version = $this->service->getLatestVersion();

            if (!$version || !isset($version['indicators'])) {
                return [];
            }

            $domains = [];
            foreach ($version['indicators'] as $indicator) {
                $domain = $indicator['domain'];
                if (!isset($domains[$domain])) {
                    $domains[$domain] = [];
                }
                $domains[$domain][] = [
                    'code' => $indicator['code'],
                    'description' => $indicator['description'],
                ];
            }

            return $domains;
        });
    }

    public function getIndicatorsByDomain(string $domain): array
    {
        $domains = $this->getAllDomains();
        return $domains[$domain] ?? [];
    }

    public function getIndicatorByCode(string $code): ?array
    {
        $domains = $this->getAllDomains();
        foreach ($domains as $domain => $indicators) {
            foreach ($indicators as $indicator) {
                if ($indicator['code'] === $code) {
                    return [
                        'code' => $indicator['code'],
                        'description' => $indicator['description'],
                        'domain' => $domain,
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Retrieve only the specified indicators, with their domain/strand info.
     *
     * @param  array<int, string>  $codes
     * @return array<int, array{code: string, description: string, domain: string}>
     */
    public function getIndicatorsByCodes(array $codes): array
    {
        $wanted = array_unique(array_map('trim', $codes));
        if ($wanted === []) {
            return [];
        }

        // Built on the cached getAllDomains() result (ai_rag_* cache strategy).
        $domains = $this->getAllDomains();

        $found = [];
        foreach ($domains as $domain => $indicators) {
            foreach ($indicators as $indicator) {
                if (in_array($indicator['code'], $wanted, true)) {
                    $found[] = [
                        'code' => $indicator['code'],
                        'description' => $indicator['description'],
                        'domain' => $domain,
                    ];
                }
            }
        }

        return $found;
    }

    /**
     * Lightweight keyword-relevance retrieval: scores indicators against a
     * free-text source (e.g. lesson plan content) using term overlap.
     *
     * @return array<int, array{code: string, description: string, domain: string}>
     */
    public function findRelevantIndicators(string $text, int $limit = 6): array
    {
        $domains = $this->getAllDomains();
        if ($domains === [] || trim($text) === '') {
            return [];
        }

        $tokens = $this->tokenize($text);
        if ($tokens === []) {
            return [];
        }

        $scored = [];
        foreach ($domains as $domain => $indicators) {
            foreach ($indicators as $indicator) {
                $indicatorTokens = $this->tokenize($indicator['code'].' '.$indicator['description']);
                if ($indicatorTokens === []) {
                    continue;
                }

                $overlap = count(array_intersect_key($tokens, $indicatorTokens));

                // Indicator codes present verbatim in the text are strong signals.
                if ($indicator['code'] !== '' && stripos($text, $indicator['code']) !== false) {
                    $overlap += 10;
                }

                if ($overlap > 0) {
                    $scored[] = [
                        'score' => $overlap,
                        'indicator' => [
                            'code' => $indicator['code'],
                            'description' => $indicator['description'],
                            'domain' => $domain,
                        ],
                    ];
                }
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(array_column($scored, 'indicator'), 0, max(1, $limit));
    }

    /**
     * Tokenize text into a frequency map of meaningful terms.
     *
     * @return array<string, int>
     */
    protected function tokenize(string $text): array
    {
        $words = str_word_count(strtolower($text), 1, '0123456789');
        $stopwords = ['the', 'and', 'for', 'with', 'that', 'this', 'from', 'are', 'was', 'were',
            'their', 'they', 'have', 'has', 'will', 'shall', 'into', 'onto', 'when', 'where',
            'which', 'who', 'whom', 'what', 'how', 'why', 'not', 'but', 'all', 'any', 'can',
            'may', 'per', 'via', 'use', 'used', 'using', 'such', 'each', 'other', 'more'];

        $tokens = [];
        foreach ($words as $word) {
            if (strlen($word) < 4 || in_array($word, $stopwords, true)) {
                continue;
            }
            $tokens[$word] = ($tokens[$word] ?? 0) + 1;
        }

        return $tokens;
    }

    public function getLatestVersionLabel(): string
    {
        $version = $this->service->getLatestVersion();
        return $version['label'] ?? 'PPST COT';
    }

    public function getSchoolYearVersions(): array
    {
        $versions = $this->service->getAllVersions();
        $result = [];

        foreach ($versions as $version) {
            $result[$version->school_year] = $version->label;
        }

        if (empty($result)) {
            foreach (config('cot.versions', []) as $schoolYear => $config) {
                $result[$schoolYear] = $config['label'];
            }
        }

        return $result;
    }

    /**
     * Clear the cached RAG context so admin edits take effect immediately.
     */
    public function clearCache(): void
    {
        $this->service->clearCache();
    }
}
