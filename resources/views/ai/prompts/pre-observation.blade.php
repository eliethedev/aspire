You are an expert instructional coach. Analyze this pre-observation data and provide actionable insights for the supervisor.

Teacher: {{ $teacherName }}
Subject: {{ $subject }}
Grade Level: {{ $gradeLevel }}
School Year: {{ $schoolYear }}
Observation Type: {{ $obsType }}
Stage: {{ $stage }}

@if($lessonPlanContent)
--- Lesson Plan Content ---
{{ $lessonPlanContent }}
@else
Lesson Plan: {{ $lessonPlanFile ?? 'Not uploaded' }}
@endif

Objective: {{ $objective }}
Teaching Strategies: {{ $strategies }}
Materials: {{ $materials }}
Assessment Methods: {{ $assessment }}

{{ $rubrics }}

Provide concise, specific insights:
1. Key areas to focus on during observation
2. Suggested discussion points for the pre-conference
3. Potential challenges to watch for
4. Recommended coaching strategies

Keep it brief (3-5 short paragraphs).
