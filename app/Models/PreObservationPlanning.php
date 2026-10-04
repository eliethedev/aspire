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
     *
     * Tolerant of real-world output shapes: `##` headings, **bold** headings,
     * numbered headings ("1. Lesson Focus"), trailing colons, inline
     * "**Heading:** content" lines, and the offline-fallback heading set
     * ("Key Focus Areas", "Teaching Strategies to Observe", ...).
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
            'lesson plan goals & focus' => 'lesson_focus',
            'lesson plan goals and focus' => 'lesson_focus',
            'key things to watch' => 'key_things_to_watch',
            'key focus areas' => 'key_things_to_watch',
            'teaching strategies to observe' => 'key_things_to_watch',
            'teaching strategies' => 'key_things_to_watch',
            'materials & resources' => 'key_things_to_watch',
            'materials and resources' => 'key_things_to_watch',
            'assessment methods' => 'key_things_to_watch',
            'conference talking points' => 'conference_talking_points',
            'pre conference talking points' => 'conference_talking_points',
            'pre conference discussion points' => 'conference_talking_points',
            'potential challenges' => 'potential_challenges',
            'lesson plan completeness' => 'potential_challenges',
            'lesson plan note' => 'potential_challenges',
        ];

        $lines = preg_split('/\R/u', $text);
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === '---' || $line === '***') {
                continue;
            }

            // Skip the fallback title line ("Pre-Observation Insights for <name>").
            if (preg_match('/^(\*\*|__)?pre[\s\-_]*observation insights for\b/i', $line)) {
                continue;
            }

            [$isHeading, $key, $inline] = $this->matchInsightHeading($line, $headingKeys);
            if ($isHeading) {
                $current = $key;
                if ($inline !== '') {
                    $sections[$current][] = $inline;
                }
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
                $cleaned = array_map(
                    fn ($line) => $this->stripInlineMarkdown(trim((string) preg_replace('/^(\d+[.)]\s*|[-*•]\s*)+/u', '', trim($line)))),
                    $content
                );
                $cleaned = array_values(array_filter($cleaned, fn ($line) => $line !== ''));
                if ($cleaned !== []) {
                    $result[$key] = implode(' ', $cleaned);
                    $parsed = true;
                    continue;
                }
                continue;
            }
                $result[$key] = $this->reduceToItems($content);
                $parsed = true;
        }

        if (! $parsed) {
            return ['raw' => $this->stripInlineMarkdown($text)];
        }

        return $result;
    }

    /**
     * Check whether a line is a section heading.
     * Returns [isHeading, sectionKey, inlineContent].
     */
    protected function matchInsightHeading(string $line, array $headingKeys): array
    {
        $candidate = $line;

        // Split "**Heading:** inline content" — only when the head part is a
        // known section so body sentences containing colons are left alone.
        if (str_contains($candidate, ':')) {
            [$maybeHead, $rest] = explode(':', $candidate, 2);
            $normHead = $this->normalizeInsightHeading($maybeHead);
            if (isset($headingKeys[$normHead])) {
                $rest = trim($rest, "*`_ \t\n\r\0\x0B");
                $rest = trim((string) preg_replace('/^(\d+[.)]\s*|[-*•]\s*)+/u', '', $rest));
                if ($rest !== '') {
                    $rest = $this->stripInlineMarkdown($rest);
                }

                return [true, $headingKeys[$normHead], $rest];
            }
        }

        $norm = $this->normalizeInsightHeading($candidate);
        if (isset($headingKeys[$norm])) {
            return [true, $headingKeys[$norm], ''];
        }

        return [false, null, ''];
    }

    /**
     * Normalize a heading for lookup: drop markdown/number/bullet markers,
     * trailing colons, and punctuation quirks.
     */
    protected function normalizeInsightHeading(string $text): string
    {
        $text = trim($text);
        $text = (string) preg_replace('/^#{1,6}\s*/u', '', $text);
        $text = (string) preg_replace('/^(\d+[.)]\s*|[-*•]\s*)+/u', '', $text);
        // Strip surrounding emphasis markers (**bold**, __bold__, `code`).
        // trim() removes them one char at a time from each end, so both
        // "**Heading**" and the "**Heading*" fragment left by a colon-split
        // (from "**Heading:** content") normalize cleanly.
        $text = trim($text, "*`_ \t\n\r\0\x0B");
        $text = rtrim($text, ':');
        $text = trim($text, "*`_ \t\n\r\0\x0B");
        $text = str_replace(['_', '-', '—', '–'], ' ', $text);
        $text = (string) preg_replace('/\s+/', ' ', $text);

        return strtolower($text);
    }

    protected function reduceToItems(array $lines): array
    {
        $items = [];
        foreach ($lines as $line) {
            $line = trim((string) preg_replace('/^(\d+[.)]\s*|[-*•]\s*)/u', '', trim($line)));
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
