<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostConference extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'ai_comparison',
        'feedback',
        'conference_date',
        'start_time',
        'end_time',
        'location',
        'mode',
        'star_notes',
        'areas_for_improvement',
        'challenges_facing_teacher',
        'ideas_for_addressing_challenges',
        'prioritized_next_steps',
        'teacher_reflection',
        'supervisor_notes',
        'form_responses',
    ];

    protected $casts = [
        'ai_comparison' => 'array',
        'conference_date' => 'datetime',
        'form_responses' => 'array',
    ];

    public function startTimeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->start_time ? Carbon::parse($this->start_time)->format('h:i A') : null);
    }

    public function endTimeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->end_time ? Carbon::parse($this->end_time)->format('h:i A') : null);
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    /**
     * Raw AI comparison (plan vs actual) as plain text.
     * The `ai_comparison` column may hold a string or a JSON array,
     * so normalize both shapes here.
     */
    public function comparisonText(): string
    {
        $value = $this->ai_comparison;

        if (is_array($value)) {
            $parts = [];
            array_walk_recursive($value, function ($item) use (&$parts) {
                if (is_string($item) && trim($item) !== '') {
                    $parts[] = $item;
                } elseif (is_numeric($item)) {
                    $parts[] = (string) $item;
                }
            });

            return implode("\n\n", $parts);
        }

        return (string) ($value ?? '');
    }

    /**
     * Parse the AI comparison markdown into organized sections.
     * Returns ['alignment_between_plan_and_execution' => string|array, ...].
     * Falls back to ['raw' => text] when no headings can be detected,
     * or [] when there is no comparison text at all.
     */
    public function comparisonSections(): array
    {
        $text = $this->comparisonText();

        if (trim($text) === '') {
            return [];
        }

        $sections = [];
        $order = [];
        $current = null;

        $lines = preg_split('/\R/u', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $heading = $this->comparisonHeading($line);

            if ($heading !== null) {
                $current = $heading;
                if (! array_key_exists($current, $sections)) {
                    $sections[$current] = [];
                    $order[] = $current;
                }
                continue;
            }

            if ($current === null) {
                $current = 'overview';
                $sections[$current] = [];
                $order[] = $current;
            }

            $sections[$current][] = $this->stripInlineMarkdown($line);
        }

        if (count($order) <= 1 && isset($sections['overview'])) {
            return ['raw' => $text];
        }

        $result = [];
        foreach ($order as $key) {
            $content = $sections[$key];
            if (empty($content)) {
                continue;
            }

            $items = [];
            $prose = [];
            foreach ($content as $contentLine) {
                if (preg_match('/^(?:[-*•]|\d+[.)])\s+(.+)$/u', $contentLine, $m)) {
                    $items[] = trim($m[1]);
                } else {
                    $prose[] = $contentLine;
                }
            }

            if ($items !== [] && $prose === []) {
                $result[$key] = $items;
            } elseif ($items !== [] && $prose !== []) {
                $result[$key] = array_merge([implode(' ', $prose)], $items);
            } else {
                $result[$key] = implode(' ', $prose);
            }
        }

        if ($result === []) {
            return ['raw' => $text];
        }

        return $result;
    }

    /**
     * Detect a markdown section heading; returns the slug key or null.
     */
    protected function comparisonHeading(string $line): ?string
    {
        // **Bold heading** (optionally trailing colon).
        if (preg_match('/^\*\*(.+?)\*\*:?\s*$/u', $line, $m)) {
            return $this->slugSectionKey($m[1]);
        }

        // # Hash heading.
        if (preg_match('/^#{1,6}\s+(.+?)\s*:?\s*$/u', $line, $m)) {
            return $this->slugSectionKey($m[1]);
        }

        // Plain "Heading:" line (short, title-ish, nothing after the colon).
        if (preg_match('/^([^:]{3,80}):\s*$/u', $line, $m)
            && preg_match('/[a-zA-Z]/', $m[1])
            && substr_count($m[1], ' ') <= 7
        ) {
            return $this->slugSectionKey($m[1]);
        }

        return null;
    }

    protected function slugSectionKey(string $heading): string
    {
        $key = strtolower(trim($heading));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? $key;
        $key = trim($key, '_');

        return $key !== '' ? $key : 'section';
    }

    /**
     * Remove inline markdown markers so rendered text stays clean.
     */
    protected function stripInlineMarkdown(string $line): string
    {
        $line = preg_replace('/\*\*(.+?)\*\*/u', '$1', $line) ?? $line;
        $line = preg_replace('/(^|\s)_(.+?)_(\s|$)/u', '$1$2$3', $line) ?? $line;

        return $line;
    }
}
