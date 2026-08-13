<?php

namespace App\Console\Commands;

use App\Enums\TeacherCareerStage;
use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeTeacherCareerStages extends Command
{
    protected $signature = 'teachers:normalize-career-stages';

    protected $description = 'Derive teachers.career_stage from existing free-text position values';

    public function handle(): int
    {
        $report = [
            'updated' => 0,
            'no_position' => 0,
            'unrecognized' => 0,
            'unchanged' => 0,
        ];

        DB::transaction(function () use (&$report) {
            Teacher::query()
                ->select(['id', 'position', 'career_stage'])
                ->orderBy('id')
                ->each(function (Teacher $teacher) use (&$report) {
                    if ($teacher->position === null || trim($teacher->position) === '') {
                        $report['no_position']++;

                        return;
                    }

                    $stage = TeacherCareerStage::fromPosition($teacher->position);

                    if ($stage === null) {
                        $report['unrecognized']++;

                        return;
                    }

                    if ($teacher->career_stage === $stage->value) {
                        $report['unchanged']++;

                        return;
                    }

                    $teacher->career_stage = $stage->value;
                    $teacher->save();
                    $report['updated']++;
                });
        });

        $this->info('Career stage normalisation complete.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Teachers updated', $report['updated']],
                ['Already correct', $report['unchanged']],
                ['Blank position (skipped)', $report['no_position']],
                ['Unrecognized position (skipped)', $report['unrecognized']],
            ]
        );

        return Command::SUCCESS;
    }
}
