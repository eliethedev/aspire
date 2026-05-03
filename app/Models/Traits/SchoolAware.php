<?php

namespace App\Models\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait SchoolAware
{
    protected static function bootSchoolAware(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            if (app()->bound('current_school')) {
                $builder->where('school_id', app('current_school')->id);
            }
        });

        static::creating(function ($model) {
            if (app()->bound('current_school') && !$model->school_id) {
                $model->school_id = app('current_school')->id;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopeForSchool(Builder $builder, School $school): Builder
    {
        return $builder->where('school_id', $school->id);
    }

    public function scopeWithoutSchoolScope(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('school');
    }
}
