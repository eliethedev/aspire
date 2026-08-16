<?php

namespace Tests\Feature;

use App\Models\CotIndicatorVersion;
use App\Models\FormTemplate;
use App\Models\Observation;
use App\Models\PostConference;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ObservationSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private const SY = '2026-2027';

    private User $supervisor;

    private Teacher $teacher;

    private User $teacherUser;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
            'career_stage' => 'teacher_i_iii',
        ]);
    }

    private function publishedVersion(array $overrides = []): CotIndicatorVersion
    {
        return CotIndicatorVersion::factory()->published()->create(array_merge([
            'school_year' => self::SY,
        ], $overrides));
    }

    private function template(string $observationType = 'teacher_observation'): FormTemplate
    {
        return FormTemplate::create([
            'name' => 'COT RPMS Template',
            'description' => 'Test template',
            'school_year' => self::SY,
            'observation_type' => $observationType,
            'is_active' => true,
            'version' => 1,
        ]);
    }

    private function schedulePayload(array $overrides = []): array
    {
        return array_merge([
            'observation_type' => 'teacher_observation',
            'observee_id' => $this->teacher->id,
            'observation_date' => now()->addDay()->format('Y-m-d'),
            'schedule_type' => 'scheduled',
            'school_year' => self::SY,
            'subject' => 'Mathematics',
            'grade_level' => '7',
            'observation_mode' => 'in_person',
        ], $overrides);
    }

    public function test_supervisor_can_schedule_an_observation_with_template_time_location_and_conference(): void
    {
        $version = $this->publishedVersion();
        $template = $this->template('teacher_observation');

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.store'), $this->schedulePayload([
                'form_template_id' => $template->id,
                'start_time' => '08:00',
                'end_time' => '08:45',
                'location' => 'Room 101',
                'schedule_conference' => true,
                'conference_date' => now()->addDay()->format('Y-m-d'),
                'conference_start_time' => '09:00',
                'conference_end_time' => '09:30',
                'conference_location' => 'Conference Room',
                'conference_mode' => 'in_person',
            ]))
            ->assertRedirect();

        $observation = Observation::where('observee_id', $this->teacher->id)->first();

        $this->assertNotNull($observation);
        $this->assertSame('08:00', $observation->start_time);
        $this->assertSame('08:45', $observation->end_time);
        $this->assertSame('Room 101', $observation->location);
        $this->assertSame($template->id, $observation->form_template_id);
        $this->assertSame($version->id, $observation->cot_indicator_version_id);
        $this->assertSame('scheduled', $observation->status);
        $this->assertSame('pre_observation_planning', $observation->stage);

        $conference = $observation->postConference;
        $this->assertNotNull($conference);
        $this->assertSame('09:00', $conference->start_time);
        $this->assertSame('09:30', $conference->end_time);
        $this->assertSame('Conference Room', $conference->location);
        $this->assertSame('in_person', $conference->mode);

        $this->assertDatabaseHas('post_conferences', [
            'observation_id' => $observation->id,
            'conference_date' => now()->addDay()->startOfDay()->format('Y-m-d H:i:s'),
            'location' => 'Conference Room',
            'mode' => 'in_person',
        ]);
    }

    public function test_supervisor_can_schedule_a_school_head_observation(): void
    {
        $version = $this->publishedVersion([
            'ratee_role' => 'school_head',
            'label' => 'School Head Instrument',
        ]);

        $school = School::factory()->create();
        $schoolHeadUser = User::factory()->create(['role' => 'school_head']);
        $schoolHead = SchoolHeadProfile::create([
            'user_id' => $schoolHeadUser->id,
            'school_id' => $school->id,
            'position_level' => 'principal_i',
            'current_designation' => 'principal',
            'position' => 'Principal I',
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.store'), $this->schedulePayload([
                'observation_type' => 'school_head_observation',
                'observee_id' => $schoolHead->id,
                'start_time' => '10:00',
                'location' => 'Principal Office',
            ]))
            ->assertRedirect();

        $observation = Observation::where('observee_id', $schoolHead->id)
            ->where('observee_type', SchoolHeadProfile::class)
            ->first();

        $this->assertNotNull($observation);
        $this->assertSame('10:00', $observation->start_time);
        $this->assertSame('Principal Office', $observation->location);
        $this->assertSame($version->id, $observation->cot_indicator_version_id);
    }

    public function test_template_for_another_observation_type_is_rejected(): void
    {
        $this->publishedVersion();
        $this->template('teacher_observation');

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.store'), $this->schedulePayload([
                'observation_type' => 'school_head_observation',
                'observee_id' => $this->teacher->id,
                'form_template_id' => FormTemplate::first()->id,
            ]))
            ->assertSessionHasErrors('form_template_id');

        $this->assertDatabaseMissing('observations', [
            'observee_id' => $this->teacher->id,
        ]);
    }

    public function test_end_time_before_start_time_is_rejected(): void
    {
        $this->publishedVersion();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.store'), $this->schedulePayload([
                'start_time' => '10:00',
                'end_time' => '09:00',
            ]))
            ->assertSessionHasErrors('end_time');

        $this->assertDatabaseMissing('observations', [
            'observee_id' => $this->teacher->id,
        ]);
    }

    public function test_duplicate_observation_at_same_time_for_same_ratee_is_rejected(): void
    {
        $this->publishedVersion();
        $this->template('teacher_observation');

        Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create([
                'school_year' => self::SY,
                'observation_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '08:00',
                'status' => 'scheduled',
            ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.store'), $this->schedulePayload([
                'form_template_id' => FormTemplate::first()->id,
                'start_time' => '08:00',
                'end_time' => '08:45',
            ]))
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, Observation::count());
    }

    public function test_conflicting_observation_for_another_ratee_is_allowed(): void
    {
        $this->publishedVersion();
        $otherTeacher = Teacher::factory()->create(['career_stage' => 'teacher_i_iii']);

        Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($otherTeacher)
            ->create([
                'school_year' => self::SY,
                'observation_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '08:00',
                'status' => 'scheduled',
            ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.store'), $this->schedulePayload([
                'start_time' => '08:00',
                'end_time' => '08:45',
            ]))
            ->assertRedirect();

        $this->assertSame(2, Observation::count());
    }

    public function test_cancelled_observation_does_not_block_a_new_schedule(): void
    {
        $this->publishedVersion();

        Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->cancelled()
            ->create([
                'school_year' => self::SY,
                'observation_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '08:00',
            ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.store'), $this->schedulePayload([
                'start_time' => '08:00',
                'end_time' => '08:45',
            ]))
            ->assertRedirect();

        $this->assertSame(2, Observation::count());
    }

    public function test_create_page_renders_the_scheduling_wizard(): void
    {
        $this->publishedVersion();
        $this->template('teacher_observation');

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.create'))
            ->assertOk()
            ->assertSee('Schedule Observation')
            ->assertSee('name="schedule_type" value="scheduled"', false);
    }

    public function test_teacher_cannot_schedule_an_observation(): void
    {
        $this->actingAs($this->teacherUser)
            ->post(route('supervisor.observations.store'), $this->schedulePayload())
            ->assertForbidden();

        $this->assertDatabaseMissing('observations', [
            'observee_id' => $this->teacher->id,
        ]);
    }

    public function test_ratee_can_view_their_scheduled_observation(): void
    {
        $this->publishedVersion();

        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create([
                'school_year' => self::SY,
                'start_time' => '08:00',
                'end_time' => '08:45:00',
                'location' => 'Room 101',
                'status' => 'scheduled',
                'stage' => 'pre_observation_planning',
            ]);

        $this->actingAs($this->teacherUser)
            ->get(route('teacher.observations.show', $observation->id))
            ->assertOk()
            ->assertSee('08:00 AM')
            ->assertSee('Room 101');
    }
}
