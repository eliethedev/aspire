<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'key',
        'label',
        'type',
        'placeholder',
        'help_text',
        'default_value',
        'validation_rules',
        'options',
        'order',
        'required',
        'column_map',
        'metadata',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'metadata' => 'array',
        'required' => 'boolean',
        'order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class, 'section_id');
    }

    public function getIsInputField(): bool
    {
        return !in_array($this->type, ['heading', 'paragraph', 'hr']);
    }

    public function getValidationRulesString(): array
    {
        $rules = $this->validation_rules ?? [];

        if ($this->required) {
            $rules[] = 'required';
        }

        return $rules;
    }
}
