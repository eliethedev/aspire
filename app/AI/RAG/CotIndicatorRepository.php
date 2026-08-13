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
