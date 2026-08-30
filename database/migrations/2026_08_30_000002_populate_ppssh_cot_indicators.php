<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $versions = DB::table('cot_indicator_versions')
            ->where('ratee_role', 'school_head')
            ->where('instrument', 'cot')
            ->get();

        if ($versions->isEmpty()) {
            return;
        }

        foreach ($versions as $version) {
            DB::table('cot_indicator_versions')
                ->where('id', $version->id)
                ->update([
                    'framework'   => 'ppssh',
                    'career_track'=> 'school_administration',
                    'ratee_position' => null,
                ]);

            $indicators = $this->ppsshIndicators();

            DB::table('cot_indicators')->insertOrIgnore(array_map(
                fn (array $ind) => array_merge($ind, [
                    'version_id' => $version->id,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]),
                $indicators
            ));
        }
    }

    public function down(): void
    {
        $codes = array_map(fn (array $ind) => $ind['code'], $this->ppsshIndicators());

        $versionIds = DB::table('cot_indicator_versions')
            ->where('ratee_role', 'school_head')
            ->where('instrument', 'cot')
            ->pluck('id');

        if ($versionIds->isEmpty()) {
            return;
        }

        DB::table('cot_indicators')
            ->whereIn('version_id', $versionIds)
            ->whereIn('code', $codes)
            ->delete();
    }

    /**
     * Indicators aligned with the Philippine Professional Standards for
     * School Heads (PPSSH, DepEd Order 24, s. 2020): 5 domains, 34 strands.
     *
     * @return array<int, array{code: string, description: string, domain: string, sort_order: int}>
     */
    private function ppsshIndicators(): array
    {
        $rows = [];

        $domains = [
            '1' => 'Domain 1: Leading Strategically',
            '2' => 'Domain 2: Managing School Operations and Resources',
            '3' => 'Domain 3: Focusing on Teaching and Learning',
            '4' => 'Domain 4: Developing Self and Others',
            '5' => 'Domain 5: Building Connections',
        ];

        foreach ($this->strands() as $code => $description) {
            $rows[] = [
                'code'        => $code,
                'description' => $description,
                'domain'      => $domains[explode('.', $code)[0]],
                'sort_order'  => count($rows) + 1,
            ];
        }

        return $rows;
    }

    /**
     * PPSSH strands (1.1 ... 5.5) mapped to observable school-head behaviors.
     *
     * @return array<string, string>
     */
    private function strands(): array
    {
        return [
            '1.1' => 'Develops and communicates a shared school vision, mission and core values.',
            '1.2' => 'Leads school planning and implements programs aligned with the school\'s vision, mission and goals.',
            '1.3' => 'Implements and reviews education policies to ensure compliance and alignment with school goals.',
            '1.4' => 'Uses research-based knowledge and innovative practices to improve school performance.',
            '1.5' => 'Designs and implements programs that address the specific needs of the school and its learners.',
            '1.6' => 'Provides opportunities for learners to participate in school decision-making and program design.',
            '1.7' => 'Uses monitoring and evaluation processes and tools to promote learner achievement.',

            '2.1' => 'Manages and maintains accurate, accessible and secure school records.',
            '2.2' => 'Manages school finances transparently, efficiently and in accordance with regulations.',
            '2.3' => 'Maintains functional, safe and adequate school facilities and equipment.',
            '2.4' => 'Manages school staff effectively through clear roles, supervision and support.',
            '2.5' => 'Implements school safety measures for disaster preparedness, mitigation and resiliency.',
            '2.6' => 'Anticipates and responds to emerging opportunities and challenges affecting the school.',

            '3.1' => 'Leads the school-based review, contextualization and implementation of learning standards.',
            '3.2' => 'Ensures teaching practices align with professional teaching standards and sound pedagogies.',
            '3.3' => 'Provides constructive and timely feedback on teacher performance.',
            '3.4' => 'Monitors and analyzes learner achievement and other performance indicators to drive improvement.',
            '3.5' => 'Ensures valid, reliable and learner-responsive assessment practices.',
            '3.6' => 'Fosters a safe, inclusive and learner-centered learning environment.',
            '3.7' => 'Promotes learner career awareness and access to career and further education opportunities.',
            '3.8' => 'Implements fair, consistent and positive learner discipline policies and practices.',

            '4.1' => 'Demonstrates commitment to continuous personal and professional development.',
            '4.2' => 'Engages in regular professional reflection and learning to improve practice.',
            '4.3' => 'Builds and sustains professional networks to support school improvement.',
            '4.4' => 'Manages performance effectively by setting expectations, monitoring progress and addressing gaps.',
            '4.5' => 'Facilitates the professional development of school personnel.',
            '4.6' => 'Develops leadership capacity in individuals and teams across the school.',
            '4.7' => 'Pursues and protects the general welfare of school personnel.',
            '4.8' => 'Implements fair rewards and recognition mechanisms for personnel and learners.',

            '5.1' => 'Manages diverse relationships with stakeholders to advance the school\'s goals.',
            '5.2' => 'Coordinates with school organizations and partner organizations for shared goals.',
            '5.3' => 'Promotes inclusive practices that respect diversity and ensure equitable access.',
            '5.4' => 'Communicates effectively with internal and external stakeholders.',
            '5.5' => 'Engages the wider community as partners in the education of learners.',
        ];
    }
};