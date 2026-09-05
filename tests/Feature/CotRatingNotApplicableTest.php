<?php

namespace Tests\Feature;

use App\Jobs\GeneratePostObservationFeedback;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CotRatingNotApplicableTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $supervisor;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();

        $this->supervisor = User::factory()->create([
            'role' => 'supervisor',
            'school_id' => $this->school->id,
        ]);
        $this->completeProfile($this->supervisor);

        $this->teacher = Teacher::factory()->forSchool($this->school->id)->create([
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'school_id' => $this->school->id,
            ])->id,
        ]);
    }

    private function completeProfile(User $user): void
    {
        $user->profile()->create([
            'mobile_number' => '09171234567',
        ]);

        if ($user->role === 'supervisor') {
            $user->supervisorProfile()->create([
                'division_district_assigned' => 'Division of Test District',
                'area_of_specialization' => 'Mathematics',
                'supervisory_level' => 'division',
            ]);
        }
    }

    private function observation(): Observation
    {
        return Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create(['stage' => 'observation']);
    }

    private function submitRatings(Observation $observation): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->supervisor)->post(
            route('supervisor.observations.storeObservationData', $observation),
            [
                'ratings' => [
                    [
                        'indicator_code' => 'COT-1-1',
                        'domain' => 'Content Knowledge and Pedagogy',
                        'indicator' => 'Applied knowledge of content within and across curriculum teaching areas.',
                        'rating' => 5,
                        'has_rating' => '1',
                        'not_observed' => '0',
                        'not_applicable' => '0',
                        'comments' => 'Strong lesson delivery.',
                    ],
                    [
                        'indicator_code' => 'COT-2-1',
                        'domain' => 'Learning Environment',
                        'indicator' => 'Managed classroom structure to engage learners.',
                        'rating' => null,
                        'has_rating' => '',
                        'not_observed' => '0',
                        'not_applicable' => '1',
                        'comments' => null,
                    ],
                ],
            ]
        );
    }

    public function test_not_applicable_rating_is_saved_with_null_rating_and_excluded_from_overall_score(): void
    {
        $observation = $this->observation();

        $response = $this->submitRatings($observation);

        $response->assertRedirect(route('supervisor.observations.postConference', $observation));
        $response->assertSessionHas('success');

        $rated = CotRating::where('observation_id', $observation->id)->where('indicator_code', 'COT-1-1')->first();
        $this->assertSame(5, $rated->numericRating());
        $this->assertFalse($rated->isNotApplicable());

        $na = CotRating::where('observation_id', $observation->id)->where('indicator_code', 'COT-2-1')->first();
        $this->assertNull($na->rating);
        $this->assertTrue($na->isNotApplicable());
        $this->assertFalse($na->isNotObserved());
        $this->assertSame(0, $na->numericRating());

        $observation->refresh();
        $this->assertSame('5.00', $observation->overall_score);
        $this->assertSame('cot_completed', $observation->status);
        $this->assertSame('post_conference', $observation->stage);
    }

    public function test_ai_feedback_is_not_dispatched_for_not_applicable_rows(): void
    {
        Queue::fake();

        $observation = $this->observation();

        $this->submitRatings($observation);

        Queue::assertPushed(GeneratePostObservationFeedback::class, 1);
    }

    public function test_observation_with_all_not_applicable_rows_has_no_overall_score(): void
    {
        $observation = $this->observation();

        $this->actingAs($this->supervisor)->post(
            route('supervisor.observations.storeObservationData', $observation),
            [
                'ratings' => [
                    [
                        'indicator_code' => 'COT-1-1',
                        'domain' => 'Content Knowledge and Pedagogy',
                        'indicator' => 'Applied knowledge of content within and across curriculum teaching areas.',
                        'rating' => null,
                        'has_rating' => '',
                        'not_observed' => '0',
                        'not_applicable' => '1',
                        'comments' => null,
                    ],
                ],
            ]
        )->assertRedirect(route('supervisor.observations.postConference', $observation));

        $observation->refresh();
        $this->assertNull($observation->overall_score);
    }
}