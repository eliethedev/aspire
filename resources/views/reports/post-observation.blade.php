<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post-Observation Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }
        .page { padding: 30px 40px; }
        h1 { font-size: 22px; color: #1e3a5f; margin-bottom: 5px; }
        h2 { font-size: 15px; color: #1e3a5f; border-bottom: 2px solid #1e3a5f; padding-bottom: 4px; margin: 20px 0 10px; }
        h3 { font-size: 13px; color: #2c5282; margin: 12px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10px; }
        th { background: #1e3a5f; color: #fff; padding: 6px 8px; text-align: left; font-weight: 600; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f7fafc; }
        .cover { text-align: center; padding: 80px 40px 40px; }
        .cover h1 { font-size: 28px; margin-bottom: 10px; }
        .cover .school-name { font-size: 18px; color: #4a5568; margin-bottom: 30px; }
        .cover .info-table { width: 70%; margin: 0 auto; }
        .cover .info-table td { padding: 8px 12px; font-size: 12px; border: 1px solid #e2e8f0; }
        .cover .info-table td:first-child { font-weight: 700; background: #edf2f7; width: 40%; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .badge-green { background: #c6f6d5; color: #22543d; }
        .badge-blue { background: #bee3f8; color: #2a4365; }
        .badge-yellow { background: #fefcbf; color: #744210; }
        .badge-orange { background: #feebc8; color: #7b341e; }
        .badge-red { background: #fed7d7; color: #742a2a; }
        .comparison-table td { text-align: center; }
        .improved { color: #22543d; font-weight: 700; }
        .declined { color: #742a2a; font-weight: 700; }
        .same { color: #718096; }
        .section { margin-bottom: 15px; }
        .signature-block { margin-top: 40px; }
        .signature-line { display: inline-block; width: 200px; border-bottom: 1px solid #1a1a1a; margin: 0 20px; }
        .pd-activity { background: #ebf8ff; border-left: 3px solid #3182ce; padding: 6px 10px; margin: 4px 0; }
        .severity-critical { border-left-color: #e53e3e; background: #fff5f5; }
        .severity-high { border-left-color: #dd6b20; background: #fffaf0; }
        .severity-medium { border-left-color: #d69e2e; background: #fffff0; }
        ul { padding-left: 20px; margin: 4px 0; }
        li { margin: 2px 0; }
        .page-break { page-break-before: always; }
        .footer { text-align: center; font-size: 9px; color: #a0aec0; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>

<!-- COVER PAGE -->
<div class="page cover">
    <h1>Post-Observation Report</h1>
    <p class="school-name">{{ $school_name }}</p>

    <table class="info-table">
        <tr><td>Teacher Observed</td><td>{{ $teacher_name }}</td></tr>
        <tr><td>Observer</td><td>{{ $observer_name }}</td></tr>
        <tr><td>Date of Observation</td><td>{{ $observation_date }}</td></tr>
        <tr><td>Term</td><td>{{ $quarter }}</td></tr>
        <tr><td>Observation Type</td><td>{{ $observation_type }}</td></tr>
        <tr><td>Subject</td><td>{{ $subject }}</td></tr>
        <tr><td>Grade Level</td><td>{{ $grade_level }}</td></tr>
        <tr><td>School Year</td><td>{{ $school_year }}</td></tr>
        @if($overall_score)
        <tr><td>Overall Score</td><td><strong>{{ number_format($overall_score, 2) }}%</strong></td></tr>
        @endif
    </table>
</div>

<!-- COT RATING SUMMARY -->
<div class="page page-break">
    <h2>COT Rating Summary</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Domain</th>
                <th>Indicator</th>
                <th>Rating</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cot_ratings as $i => $rating)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $rating->domain }}</td>
                <td>{{ $rating->indicator }}</td>
                <td>
                    @if($rating->not_observed)
                        <span class="badge badge-orange">NO</span>
                    @else
                        <span class="badge
                            @if($rating->rating >= 5) badge-green
                            @elseif($rating->rating >= 4) badge-blue
                            @elseif($rating->rating >= 3) badge-yellow
                            @else badge-red
                            @endif">{{ $rating->rating }}/6</span>
                    @endif
                </td>
                <td>{{ $rating->comments ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($overall_score)
    <p style="margin-top: 8px;"><strong>Overall Average:</strong> {{ number_format($overall_score, 2) }}%</p>
    @endif
</div>

<!-- PRE-OBSERVATION PLANNING -->
<div class="page page-break">
    <h2>Pre-Observation Summary</h2>

    @if($planning)
    <h3>Planning Notes</h3>
    @if($planning->suggested_focus)
    <p><strong>Suggested Focus:</strong> {{ is_array($planning->suggested_focus) ? implode(', ', $planning->suggested_focus) : $planning->suggested_focus }}</p>
    @endif
    @if($planning->ai_insights)
    <p><strong>AI Pre-Observation Insights:</strong> {{ is_array($planning->ai_insights) ? json_encode($planning->ai_insights) : $planning->ai_insights }}</p>
    @endif
    @if($planning->supervisor_notes)
    <p><strong>Supervisor Notes:</strong> {{ $planning->supervisor_notes }}</p>
    @endif
    @endif

    @if($pre_conference)
    <h3>Pre-Conference</h3>
    @if($pre_conference->finalized_focus)
    <p><strong>Finalized Focus:</strong> {{ $pre_conference->finalized_focus }}</p>
    @endif
    @if($pre_conference->discussion_notes)
    <p><strong>Discussion Notes:</strong> {{ $pre_conference->discussion_notes }}</p>
    @endif
    @endif
</div>

<!-- OBSERVATION HIGHLIGHTS & POST-CONFERENCE -->
<div class="page page-break">
    <h2>Observation Highlights (STAR Notes)</h2>
    @if($post_conference && $post_conference->star_notes)
    <p>{{ $post_conference->star_notes }}</p>
    @else
    <p><em>No STAR notes recorded.</em></p>
    @endif

    <h2>Post-Observation Conference Summary</h2>

    <h3>Focus on What's Going Well</h3>
    @if($post_conference && $post_conference->star_notes)
    <p>{{ $post_conference->star_notes }}</p>
    @else
    <p><em>No specific strengths recorded.</em></p>
    @endif

    <h3>Identify Challenges Facing the Teacher</h3>
    @if($post_conference && $post_conference->challenges_facing_teacher)
    <p>{{ $post_conference->challenges_facing_teacher }}</p>
    @else
    <p><em>No specific challenges identified.</em></p>
    @endif

    <h3>Generating Ideas for Addressing Challenges</h3>
    @if($post_conference && $post_conference->ideas_for_addressing_challenges)
    <p>{{ $post_conference->ideas_for_addressing_challenges }}</p>
    @else
    <p><em>No specific ideas generated.</em></p>
    @endif

    <h3>Prioritizing Next Steps</h3>
    @if($post_conference && $post_conference->prioritized_next_steps)
    <p>{{ $post_conference->prioritized_next_steps }}</p>
    @else
    <p><em>No next steps recorded.</em></p>
    @endif

    @if($post_conference && $post_conference->supervisor_notes)
    <h3>Supervisor's Overall Comments</h3>
    <p>{{ $post_conference->supervisor_notes }}</p>
    @endif
</div>

<!-- AI INSIGHTS -->
<div class="page page-break">
    <h2>AI-Generated Insights &amp; Recommendations</h2>

    @if(!empty($ai_summary['strengths']))
    <h3>Personalized Strengths</h3>
    <ul>
        @foreach($ai_summary['strengths'] as $s)
        <li>{{ $s }}</li>
        @endforeach
    </ul>
    @endif

    @if(!empty($ai_summary['areas_for_improvement']))
    <h3>Areas for Improvement</h3>
    <ul>
        @foreach($ai_summary['areas_for_improvement'] as $a)
        <li>{{ $a }}</li>
        @endforeach
    </ul>
    @endif

    @if(!empty($ai_summary['recommendations']))
    <h3>Actionable Coaching Recommendations</h3>
    <ul>
        @foreach($ai_summary['recommendations'] as $r)
        <li>{{ $r }}</li>
        @endforeach
    </ul>
    @endif

    @if(empty($ai_summary['strengths']) && empty($ai_summary['areas_for_improvement']) && empty($ai_summary['recommendations']))
    <p><em>AI-generated feedback was not available for this observation.</em></p>
    @endif
</div>

<!-- OBSERVATION COMPARISON -->
@if($comparison)
<div class="page page-break">
    <h2>Progress Comparison with Previous Observation</h2>
    <p><strong>Previous:</strong> {{ $comparison['previous_date'] }} | <strong>Current:</strong> {{ $comparison['current_date'] }}</p>

    <table class="comparison-table">
        <thead>
            <tr>
                <th>Indicator</th>
                <th>Previous Rating</th>
                <th>Current Rating</th>
                <th>Change</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comparison['comparisons'] as $c)
            <tr>
                <td><strong>{{ $c['code'] }}</strong>: {{ $c['indicator'] }}</td>
                <td>{{ $c['previous_rating'] }}/6</td>
                <td>{{ $c['current_rating'] }}/6</td>
                <td class="{{ $c['direction'] === 'improved' ? 'improved' : ($c['direction'] === 'declined' ? 'declined' : 'same') }}">
                    @if($c['delta'] !== null)
                        {{ $c['delta'] > 0 ? '+' : '' }}{{ $c['delta'] }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 8px;"><strong>Overall Change:</strong>
        @if($comparison['overall_delta'] !== null)
            <span class="{{ $comparison['overall_direction'] === 'improved' ? 'improved' : ($comparison['overall_direction'] === 'declined' ? 'declined' : 'same') }}">
                {{ $comparison['overall_delta'] > 0 ? '+' : '' }}{{ $comparison['overall_delta'] }}%
            </span>
        @else
            N/A
        @endif
    </p>
</div>
@endif

<!-- PD PLAN -->
@if($pd_plan && !empty($pd_plan['short_term_goals']) || !empty($pd_plan['long_term_goals']))
<div class="page page-break">
    <h2>Professional Development Plan</h2>
    <p><strong>Teacher:</strong> {{ $pd_plan['teacher'] }} | <strong>Generated:</strong> {{ $pd_plan['generated_at'] }}</p>

    @if(!empty($pd_plan['short_term_goals']))
    <h3>Short-Term Goals (1-2 Months)</h3>
    @foreach($pd_plan['short_term_goals'] as $goal)
    <div class="pd-activity severity-critical">
        <p><strong>{{ $goal['indicator'] }}</strong></p>
        <p><em>Target:</em> {{ $goal['target'] }}</p>
        <p><em>Activities:</em></p>
        <ul>
            @foreach($goal['activities'] as $a)
            <li>{{ $a }}</li>
            @endforeach
        </ul>
        <p><em>Support:</em> {{ $goal['support_needed'] }}</p>
    </div>
    @endforeach
    @endif

    @if(!empty($pd_plan['long_term_goals']))
    <h3>Long-Term Goals (3-6 Months)</h3>
    @foreach($pd_plan['long_term_goals'] as $goal)
    <div class="pd-activity">
        <p><strong>{{ $goal['indicator'] }}</strong></p>
        <p><em>Target:</em> {{ $goal['target'] }}</p>
        <p><em>Activities:</em></p>
        <ul>
            @foreach($goal['activities'] as $a)
            <li>{{ $a }}</li>
            @endforeach
        </ul>
        <p><em>Support:</em> {{ $goal['support_needed'] }}</p>
    </div>
    @endforeach
    @endif
</div>
@endif

<!-- SIGNATURES -->
<div class="page page-break">
    <h2>Teacher Reflection &amp; Agreement</h2>

    @if($post_conference && $post_conference->teacher_reflection)
    <h3>Teacher's Self-Reflection</h3>
    <p>{{ $post_conference->teacher_reflection }}</p>
    @endif

    @if($post_conference && $post_conference->prioritized_next_steps)
    <h3>Agreed Next Steps and Support Needed</h3>
    <p>{{ $post_conference->prioritized_next_steps }}</p>
    @endif

    <div class="signature-block">
        <h3>Signatures</h3>
        <table style="margin-top: 15px;">
            <tr>
                <td style="width: 40%; border: none; padding: 20px 0;">
                    <div style="border-bottom: 1px solid #1a1a1a; width: 200px; margin-bottom: 5px;">&nbsp;</div>
                    <strong>{{ $observer_name }}</strong><br>
                    <small>Supervisor / Observer</small><br>
                    <small>Date: _______________</small>
                </td>
                <td style="width: 40%; border: none; padding: 20px 0;">
                    <div style="border-bottom: 1px solid #1a1a1a; width: 200px; margin-bottom: 5px;">&nbsp;</div>
                    <strong>{{ $teacher_name }}</strong><br>
                    <small>Teacher Observed</small><br>
                    <small>Date: _______________</small>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Post-Observation Report | {{ $school_name }} | {{ $school_year }}</p>
        <p>Generated on {{ now()->format('F d, Y') }} | ASPIRE - Classroom Observation Management System</p>
    </div>
</div>

</body>
</html>
