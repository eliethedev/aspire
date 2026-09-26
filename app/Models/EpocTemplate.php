<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EpocTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'school_year',
        'is_active',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(EpocIndicator::class, 'template_id')->orderBy('order');
    }

    public function scopeActive($query, ?string $schoolYear = null)
    {
        $query->where('is_active', true);

        if ($schoolYear) {
            $query->where('school_year', $schoolYear);
        }

        return $query;
    }

    public static function activeFor(string $schoolYear): ?self
    {
        return static::with('indicators')->active($schoolYear)->first();
    }

    public function activate(): void
    {
        static::where('is_active', true)
            ->where('school_year', $this->school_year)
            ->update(['is_active' => false]);

        $this->update(['is_active' => true, 'version' => $this->version + 1]);
    }

    /**
     * Domains grouped for the rating sheet: domain => [indicator, ...].
     */
    public function groupedDomains(): array
    {
        return $this->indicators()
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->groupBy('domain')
            ->map(fn ($rows) => $rows->pluck('indicator')->all())
            ->toArray();
    }

    /**
     * Built-in DepEd CID EPOC domains. Used to seed the first template and
     * as a fallback when no active template exists for a school year.
     */
    public static function defaultDomains(): array
    {
        return [
            'Domain 1: Establishing a Warm and Clear Opening of the Post Observation Conference' => [
                'School Head acknowledges teacher\'s time (Thanks the teacher for allowing him/her to observe a class)',
                'School Head states the purpose of the conversation',
                'Talks in a voice that is warm, friendly and sincere',
            ],
            'Domain 2: Focus on what\'s going well' => [
                'Congratulates teachers for doing a job well (cite specific instances or teacher behavior/activities that are worth mentioning. Refer to the strengths noted)',
                'Asks the teacher to clearly state the objectives of the lesson',
                'Paraphrases and affirms the teacher\'s lesson objective (Asks what the pupils are able to demonstrate at the end of the lesson)',
                'Asks the teacher what she did to teach the lesson',
                'Asks teacher what made him/her happy about the delivery of the lesson. The SH listens intently to what the teacher is saying',
                'The SH affirms what the teacher considered as things that went well in the delivery of the lesson',
                'The SH extends the positive focus in addition to what the teacher identified as what went well, citing additional specific things referring to the strengths noted',
            ],
            'Domain 3: Identify Challenges Facing the Teacher' => [
                'The SH asks the teacher to tell which part of the lesson she thinks did not go well',
                'The SH paraphrases teacher\'s message to check whether they have the same understanding',
                'The SH enables the teacher to tell additional parts that did not go well by citing specific instances recorded in the strengths noted',
                'The SH avoids diversion and stays focused on the issues/data/documentation at hand when teacher makes caustic statements',
                'The SH is able to verify the teacher\'s perception about the identified areas for improvement',
            ],
            'Domain 4: Generating Ideas for Addressing Teacher\'s Challenges' => [
                'The SH guides the teacher in identifying possible strategies in addressing the challenges',
                'The SH helps solve the problem by offering ideas for improvement if and when the teacher is not able to do so',
                'The SH connects the teacher to available and appropriate resources to help address the challenges',
                'The SH avoids compromising statements that provide an excuse for poor performance',
            ],
            'Domain 5: Prioritizing the Next Steps' => [
                'The Teacher and the principal reviews ideas for improvement and assign priority to possible options',
            ],
            'Domain 6: Ending the Post Observation Conference' => [
                'The SH makes the teacher agree on the next steps by asking the teacher to choose whose help he/she would want to ask to assist in improving the identified challenges',
                'The SH enables the teacher to make a commitment regarding the next steps identified',
                'The SH thanks the teacher for the conversation',
            ],
        ];
    }
}
