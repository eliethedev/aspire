<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_id',
        'ppst_standard_id',
        'code',
        'description',
        'domain',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(CotIndicatorVersion::class, 'version_id');
    }

    public function ppstStandard(): BelongsTo
    {
        return $this->belongsTo(PpstStandard::class, 'ppst_standard_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
