<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'key',
        'label',
        'order',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'template_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class, 'section_id')->orderBy('order');
    }
}
