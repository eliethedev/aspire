<?php

namespace Tests\Unit;

use App\Enums\TeacherCareerStage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherCareerStageTest extends TestCase
{
    public function test_ordered_lists_stages_in_official_precedence(): void
    {
        $this->assertSame(
            ['teacher_i_iii', 'teacher_iv_vii', 'master_teacher_i_ii', 'master_teacher_iii_v'],
            array_map(fn (TeacherCareerStage $s) => $s->value, TeacherCareerStage::ordered())
        );
    }

    public function test_labels_are_read_from_config(): void
    {
        $this->assertSame('Teacher I-III', TeacherCareerStage::TEACHER_I_III->label());
        $this->assertSame('Master Teacher III-V', TeacherCareerStage::MASTER_TEACHER_III_V->label());
    }

    #[DataProvider('positionProvider')]
    public function test_from_position_maps_recognised_positions(?string $position, ?string $expected): void
    {
        $this->assertSame($expected, TeacherCareerStage::fromPosition($position)?->value);
    }

    public static function positionProvider(): array
    {
        return [
            'teacher i' => ['Teacher I', 'teacher_i_iii'],
            'teacher 2' => ['Teacher 2', 'teacher_i_iii'],
            'teacher iii' => ['Teacher III', 'teacher_i_iii'],
            'teacher iv' => ['Teacher IV', 'teacher_iv_vii'],
            'teacher vii' => ['Teacher VII', 'teacher_iv_vii'],
            'teacher iv-vii' => ['Teacher IV-VII', 'teacher_iv_vii'],
            'master teacher i' => ['Master Teacher I', 'master_teacher_i_ii'],
            'master teacher 2' => ['Master Teacher 2', 'master_teacher_i_ii'],
            'master teacher iii' => ['Master Teacher III', 'master_teacher_iii_v'],
            'master teacher v' => ['Master Teacher V', 'master_teacher_iii_v'],
            'extra whitespace' => ['  Master   Teacher   II  ', 'master_teacher_i_ii'],
            'mixed case' => ['mAsTeR tEaChEr Iv', 'master_teacher_iii_v'],
            'head teacher not a stage' => ['Head Teacher III', null],
            'unknown' => ['Senior Teacher', null],
            'blank' => ['', null],
            'null' => [null, null],
        ];
    }

    public function test_options_are_keyed_by_value(): void
    {
        $options = TeacherCareerStage::options();

        $this->assertSame('Teacher IV-VII', $options['teacher_iv_vii']);
    }
}
