<?php

namespace App\Models;

use App\Enums\TeacherCareerStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CotIndicatorVersion extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    public const DEFAULT_RATEE_ROLE = 'teacher';

    public const DEFAULT_OBSERVER_ROLES = ['supervisor', 'school_head'];

    public const DEFAULT_INSTRUMENT = 'cot';

    protected $fillable = [
        'school_year',
        'label',
        'is_default',
        'status',
        'ratee_role',
        'observer_roles',
        'framework',
        'career_track',
        'ratee_position',
        'instrument',
        'career_stage',
        'rating_scale',
        'rating_scale_css',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'observer_roles' => 'array',
        'rating_scale' => 'array',
        'rating_scale_css' => 'array',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(CotIndicator::class, 'version_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class, 'cot_indicator_version_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Whether the version contents may still be edited.
     * Published and archived versions are immutable.
     */
    public function canEdit(): bool
    {
        return ! $this->isPublished() && ! $this->isArchived();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    /**
     * The role of the ratee this COT form evaluates (teacher by default).
     */
    public function rateeRole(): string
    {
        return $this->ratee_role ?? self::DEFAULT_RATEE_ROLE;
    }

    /**
     * Human-readable label for the ratee role.
     */
    public function rateeRoleLabel(): string
    {
        return $this->ratee_role === 'school_head' ? 'School Head' : 'Teacher';
    }

    /**
     * The career stage this instrument applies to, or null when it is a
     * stage-agnostic instrument (applies to every teacher stage).
     */
    public function careerStage(): ?TeacherCareerStage
    {
        return $this->career_stage
            ? TeacherCareerStage::tryFrom($this->career_stage)
            : null;
    }

    /**
     * Whether this instrument targets the given teacher career stage.
     * Null career_stage means the instrument applies to all stages.
     */
    public function appliesToCareerStage(?string $careerStage): bool
    {
        return $this->career_stage === null
            || $this->career_stage === 'all'
            || ($careerStage !== null && $this->career_stage === $careerStage);
    }

    /**
     * The rating scale for this instrument (value => label), falling back to
     * the global config/cot.php scale when not overridden per version.
     */
    public function ratingScale(): array
    {
        return $this->rating_scale ?: config('cot.rating_scale', []);
    }

    /**
     * Tailwind classes used to colour rating buttons, falling back to the
     * global config/cot.php mapping when not overridden per version.
     */
    public function ratingScaleCss(): array
    {
        return $this->rating_scale_css ?: config('cot.rating_scale_css', []);
    }

    /**
     * Human-readable label for the career stage this instrument targets.
     * Teacher stages keep their existing position-group label (e.g.
     * "Teacher IV-VII"); PPSSH stages fall back to the shared
     * "Career Stage N" labels. Returns null for stage-agnostic instruments.
     */
    public function careerStageLabel(): ?string
    {
        if ($this->careerStage()) {
            return $this->careerStage()->label();
        }

        return $this->career_stage
            ? config("career_stages.career_stage_labels.{$this->career_stage}")
            : null;
    }

    /**
     * Human-readable standards framework label (e.g. "PPST").
     */
    public function frameworkLabel(): string
    {
        return $this->framework
            ? config("career_stages.frameworks.{$this->framework}.label", $this->framework)
            : '—';
    }

    /**
     * Human-readable career track label (e.g. "Classroom Teaching").
     */
    public function careerTrackLabel(): string
    {
        return $this->career_track
            ? config("career_stages.frameworks.{$this->framework}.tracks.{$this->career_track}.label", $this->career_track)
            : '—';
    }

    /**
     * Human-readable ratee position group label (e.g. "Teacher I-III").
     */
    public function rateePositionLabel(): string
    {
        return $this->ratee_position
            ? config("career_stages.frameworks.{$this->framework}.tracks.{$this->career_track}.positions.{$this->ratee_position}.label", $this->ratee_position)
            : '—';
    }

    /**
     * Human-readable instrument label (e.g. "COT").
     */
    public function instrumentLabel(): string
    {
        $key = $this->instrument ?: self::DEFAULT_INSTRUMENT;

        return config("career_stages.instruments.{$key}", strtoupper($key));
    }

    /**
     * The roles allowed to observe/rate with this COT form.
     */
    public function observerRoles(): array
    {
        return $this->observer_roles ?: self::DEFAULT_OBSERVER_ROLES;
    }

    /**
     * Whether the given user role may rate with this form.
     */
    public function allowsObserverRole(string $role): bool
    {
        return in_array($role, $this->observerRoles(), true);
    }

    /**
     * Publish this version. It becomes the default version.
     */
    public function publish(): void
    {
        static::where('id', '!=', $this->id)->update(['is_default' => false]);

        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'is_default' => true,
        ]);
    }

    /**
     * Move a published version back to draft so its contents can be revised.
     * Historical observations keep rendering via their pinned version.
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'is_default' => false,
        ]);
    }

    /**
     * Archive this version. It remains readable for historical
     * observations but is no longer used for new ones.
     */
    public function archive(): void
    {
        $this->update([
            'status' => self::STATUS_ARCHIVED,
            'is_default' => false,
        ]);
    }
}
