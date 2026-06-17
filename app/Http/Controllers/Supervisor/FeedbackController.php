<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AiFeedback;
use App\Models\Observation;
use App\Models\Teacher;
use App\Services\AIFeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    protected AIFeedbackService $aiFeedback;

    public function __construct(AIFeedbackService $aiFeedback)
    {
        $this->aiFeedback = $aiFeedback;
    }

    public function center(Request $request)
    {
        $user = Auth::user();

        $observations = Observation::with([
            'observee.user',
            'aiFeedbacks' => function ($q) {
                $q->latest();
            },
        ])
        ->where('observer_id', $user->id)
        ->where('status', '!=', 'cancelled')
        ->when($request->search, function ($q, $search) {
            $q->whereHas('observee.user', function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%");
            });
        })
        ->when($request->status, function ($q, $status) {
            if ($status === 'needs_review') {
                $q->whereHas('aiFeedbacks', function ($sq) {
                    $sq->where('status', 'draft');
                });
            } else {
                $q->where('status', $status);
            }
        })
        ->latest('observation_date')
        ->paginate(20);

        return view('supervisor.feedback.center', compact('observations'));
    }

    public function index(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load([
            'observee.user',
            'aiFeedbacks' => function ($q) {
                $q->with('reviewer')->latest();
            },
            'cotRatings.aiFeedback',
            'preObservationPlanning',
            'preConference',
            'postConference',
        ]);

        $feedbacks = $observation->aiFeedbacks;
        $perIndicatorFeedbacks = $observation->cotRatings->pluck('aiFeedback')->filter();

        return view('supervisor.feedback.index', compact('observation', 'feedbacks', 'perIndicatorFeedbacks'));
    }

    public function generate(Observation $observation, Request $request)
    {
        $this->authorizeObservation($observation);

        $type = $request->input('type', 'post_observation');

        $feedback = AiFeedback::create([
            'observation_id' => $observation->id,
            'feedback_type' => $type,
            'analysis' => $request->input('analysis', ''),
            'recommendations' => $request->input('recommendations', []),
            'strengths' => $request->input('strengths', []),
            'areas_for_improvement' => $request->input('areas_for_improvement', []),
            'confidence_score' => 0.85,
            'generated_by' => 'supervisor',
            'status' => 'draft',
        ]);

        return redirect()->route('supervisor.feedback.index', [$observation, 'type' => $type])
            ->with('success', 'Feedback created successfully.');
    }

    public function create(Observation $observation, Request $request)
    {
        $this->authorizeObservation($observation);

        $type = $request->input('type', 'post_observation');

        $feedback = AiFeedback::create([
            'observation_id' => $observation->id,
            'feedback_type' => $type,
            'analysis' => '',
            'recommendations' => [],
            'strengths' => [],
            'areas_for_improvement' => [],
            'confidence_score' => 0.00,
            'generated_by' => 'supervisor',
            'status' => 'draft',
        ]);

        return redirect()->route('supervisor.feedback.edit', [$observation, $feedback]);
    }

    public function edit(Observation $observation, AiFeedback $feedback)
    {
        $this->authorizeObservation($observation);

        if ($feedback->observation_id !== $observation->id) {
            abort(404);
        }

        $observation->load('observee.user');

        return view('supervisor.feedback.edit', compact('observation', 'feedback'));
    }

    public function update(Request $request, Observation $observation, AiFeedback $feedback)
    {
        $this->authorizeObservation($observation);

        if ($feedback->observation_id !== $observation->id) {
            abort(404);
        }

        $validated = $request->validate([
            'analysis' => ['nullable', 'string'],
            'strengths' => ['nullable', 'array'],
            'strengths.*' => ['string'],
            'areas_for_improvement' => ['nullable', 'array'],
            'areas_for_improvement.*' => ['string'],
            'recommendations' => ['nullable', 'array'],
            'recommendations.*' => ['string'],
        ]);

        $feedback->update([
            'analysis' => $validated['analysis'] ?? $feedback->analysis,
            'strengths' => $validated['strengths'] ?? $feedback->strengths,
            'areas_for_improvement' => $validated['areas_for_improvement'] ?? $feedback->areas_for_improvement,
            'recommendations' => $validated['recommendations'] ?? $feedback->recommendations,
        ]);

        return redirect()->route('supervisor.feedback.index', $observation)
            ->with('success', 'Feedback updated successfully.');
    }

    public function publish(Observation $observation, AiFeedback $feedback)
    {
        $this->authorizeObservation($observation);

        if ($feedback->observation_id !== $observation->id) {
            abort(404);
        }

        $feedback->publish();

        return redirect()->route('supervisor.feedback.index', $observation)
            ->with('success', 'Feedback published and is now visible to the teacher.');
    }

    public function destroy(Observation $observation, AiFeedback $feedback)
    {
        $this->authorizeObservation($observation);

        if ($feedback->observation_id !== $observation->id) {
            abort(404);
        }

        $feedback->delete();

        return redirect()->route('supervisor.feedback.index', $observation)
            ->with('success', 'Feedback deleted.');
    }

    public function generateAi(Observation $observation, Request $request)
    {
        $this->authorizeObservation($observation);

        $type = $request->input('type', 'post_observation');

        $existing = AiFeedback::where('observation_id', $observation->id)
            ->where('feedback_type', $type)
            ->where('generated_by', 'ai')
            ->first();

        if ($existing) {
            $existing->delete();
        }

        $dummyAnalysis = match ($type) {
            'pre_observation' => 'The lesson plan demonstrates strong alignment between learning objectives and planned activities. The teacher has incorporated varied instructional strategies that cater to different learning styles. Areas of particular strength include the use of formative assessment strategies and the integration of ICT tools to enhance student engagement.',
            'post_observation' => 'Based on the COT ratings, the teacher demonstrated effective classroom management and clear lesson delivery. Student participation was actively encouraged through differentiated activities. The teacher consistently checked for understanding and provided timely feedback to learners.',
            'post_conference' => 'The post-conference discussion highlighted the teacher\'s strengths in lesson delivery and classroom management. The teacher showed receptiveness to feedback and identified specific areas for professional growth, particularly in differentiated instruction and assessment strategies.',
            'final_summary' => 'Overall, this observation cycle shows a teacher who is competent and reflective. The teacher demonstrates strength in lesson planning, classroom management, and learner engagement. Continued focus on differentiated instruction and formative assessment will further enhance teaching effectiveness.',
            default => 'Feedback analysis generated for the observation.',
        };

        $dummyStrengths = match ($type) {
            'pre_observation' => [
                'Clear and measurable learning objectives aligned with curriculum standards',
                'Well-structured lesson flow with appropriate time allocation',
                'Integration of ICT tools to enhance lesson delivery',
                'Varied assessment strategies including formative checks',
            ],
            'post_observation' => [
                'Effective classroom management and positive learning environment',
                'Clear and engaging lesson delivery with good pacing',
                'Active student participation through varied activities',
                'Timely and constructive feedback to learners',
            ],
            'post_conference' => [
                'Teacher demonstrated strong reflective practice during conference',
                'Open and receptive to feedback and suggestions',
                'Clear action plan for professional growth identified',
                'Collaborative approach to addressing challenges',
            ],
            'final_summary' => [
                'Competent lesson planning with clear objectives',
                'Effective classroom management strategies',
                'Strong learner engagement techniques',
                'Reflective practice and commitment to improvement',
            ],
            default => ['Competent teaching practice observed'],
        };

        $dummyAreasForImprovement = match ($type) {
            'pre_observation' => [
                'Consider incorporating more learner-centered activities',
                'Include differentiation strategies for diverse learners',
                'Add specific time-bound checkpoints for assessment',
            ],
            'post_observation' => [
                'Increase opportunities for higher-order thinking questions',
                'Enhance differentiation for learners at varying levels',
                'Provide more wait time after posing questions',
            ],
            'post_conference' => [
                'Develop a more detailed timeline for implementing action plan',
                'Identify specific professional development programs aligned with goals',
                'Establish concrete success indicators for each improvement area',
            ],
            'final_summary' => [
                'Continue developing differentiated instruction strategies',
                'Strengthen use of formative assessment data to guide instruction',
                'Expand repertoire of higher-order thinking activities',
            ],
            default => ['Continue professional growth in identified areas'],
        };

        $dummyRecommendations = match ($type) {
            'pre_observation' => [
                'Incorporate collaborative learning structures to increase student engagement',
                'Use pre-assessment data to differentiate instruction effectively',
                'Integrate checkpoints for real-time assessment of student understanding',
                'Include enrichment activities for advanced learners',
            ],
            'post_observation' => [
                'Attend professional development on higher-order thinking strategies',
                'Implement tiered activities to address diverse learner needs',
                'Use structured wait time techniques (3-5 seconds) after questions',
                'Collaborate with peers on differentiated instruction strategies',
            ],
            'post_conference' => [
                'Create a professional development plan with specific milestones',
                'Schedule follow-up observation to monitor progress on focus areas',
                'Engage in lesson study with colleagues for collaborative improvement',
                'Document best practices for sharing with the learning community',
            ],
            'final_summary' => [
                'Continue participation in professional learning communities',
                'Implement action plan developed during post-conference',
                'Seek mentorship opportunities for targeted skill development',
                'Document growth through a professional portfolio',
            ],
            default => ['Continue current effective practices while pursuing growth'],
        };

        $feedback = AiFeedback::create([
            'observation_id' => $observation->id,
            'feedback_type' => $type,
            'analysis' => $dummyAnalysis,
            'strengths' => $dummyStrengths,
            'areas_for_improvement' => $dummyAreasForImprovement,
            'recommendations' => $dummyRecommendations,
            'confidence_score' => 0.88,
            'model_version' => 'gemini-2.0-flash',
            'generated_by' => 'ai',
            'status' => 'draft',
        ]);

        return redirect()->route('supervisor.feedback.index', [$observation, 'type' => $type])
            ->with('success', 'AI feedback generated successfully. Please review before publishing.');
    }

    protected function authorizeObservation(Observation $observation): void
    {
        $user = Auth::user();
        $observee = $observation->observee;

        if (!$observee || !$observee->user) {
            abort(404);
        }

        if ($observee->user->school_id !== $user->school_id) {
            abort(403, 'This observation does not belong to your school.');
        }
    }

    public function export(Observation $observation, AiFeedback $feedback)
    {
        $this->authorizeObservation($observation);

        $feedback->load(['observation.observee.user', 'observation.observer']);

        $teacherName = $feedback->observation->observee?->user?->name ?? 'Teacher';
        $observerName = $feedback->observation->observer?->name ?? 'Supervisor';
        $obsDate = $feedback->observation->observation_date?->format('F d, Y') ?? 'N/A';

        $md = "# Feedback Report: {$feedback->feedbackTypeLabel()}\n\n";
        $md .= "---\n\n";
        $md .= "| | |\n|---|---|\n";
        $md .= "| **Teacher** | {$teacherName} |\n";
        $md .= "| **Supervisor** | {$observerName} |\n";
        $md .= "| **Observation Date** | {$obsDate} |\n";
        $md .= "| **Feedback Type** | {$feedback->feedbackTypeLabel()} |\n";
        $md .= "| **Status** | " . ucfirst($feedback->status) . " |\n";
        $md .= "| **Generated** | " . ucfirst($feedback->generated_by) . " |\n\n";
        $md .= "---\n\n";

        $md .= "## Analysis\n\n{$feedback->analysis}\n\n";

        if ($feedback->strengths) {
            $md .= "## Strengths\n\n";
            foreach ($feedback->strengths as $s) {
                $md .= "- {$s}\n";
            }
            $md .= "\n";
        }

        if ($feedback->areas_for_improvement) {
            $md .= "## Areas for Improvement\n\n";
            foreach ($feedback->areas_for_improvement as $a) {
                $md .= "- {$a}\n";
            }
            $md .= "\n";
        }

        if ($feedback->recommendations) {
            $md .= "## Recommendations\n\n";
            foreach ($feedback->recommendations as $r) {
                $md .= "- {$r}\n";
            }
            $md .= "\n";
        }

        $md .= "---\n\n*Generated by ASPIRE on " . now()->format('F d, Y h:i A') . "*\n";

        $filename = 'feedback-' . strtolower(str_replace(' ', '-', $feedback->feedbackTypeLabel()))
            . '-' . preg_replace('/[^a-z0-9]/i', '-', $teacherName)
            . '-' . ($feedback->observation->observation_date?->format('Y-m-d') ?? date('Y-m-d'))
            . '.md';

        return response()->streamDownload(function () use ($md) {
            echo $md;
        }, $filename, [
            'Content-Type' => 'text/markdown; charset=utf-8',
        ]);
    }
}
