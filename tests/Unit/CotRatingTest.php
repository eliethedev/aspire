<?php

namespace Tests\Unit;

use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CotRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_numeric_rating_returns_rating_value(): void
    {
        $rating = CotRating::factory()->withRating(6)->create();

        $this->assertSame(6, $rating->numericRating());
    }

    public function test_numeric_rating_returns_zero_when_not_observed(): void
    {
        $rating = CotRating::factory()->notObserved()->create();

        $this->assertSame(0, $rating->numericRating());
        $this->assertTrue($rating->isNotObserved());
    }

    public function test_numeric_rating_returns_zero_when_rating_is_null(): void
    {
        $rating = CotRating::factory()->create(['rating' => null]);

        $this->assertSame(0, $rating->numericRating());
    }

    public function test_percentage_uses_six_point_scale(): void
    {
        $this->assertSame(100.0, CotRating::factory()->withRating(6)->create()->percentage());
        $this->assertSame(50.0, CotRating::factory()->withRating(3)->create()->percentage());
        $this->assertSame(33.3, CotRating::factory()->withRating(2)->create()->percentage());
    }

    public function test_percentage_is_zero_when_not_observed(): void
    {
        $rating = CotRating::factory()->notObserved()->create();

        $this->assertSame(0.0, $rating->percentage());
    }

    public function test_descriptive_label_maps_ratings(): void
    {
        $this->assertSame('Outstanding', CotRating::factory()->withRating(6)->create()->descriptiveLabel());
        $this->assertSame('Very Satisfactory', CotRating::factory()->withRating(5)->create()->descriptiveLabel());
        $this->assertSame('Satisfactory', CotRating::factory()->withRating(4)->create()->descriptiveLabel());
        $this->assertSame('Unsatisfactory', CotRating::factory()->withRating(3)->create()->descriptiveLabel());
        $this->assertSame('Poor', CotRating::factory()->withRating(2)->create()->descriptiveLabel());
    }

    public function test_descriptive_label_is_not_observed_when_unrated(): void
    {
        $rating = CotRating::factory()->notObserved()->create();

        $this->assertSame('Not Observed', $rating->descriptiveLabel());
    }

    public function test_high_ratings_scope_excludes_low_and_not_observed(): void
    {
        $observation = Observation::factory()->create();

        CotRating::factory()->withRating(6)->create(['observation_id' => $observation->id]);
        CotRating::factory()->withRating(4)->create(['observation_id' => $observation->id]);
        CotRating::factory()->notObserved()->create(['observation_id' => $observation->id]);

        $high = CotRating::where('observation_id', $observation->id)->highRatings()->get();

        $this->assertCount(1, $high);
    }

    public function test_by_domain_scope_filters_ratings(): void
    {
        $observation = Observation::factory()->create();

        CotRating::factory()->create([
            'observation_id' => $observation->id,
            'domain' => 'Content Knowledge and Pedagogy',
        ]);
        CotRating::factory()->create([
            'observation_id' => $observation->id,
            'domain' => 'Learning Environment',
        ]);

        $content = CotRating::where('observation_id', $observation->id)->byDomain('Content Knowledge and Pedagogy')->get();

        $this->assertCount(1, $content);
    }

    public function test_belongs_to_observation(): void
    {
        $observation = Observation::factory()->create();
        $rating = CotRating::factory()->create(['observation_id' => $observation->id]);

        $this->assertInstanceOf(Observation::class, $rating->observation);
        $this->assertSame($observation->id, $rating->observation->id);
    }
}
