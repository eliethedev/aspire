<?php

namespace App\AI\RAG;

use Illuminate\Support\Facades\Cache;

class CotIndicatorRepository
{
    public function getAllDomains(): array
    {
        $cacheKey = config('ai.cache.key_prefix', 'ai_rag_') . 'all_domains';
        $ttl = config('ai.cache.ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () {
            $versions = config('cot.versions', []);
            $latestVersion = end($versions);

            if (!$latestVersion || !isset($latestVersion['indicators'])) {
                return [];
            }

            $domains = [];
            foreach ($latestVersion['indicators'] as $indicator) {
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

    public function getLatestVersionLabel(): string
    {
        $versions = config('cot.versions', []);
        $latest = end($versions);
        return $latest['label'] ?? 'PPST COT';
    }

    public function getSchoolYearVersions(): array
    {
        $versions = config('cot.versions', []);
        $result = [];

        foreach ($versions as $schoolYear => $config) {
            $result[$schoolYear] = $config['label'];
        }

        return $result;
    }
}
