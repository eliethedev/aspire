<?php

namespace Tests\Unit\AI;

use App\AI\RAG\CotIndicatorRepository;
use Tests\TestCase;

class StubIndicatorRepository extends CotIndicatorRepository
{
    public function __construct(protected array $domains)
    {
        parent::__construct(app(\App\Services\CotIndicatorService::class));
    }

    public function getAllDomains(): array
    {
        return $this->domains;
    }
}

class RagRetrievalTest extends TestCase
{
    public function test_find_relevant_indicators_ranks_keyword_overlap(): void
    {
        $repo = new StubIndicatorRepository([
            'Content Knowledge and Pedagogy' => [
                ['code' => '1.1.2', 'description' => 'Use effective verbal and non-verbal communication strategies in teaching activities'],
            ],
            'Learning Environment' => [
                ['code' => '2.1.1', 'description' => 'Maintain learning environments that promote fairness, respect and safety'],
            ],
            'Assessment' => [
                ['code' => '5.1.2', 'description' => 'Use formative assessment strategies to monitor learner comprehension'],
            ],
        ]);

        $matches = $repo->findRelevantIndicators(
            'The teacher used group activities and formative assessment to check learner comprehension.',
            limit: 3
        );

        $this->assertNotEmpty($matches);
        $this->assertSame('5.1.2', $matches[0]['code']); // strongest overlap: formative/assessment/comprehension
    }

    public function test_exact_indicator_code_mention_scores_highest(): void
    {
        $repo = new StubIndicatorRepository([
            'Domain A' => [
                ['code' => '3.4.1', 'description' => 'Something unrelated entirely to the text below'],
            ],
            'Domain B' => [
                ['code' => '9.9.9', 'description' => 'Another unrelated description here'],
            ],
        ]);

        $matches = $repo->findRelevantIndicators('Plan references indicator 3.4.1 for the lesson.', 2);

        $this->assertSame('3.4.1', $matches[0]['code']);
    }

    public function test_returns_empty_for_blank_text_or_no_indicators(): void
    {
        $repo = new StubIndicatorRepository([]);

        $this->assertSame([], $repo->findRelevantIndicators('some lesson plan text'));

        $withData = new StubIndicatorRepository([
            'D' => [['code' => '1.2.3', 'description' => 'Description words']],
        ]);

        $this->assertSame([], $withData->findRelevantIndicators('   '));
    }

    public function test_get_indicators_context_renders_only_requested_codes(): void
    {
        $indicators = new class(app(\App\Services\CotIndicatorService::class)) extends CotIndicatorRepository
        {
            public function __construct($service)
            {
                parent::__construct($service);
            }

            public function getIndicatorsByCodes(array $codes): array
            {
                return [
                    ['code' => '1.1.2', 'description' => 'Apply knowledge of content', 'domain' => 'Content Knowledge'],
                ];
            }
        };

        $rubrics = new \App\AI\RAG\PPSTRubricRepository($indicators);

        $context = $rubrics->getIndicatorsContext(['1.1.2']);

        $this->assertStringContainsString('1.1.2', $context);
        $this->assertStringContainsString('Content Knowledge', $context);
        $this->assertStringNotContainsString('2.1.1', $context); // other indicators excluded
    }
}
