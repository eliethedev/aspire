<?php

namespace App\Models\Traits;

use App\Models\AiInsight;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAIInsights
{
    public function aiInsights(): MorphMany
    {
        return $this->morphMany(AiInsight::class, 'insightable');
    }

    public function getLatestAiInsight(?string $type = null): ?AiInsight
    {
        $query = $this->aiInsights();
        if ($type) {
            $query->byType($type);
        }
        return $query->latest()->first();
    }

    public function addAiInsight(array $data): AiInsight
    {
        return $this->aiInsights()->create($data);
    }
}
