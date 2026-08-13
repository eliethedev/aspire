<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single indicator in the Philippine Professional Standards for Teachers
 * (PPST) framework. PPST is the standards foundation of the system; COT forms
 * (CotIndicatorVersion) assemble a subset of these standards into the
 * classroom-observation rating sheet.
 */
class PpstStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'strand',
        'indicator_code',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function cotIndicators(): HasMany
    {
        return $this->hasMany(CotIndicator::class, 'ppst_standard_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('indicator_code');
    }
}
