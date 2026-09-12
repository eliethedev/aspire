<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreObservationPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'lesson_plan_file',
        'ai_insights',
        'ai_insights_meta',
        'ai_insights_reviewed',
        'suggested_focus',
        'supervisor_notes',
        'observation_tool',
        'form_responses',
    ];

    protected $casts = [
        'ai_insights' => 'array',
        'ai_insights_meta' => 'array',
        'ai_insights_reviewed' => 'boolean',
        'suggested_focus' => 'array',
        'form_responses' => 'array',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    protected static $insightSectionLabels = [
        'lesson_focus' => 'Lesson Focus',
        'key_things_to_watch' => 'Key Things to Watch',
        'conference_talking_points' => 'Pre-Conference Talking Points',
        'potential_challenges' => 'Potential Challenges',
    ];

    /**
     * Raw insight text / array for templates.
     */
    public function insightsText(): string
    {
        $value = $this->ai_insights;

        return is_array($value) ? (json_encode($value, JSON_PRETTY_PRINT) ?: '') : (string) ($value ?? '');
    }

    /**
     * Parse the AI insights markdown into organized sections.
     * Returns ['lesson_focus' => string, 'key_things_to_watch' => [...], ...] etc.
     * Falls back to a single 'raw' entry when it can't be parsed.
     */
    public function insightsSections(): array
    {
        $text = $this->insightsText();

        if (trim($text) === '') {
            return [];
        }

        $sections = [
            'lesson_focus' => null,
            'key_things_to_watch' => null,
            'conference_talking_points' => null,
            'potential_challenges' => null,
        ];

        $headingKeys = [
            'lesson focus' => 'lesson_focus',
            'key things to watch' => 'key_things_to_watch',
            'conference talking points' => 'conference_talking_points',
            'pre conference talking points' => 'conference_talking_points',
            'potential challenges' => 'potential_challenges',
        ];

        $lines = preg_split('/\R/u', $text);
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $heading = strtolower(preg_replace('/^#{1,6}\s*/', '', $line));
            $heading = rtrim($heading, ':');
            $heading = str_replace(['_', '-'], ' ', $heading);

            if (isset($headingKeys[$heading])) {
                $current = $headingKeys[$heading];
                continue;
            }

            if ($current === null) {
                continue;
            }

            $sections[$current][] = $line;
        }

        $result = [];
        $parsed = false;

        foreach ($sections as $key => $content) {
            if (empty($content)) {
                continue;
            }

            if ($key === 'lesson_focus') {
                $result[$key] = $this->stripInlineMarkdown(implode(' ', $content));
            } else {
                $result[$key] = $this->reduceToItems($content);
            }
            $parsed = true;
        }

        if (! $parsed) {
            return ['raw' => $this->stripInlineMarkdown($text)];
        }

        return $result;
    }

    protected function reduceToItems(array $lines): array
    {
        $items = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^[-*•]\s*/', '', $line));
            if ($line !== '') {
                $items[] = $this->stripInlineMarkdown($line);
            }
        }

        return $items;
    }

    protected function stripInlineMarkdown(string $text): string
    {
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text);
        $text = preg_replace('/`(.+?)`/s', '$1', $text);

        return str_replace(['**', '__', '`'], '', $text);
    }

    public function insightSectionLabels(): array
    {
        return self::$insightSectionLabels;
    }
}
