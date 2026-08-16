<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Observation Tool — {{ $teacher_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 11px; color: #1a1a1a; line-height: 1.4; }
        .doc { padding: 24px 30px; }
        .center { text-align: center; }
        .header-line { font-weight: 700; }
        .school-name { font-weight: 700; font-size: 12px; margin-bottom: 6px; }
        .title { font-size: 16px; font-weight: 700; margin-top: 2px; }
        .subtitle { font-size: 10px; font-style: italic; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; margin: 6px 0; }
        table.bordered { border: 1px solid #000; }
        table.bordered th, table.bordered td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
        .info-table td { font-size: 10px; }
        .info-table td.label { background: #f2f2f2; font-weight: 700; width: 16%; }

        .rating-table th { background: #d9e2f3; font-weight: 700; font-size: 9px; text-align: center; }
        .rating-table td { font-size: 9px; }
        .rating-table .col-num { width: 3%; text-align: center; font-weight: 700; }
        .rating-table .col-indicator { width: 32%; }
        .rating-table .col-rating { width: 4.5%; text-align: center; }
        .rating-table .col-no { width: 4%; text-align: center; }
        .rating-table .col-comments { width: 37%; }
        .rating-table .domain-row td { background: #b4c7e7; font-weight: 700; }
        .rating-table td.marked { background: #d9e2f3; font-weight: 700; }

        .summary { margin: 8px 0; font-size: 11px; }
        .summary strong { font-weight: 700; }
        .section-title { font-size: 11px; font-weight: 700; text-align: center; margin: 12px 0 6px; }
        .ai-section { background: #fce5cd; }
        .signatures { width: 100%; margin-top: 26px; }
        .signatures td { vertical-align: top; }
        .sign-box { width: 48%; }
        .sign-line { border-bottom: 1px solid #000; height: 18px; }
        .sign-name { font-weight: 700; text-align: center; margin-top: 2px; }
        .sign-role { font-style: italic; text-align: center; font-size: 9px; }
        .sign-pos { text-align: center; font-size: 9px; }
        .footer { text-align: center; font-size: 8px; margin-top: 20px; border-top: 1px solid #ccc; padding-top: 6px; color: #666; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
<div class="doc">

    <div class="center">
        <p class="header-line">Republic of the Philippines</p>
        <p class="header-line">Department of Education</p>
        <p class="school-name">{{ $school_name }}</p>
        <p class="title">CLASSROOM OBSERVATION TOOL</p>
        <p class="subtitle">(for {{ $framework_label }})</p>
    </div>

    <table class="info-table bordered">
        <tr>
            <td class="label">Name of Teacher:</td>
            <td>{{ $teacher_name }}</td>
            <td class="label">Position:</td>
            <td>{{ $teacher_position ?: '____________' }}</td>
        </tr>
        <tr>
            <td class="label">Date of Observation:</td>
            <td>{{ $date_label }}</td>
            <td class="label">Time:</td>
            <td>{{ $time_label ?: '____________' }}</td>
        </tr>
        <tr>
            <td class="label">Subject:</td>
            <td>{{ $subject }}</td>
            <td class="label">Grade &amp; Section:</td>
            <td>{{ $grade_section }}</td>
        </tr>
        <tr>
            <td class="label">School:</td>
            <td>{{ $school_name }}</td>
            <td class="label">Quarter:</td>
            <td>Quarter {{ $quarter }}</td>
        </tr>
        <tr>
            <td class="label">School Year:</td>
            <td>{{ $school_year }}</td>
            <td class="label">Observation No.:</td>
            <td>Observation {{ $observation_number }}</td>
        </tr>
        <tr>
            <td class="label">Name of Observer:</td>
            <td>{{ $observer_name }}</td>
            <td class="label">Observer Position:</td>
            <td>{{ $observer_position ?: '____________' }}</td>
        </tr>
    </table>

    <table class="rating-table bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>PPST Indicators</th>
                <th>6</th>
                <th>5</th>
                <th>4</th>
                <th>3</th>
                <th>2</th>
                <th>NO</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @php $index = 0; @endphp
            @foreach($grouped_ratings as $group)
                <tr class="domain-row">
                    <td colspan="9">{{ $group['domain'] }}</td>
                </tr>
                @foreach($group['items'] as $rating)
                    @php $index++; @endphp
                    <tr>
                        <td class="col-num">{{ $index }}</td>
                        <td class="col-indicator"><strong>{{ $rating->indicator_code }}.</strong> {{ $rating->indicator }}</td>
                        <td class="col-rating {{ !$rating->not_observed && $rating->rating == 6 ? 'marked' : '' }}">{{ !$rating->not_observed && $rating->rating == 6 ? 'X' : '' }}</td>
                        <td class="col-rating {{ !$rating->not_observed && $rating->rating == 5 ? 'marked' : '' }}">{{ !$rating->not_observed && $rating->rating == 5 ? 'X' : '' }}</td>
                        <td class="col-rating {{ !$rating->not_observed && $rating->rating == 4 ? 'marked' : '' }}">{{ !$rating->not_observed && $rating->rating == 4 ? 'X' : '' }}</td>
                        <td class="col-rating {{ !$rating->not_observed && $rating->rating == 3 ? 'marked' : '' }}">{{ !$rating->not_observed && $rating->rating == 3 ? 'X' : '' }}</td>
                        <td class="col-rating {{ !$rating->not_observed && $rating->rating == 2 ? 'marked' : '' }}">{{ !$rating->not_observed && $rating->rating == 2 ? 'X' : '' }}</td>
                        <td class="col-no {{ $rating->not_observed ? 'marked' : '' }}">{{ $rating->not_observed ? 'X' : '' }}</td>
                        <td class="col-comments">{{ $rating->comments ?? '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <p class="summary">
        <strong>Total:</strong> {{ number_format($total, 1) }}&nbsp;&nbsp;&nbsp;
        <strong>Average:</strong> {{ number_format($average, 2) }} / 6.00&nbsp;&nbsp;&nbsp;
        <strong>Percentage:</strong> {{ number_format($percentage, 2) }}%
    </p>

    @if($post_conference)
        @php
            $pcRows = [];
            if ($post_conference->conference_date) $pcRows[] = ['Conference Date', $post_conference->conference_date->format('F d, Y')];
            if ($post_conference->ai_comparison) $pcRows[] = ['AI Comparison (Plan vs Actual)', is_array($post_conference->ai_comparison) ? implode(' ', $post_conference->ai_comparison) : $post_conference->ai_comparison];
            if ($post_conference->feedback) $pcRows[] = ['Feedback', $post_conference->feedback];
            if ($post_conference->areas_for_improvement) $pcRows[] = ['Areas for Improvement', $post_conference->areas_for_improvement];
            if ($post_conference->prioritized_next_steps) $pcRows[] = ['Prioritized Next Steps', $post_conference->prioritized_next_steps];
            if ($post_conference->supervisor_notes) $pcRows[] = ['Supervisor\'s Notes', $post_conference->supervisor_notes];
        @endphp
        @if(!empty($pcRows))
            <p class="section-title">POST-OBSERVATION CONFERENCE SUMMARY</p>
            <table class="bordered">
                @foreach($pcRows as [$label, $value])
                    <tr>
                        <td class="label" style="width: 26%; background:#f2f2f2; font-weight:700;">{{ $label }}</td>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endif

    @if($ai_feedbacks->isNotEmpty())
        <p class="section-title">AI-GENERATED FEEDBACK (Reference Only — Does Not Replace Official Ratings)</p>
        <table class="bordered">
            @foreach($ai_feedbacks as $feedback)
                <tr>
                    <td style="width: 30%; background:#fce5cd;">{{ $feedback->cotRating?->indicator_code ?? '' }}. {{ $feedback->cotRating?->indicator ?? '' }}</td>
                    <td class="ai-section">{{ $feedback->analysis ?: implode(' ', $feedback->recommendations ?? []) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <table class="signatures">
        <tr>
            <td class="sign-box">
                <div class="sign-line"></div>
                <p class="sign-name">{{ $observer_name }}</p>
                <p class="sign-role">Observed By — Signature over Printed Name</p>
                @if($observer_position)<p class="sign-pos">{{ $observer_position }}</p>@endif
            </td>
            <td style="width: 4%;">&nbsp;</td>
            <td class="sign-box">
                <div class="sign-line"></div>
                <p class="sign-name">{{ $teacher_name }}</p>
                <p class="sign-role">Observed — Signature over Printed Name</p>
                @if($teacher_position)<p class="sign-pos">{{ $teacher_position }}</p>@endif
            </td>
        </tr>
    </table>

    <p class="footer">COT Document — {{ $teacher_name }} — generated by ASPIRE on {{ now()->format('F d, Y') }}</p>

</div>
</body>
</html>
