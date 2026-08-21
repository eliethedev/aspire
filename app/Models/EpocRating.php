<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpocRating extends Model
{
    use HasFactory;

    protected $table = 'epoc_ratings';

    protected $fillable = [
        'epoc_evaluation_id',
        'domain',
        'indicator',
        'rating',
        'comments',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(EpocEvaluation::class, 'epoc_evaluation_id');
    }
}
