<?php

namespace Tests\Feature;

use App\Models\AiFeedback;
use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\PostConference;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CotDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CotDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->supervisor = User::factory()->create(['role' => 'supervisor', 'name' => 'Jane Observer']);
        $this->teacherUser = User::factory()->create(['role' => 'teacher', 'name' => 'John Doe']);
        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
            'position' => 'Teacher III',
        ]);
    }

    public function test_can_generate_cot_document_for_completed_observation(): void
    {
        $observation = $this->completedObservation();

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.cot-document.generate', $observation));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'COT document generated successfully.');

        $observation->refresh();

        $this->assertNotNull($observation->cot_document_path);
        $this->assertNotNull($observation->cot_document_generated_at);
        $this->assertTrue(Storage::disk('public')->exists($observation->cot_document_path));
        $this->assertSame('COT_John-Doe_2026-2027_2026-09-01.docx', basename($observation->cot_document_path));
    }

    public function test_generated_docx_contains_teacher_and_observer_information(): void
    {
        $observation = $this->completedObservation();

        $xml = $this->docxXml($observation);

        $this->assertStringContainsString('John Doe', $xml);
        $this->assertStringContainsString('Teacher III', $xml);
        $this->assertStringContainsString('Jane Observer', $xml);
    }

    public function test_generated_docx_contains_observation_information(): void
    {
        $observation = $this->completedObservation();

        $xml = $this->docxXml($observation);

        $this->assertStringContainsString('September 01, 2026', $xml);
        $this->assertStringContainsString('Mathematics', $xml);
        $this->assertStringContainsString('Grade 7 - Diamond', $xml);
        $this->assertStringContainsString('2026-2027', $xml);
        $this->assertStringContainsString('Quarter 2', $xml);
    }

    public function test_generated_docx_contains_indicators_in_template_order(): void
    {
        $observation = $this->completedObservation();

        $xml = $this->docxXml($observation);

        $pos11 = strpos($xml, '1.1.1.');
        $pos12 = strpos($xml, '1.1.2.');
        $pos13 = strpos($xml, '1.1.3.');

        $this->assertNotFalse($pos11);
        $this->assertNotFalse($pos12);
        $this->assertNotFalse($pos13);
        $this->assertLessThan($pos12, $pos11);
        $this->assertLessThan($pos13, $pos12);

        $this->assertStringContainsString('PPST Indicators', $xml);
        $this->assertStringContainsString('Comments', $xml);
        $this->assertStringContainsString('Content Knowledge and Pedagogy', $xml);
    }

    public function test_rating_marks_are_drawn_correctly_for_rated_and_not_observed(): void
    {
        $observation = $this->completedObservation();

        $html = $this->documentHtml($observation);

        // 1.1.1 rated 6 -> X in the "6" column
        $this->assertStringContainsString('class="col-rating marked">X</td>', $html);
        // 1.1.3 not observed -> X in the NO column
        $this->assertStringContainsString('class="col-no marked">X</td>', $html);
    }

    public function test_comments_are_included_in_the_document(): void
    {
        $observation = $this->completedObservation();

        $html = $this->documentHtml($observation);

        $this->assertStringContainsString('Excellent delivery of the lesson.', $html);
    }

    public function test_post_conference_section_is_included_when_completed(): void
    {
        $observation = $this->completedObservation();

        $html = $this->documentHtml($observation);

        $this->assertStringContainsString('POST-OBSERVATION CONFERENCE SUMMARY', $html);
        $this->assertStringContainsString('Great conference feedback.', $html);
    }

    public function test_ai_feedback_is_marked_as_reference_only_and_never_replaces_ratings(): void
    {
        $observation = $this->completedObservation();

        $html = $this->documentHtml($observation);

        $this->assertStringContainsString('AI-GENERATED FEEDBACK', $html);
        $this->assertStringContainsString('Does Not Replace Official Ratings', $html);
        $this->assertStringContainsString('Strong use of scaffolding techniques.', $html);

        // Official rating (6) is still marked in the rating table
        $this->assertStringContainsString('class="col-rating marked">X</td>', $html);
    }

    public function test_document_is_built_from_historical_cot_ratings_snapshot_not_the_live_version(): void
    {
        $version = CotIndicatorVersion::factory()->create([
            'school_year' => '2026-2027',
            'label' => 'COT 2026-2027',
        ]);

        $rating = CotRating::factory()->create([
            'observation_id' => $this->completedObservation()->id,
            'indicator_code' => '2.3.1',
            'domain' => 'Learning Environment',
            'indicator' => 'SNAPSHOT-INDICATOR-TEXT',
            'rating' => 5,
            'not_observed' => false,
            'comments' => 'Snapshot comment.',
        ]);

        $observation = $rating->observation;
        $observation->update(['cot_indicator_version_id' => $version->id]);

        $firstXml = $this->docxXml($observation);
        $this->assertStringContainsString('SNAPSHOT-INDICATOR-TEXT', $firstXml);

        // Change the live version; the regenerated document must still use the snapshot.
        $version->label = 'COT 2027-2028';
        $version->save();

        $secondXml = $this->docxXml($observation);
        $this->assertStringContainsString('SNAPSHOT-INDICATOR-TEXT', $secondXml);
        $this->assertStringNotContainsString('2027-2028', $secondXml);
    }

    public function test_generating_document_does_not_modify_existing_cot_ratings(): void
    {
        $observation = $this->completedObservation();
        $commentBefore = $observation->cotRatings()->first()->comments;

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.cot-document.generate', $observation));

        $observation->refresh();

        $this->assertDatabaseCount('cot_ratings', 3);
        $this->assertSame($commentBefore, $observation->cotRatings()->first()->comments);
    }

    public function test_unauthorized_supervisor_cannot_generate_the_document(): void
    {
        $otherSupervisor = User::factory()->create(['role' => 'supervisor']);
        $observation = $this->completedObservation();

        $this->actingAs($otherSupervisor)
            ->post(route('supervisor.observations.cot-document.generate', $observation))
            ->assertForbidden();

        $this->assertNull($observation->fresh()->cot_document_path);
    }

    public function test_generation_is_blocked_when_no_ratings_exist(): void
    {
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->completed()
            ->create([
                'school_year' => '2026-2027',
                'subject' => 'Mathematics',
                'grade_level' => 'Grade 7 - Diamond',
                'observation_date' => now()->parse('2026-09-01'),
                'quarter' => 2,
            ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.cot-document.generate', $observation))
            ->assertSessionHas('error');

        $this->assertNull($observation->fresh()->cot_document_path);
    }

    public function test_cannot_download_document_before_it_is_generated(): void
    {
        $observation = $this->completedObservation();

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.cot-document.download', $observation))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_can_download_generated_document(): void
    {
        $observation = $this->completedObservation();
        app(CotDocumentService::class)->generateDocument($observation);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.cot-document.download', $observation));

        $response->assertOk();
        $this->assertStringContainsString('COT_John-Doe_2026-2027_2026-09-01.docx', (string) $response->headers->get('content-disposition'));
    }

    public function test_can_download_document_as_pdf(): void
    {
        $observation = $this->completedObservation();

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.cot-document.pdf', $observation));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('COT_John-Doe_2026-2027_2026-09-01.pdf', (string) $response->headers->get('content-disposition'));
    }

    public function test_admin_can_download_an_already_generated_cot_document(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $observation = $this->completedObservation();
        app(CotDocumentService::class)->generateDocument($observation);

        $response = $this->actingAs($admin)
            ->get(route('admin.observations.cot-document', $observation));

        $response->assertOk();
        $this->assertStringContainsString('COT_John-Doe_2026-2027_2026-09-01.docx', (string) $response->headers->get('content-disposition'));
    }

    public function test_admin_download_generates_the_document_on_demand(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $observation = $this->completedObservation();

        $response = $this->actingAs($admin)
            ->get(route('admin.observations.cot-document', $observation));

        $response->assertOk();
        $this->assertStringContainsString('COT_John-Doe_2026-2027_2026-09-01.docx', (string) $response->headers->get('content-disposition'));
        $this->assertNotNull($observation->fresh()->cot_document_path);
    }

    public function test_admin_download_is_blocked_when_no_ratings_exist(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->completed()
            ->create([
                'school_year' => '2026-2027',
                'observation_date' => now()->parse('2026-09-01'),
            ]);

        $this->actingAs($admin)
            ->get(route('admin.observations.cot-document', $observation))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_non_admin_cannot_download_cot_document(): void
    {
        $observation = $this->completedObservation();

        $this->actingAs($this->supervisor)
            ->get(route('admin.observations.cot-document', $observation))
            ->assertForbidden();
    }

    public function test_admin_can_download_blank_cot_template_for_a_version(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $version = $this->templateVersion();

        $response = $this->actingAs($admin)
            ->get(route('admin.cot-indicators.template', $version));

        $response->assertOk();
        $this->assertSame(
            'attachment; filename=COT-Template_COT-Template-Test_SY2026-2027.docx',
            str_replace('"', '', (string) $response->headers->get('content-disposition'))
        );
        $this->assertSame('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $response->headers->get('content-type'));
    }

    public function test_template_docx_contains_the_version_indicators_grouped_by_domain(): void
    {
        $version = $this->templateVersion();
        $xml = $this->templateDocxXml($version);

        $this->assertStringContainsString('CLASSROOM OBSERVATION TOOL', $xml);
        $this->assertStringContainsString('Content Knowledge and Pedagogy', $xml);
        $this->assertStringContainsString('Learning Environment', $xml);
        $this->assertStringContainsString('1.1.2. ', $xml);
        $this->assertStringContainsString('Apply knowledge of content', $xml);
        $this->assertStringContainsString('2.1.1. ', $xml);
        $this->assertStringContainsString('Establish a safe and secure classroom', $xml);
        foreach (['6', '5', '4', '3', '2', 'NO', 'Comments'] as $header) {
            $this->assertStringContainsString($header, $xml);
        }
        $this->assertStringNotContainsString('X', $xml);
    }

    public function test_template_docx_xml_is_well_formed_and_escapes_ampersands(): void
    {
        $version = $this->templateVersion();
        $xml = $this->templateDocxXml($version);

        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($xml), 'word/document.xml must be well-formed XML');

        $this->assertStringContainsString('Grade &amp; Section:', $xml);
        $this->assertDoesNotMatchRegularExpression('/&(?!(amp;|lt;|gt;|quot;|apos;|#))/', $xml);
    }

    public function test_non_admin_cannot_download_cot_template(): void
    {
        $version = $this->templateVersion();

        $this->actingAs($this->supervisor)
            ->get(route('admin.cot-indicators.template', $version))
            ->assertForbidden();
    }

    private function templateVersion(): CotIndicatorVersion
    {
        $version = CotIndicatorVersion::factory()->published()->create([
            'label' => 'COT Template Test',
            'school_year' => '2026-2027',
        ]);

        CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.2',
            'description' => 'Apply knowledge of content',
            'domain' => 'Content Knowledge and Pedagogy',
            'sort_order' => 1,
        ]);

        CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '2.1.1',
            'description' => 'Establish a safe and secure classroom',
            'domain' => 'Learning Environment',
            'sort_order' => 2,
        ]);

        return $version;
    }

    private function templateDocxXml(CotIndicatorVersion $version): string
    {
        $phpWord = app(CotDocumentService::class)->buildTemplateDocx($version);
        $tmp = tempnam(sys_get_temp_dir(), 'cot_tpl_test_');
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

        $zip = new ZipArchive();
        $zip->open($tmp);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($tmp);

        return $xml;
    }

    private function completedObservation(): Observation
    {
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->completed()
            ->create([
                'school_year' => '2026-2027',
                'subject' => 'Mathematics',
                'grade_level' => 'Grade 7 - Diamond',
                'observation_date' => now()->parse('2026-09-01'),
                'quarter' => 2,
                'observation_number' => 3,
                'overall_score' => 4.50,
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
            ]);

        $r1 = CotRating::factory()->withRating(6)->create([
            'observation_id' => $observation->id,
            'indicator_code' => '1.1.1',
            'domain' => 'Content Knowledge and Pedagogy',
            'indicator' => 'Applies knowledge of content within and across curriculum areas',
            'comments' => 'Excellent delivery of the lesson.',
        ]);

        CotRating::factory()->withRating(3)->create([
            'observation_id' => $observation->id,
            'indicator_code' => '1.1.2',
            'domain' => 'Content Knowledge and Pedagogy',
            'indicator' => 'Uses strategies to develop critical and creative thinking',
            'comments' => null,
        ]);

        CotRating::factory()->notObserved()->create([
            'observation_id' => $observation->id,
            'indicator_code' => '1.1.3',
            'domain' => 'Content Knowledge and Pedagogy',
            'indicator' => 'Integrates ICT in the teaching-learning process',
            'comments' => null,
        ]);

        PostConference::create([
            'observation_id' => $observation->id,
            'conference_date' => now()->parse('2026-09-02'),
            'ai_comparison' => ['Plan vs actual aligned well.'],
            'feedback' => 'Great conference feedback.',
            'star_notes' => 'Good pacing and rapport.',
            'areas_for_improvement' => 'Higher-order questioning.',
            'prioritized_next_steps' => 'Apply differentiated instruction.',
            'supervisor_notes' => 'Continue building on strengths.',
        ]);

        AiFeedback::create([
            'observation_id' => $observation->id,
            'cot_rating_id' => $r1->id,
            'feedback_type' => 'post_observation',
            'analysis' => 'Strong use of scaffolding techniques.',
            'recommendations' => ['Add more collaborative tasks.'],
            'strengths' => ['Clear explanations.'],
            'areas_for_improvement' => ['Pace of questioning.'],
            'confidence_score' => 0.90,
            'model_version' => 'test-model',
            'generated_by' => 'ai',
            'status' => 'published',
        ]);

        return $observation->fresh();
    }

    private function docxXml(Observation $observation): string
    {
        app(CotDocumentService::class)->generateDocument($observation);

        $path = Storage::disk('public')->path($observation->cot_document_path);
        $tmp = tempnam(sys_get_temp_dir(), 'cot_test_');
        copy($path, $tmp);

        $zip = new ZipArchive();
        $zip->open($tmp);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($tmp);

        return $xml;
    }

    private function documentHtml(Observation $observation): string
    {
        return view('reports.cot-document', app(CotDocumentService::class)->viewData($observation))->render();
    }
}
