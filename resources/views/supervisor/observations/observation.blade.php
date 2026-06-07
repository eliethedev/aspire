@extends('layouts.supervisor')

@section('title', 'Classroom Observation')

@push('styles')
<style>
    select option {
        background-color: #1f2937;
        color: #ffffff;
    }
</style>
@endpush

@php
    $stageKeys = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
    $stageLabels = [
        'pre_observation_planning' => 'Pre-Observation Planning',
        'pre_conference' => 'Pre-Conference',
        'observation' => 'Observation',
        'post_conference' => 'Post-Conference',
    ];
    $stageRoutes = [
        'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
        'pre_conference' => 'supervisor.observations.preConference',
        'observation' => 'supervisor.observations.observation',
        'post_conference' => 'supervisor.observations.postConference',
    ];
    $currentStage = 'observation';
    $currentIdx = array_search($currentStage, $stageKeys);
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 font-medium">Observation</li>
        </ol>
    </nav>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach($stageKeys as $i => $key)
                @php
                    $isCurrent = $key === $currentStage;
                    $isCompleted = $i < $currentIdx;
                    $canAccess = $isCurrent || $isCompleted;
                @endphp
                @if($i > 0)
                    <div class="flex-1 mx-4 h-1 {{ $isCompleted ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
                @if($canAccess)
                    <a href="{{ $isCurrent ? '#' : route($stageRoutes[$key], $observation) }}"
                       class="flex items-center group {{ $isCurrent ? 'cursor-default' : 'cursor-pointer' }}">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $isCompleted ? 'bg-green-600 text-white' : 'bg-indigo-600 text-white ring-2 ring-indigo-200' }} font-semibold transition-colors group-hover:shadow-md text-sm">
                            @if($isCompleted)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </div>
                        <span class="ml-2 {{ $isCompleted ? 'text-gray-600' : 'text-gray-900 font-medium' }} text-sm group-hover:text-indigo-600 transition-colors">{{ $stageLabels[$key] }}</span>
                    </a>
                @else
                    <div class="flex items-center opacity-50">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-400 font-semibold text-sm">{{ $i + 1 }}</div>
                        <span class="ml-2 text-gray-400 text-sm">{{ $stageLabels[$key] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $observation->isTeacherObservation() ? 'Classroom Observation' : 'School Head Observation' }}
        </h1>
        <p class="text-gray-500 mt-1">
            Complete the {{ $observation->isTeacherObservation() ? 'PPST' : 'Leadership' }} COT Form for {{ $observation->observee->user->name }}
        </p>
    </div>

    <!-- Pre-Conference Summary -->
    @if($preConference)
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pre-Conference Summary</h2>
        <div class="space-y-3">
            @if($preConference->finalized_focus)
            <div>
                <span class="text-gray-500 text-sm">Finalized Focus:</span>
                <p class="text-gray-900 mt-1">{{ $preConference->finalized_focus }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="POST" action="{{ route('supervisor.observations.storeObservationData', $observation) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            
            <!-- Observee Info -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-500 text-sm">{{ $observation->isTeacherObservation() ? 'Teacher' : 'School Head' }}</span>
                        <p class="text-gray-900 font-medium">{{ $observation->observee->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Observation Date</span>
                        <p class="text-gray-900 font-medium">{{ $observation->observation_date->format('M d, Y') }}</p>
                    </div>
                    @if($observation->isTeacherObservation())
                    <div>
                        <span class="text-gray-500 text-sm">Subject</span>
                        <p class="text-gray-900 font-medium">{{ $observation->subject ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm">Grade Level</span>
                        <p class="text-gray-900 font-medium">{{ $observation->grade_level ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- COT Ratings -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-4">
                    {{ $observation->isTeacherObservation() ? 'PPST Ratings (1-5 Scale)' : 'Leadership COT Ratings (1-5 Scale)' }}
                </label>
                
                <div id="ratings-container" class="space-y-4">
                    <!-- Rating items will be added dynamically -->
                </div>

                <button type="button" onclick="addRating()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Rating
                </button>
            </div>

            <!-- Evidence Files -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Evidence Files <span class="text-gray-400 font-normal">(photos, videos, documents)</span></label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 transition-colors">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <p class="text-sm text-gray-500 mb-1">Drop files here or click to upload</p>
                    <p class="text-xs text-gray-400">Upload photos, videos, or documents as evidence</p>
                    <input type="file" name="evidence_files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx"
                           class="mt-3 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                @if($observation->evidence_files)
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($observation->evidence_files as $file)
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium truncate">
                                    {{ $file['original_name'] ?? basename($file['path']) }}
                                </a>
                                <span class="text-gray-400 text-xs shrink-0 ml-2">{{ isset($file['size']) ? number_format($file['size'] / 1024, 1) . ' KB' : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('supervisor.observations.preConference', $observation) }}" 
                       class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium text-sm text-center transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Back to Pre-Conference
                        </span>
                    </a>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            Save &amp; Continue
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bottom Navigation -->
    <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <a href="{{ route('supervisor.observations.show', $observation) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Details
            </a>
            <a href="{{ route('supervisor.observations.index') }}"
               class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
                All Evaluations
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let ratingIndex = 0;
    const observationType = '{{ $observation->observation_type }}';

    // PPST Domains and Indicators (for Teacher Observations)
    const ppstDomains = [
        {
            name: 'Domain 1: Content Knowledge and Pedagogy',
            indicators: [
                '1.1.1 Apply knowledge of content within and across curriculum teaching areas',
                '1.2.1 Use research-based knowledge and principles of teaching and learning to enhance professional practice',
                '1.3.1 Use developmentally appropriate teaching strategies to address learners\' developmental needs',
                '1.4.1 Plan and teach using standard- and competence-based learning',
                '1.5.1 Apply knowledge of learner diversity and individual differences',
                '1.6.1 Demonstrate mastery of subject matter',
            ]
        },
        {
            name: 'Domain 2: Learning Environment',
            indicators: [
                '2.1.1 Establish safe and secure learning environments',
                '2.2.1 Promote fairness in the classroom',
                '2.3.1 Manage classroom structure to engage learners',
                '2.4.1 Manage classroom activities to maintain discipline',
                '2.5.1 Use classroom procedures that support learner participation',
                '2.6.1 Manage learner behavior constructively',
            ]
        },
        {
            name: 'Domain 3: Diversity of Learners',
            indicators: [
                '3.1.1 Demonstrate knowledge of policies and guidelines on learner protection',
                '3.2.1 Adapt and use teaching strategies that are responsive to learners\' linguistic and cultural background',
                '3.3.1 Adapt and use teaching strategies that are responsive to learners\' socioeconomic background',
                '3.4.1 Adapt and use teaching strategies that are responsive to learners\' physical disabilities',
                '3.5.1 Adapt and use teaching strategies that are responsive to learners\' giftedness and talents',
            ]
        },
        {
            name: 'Domain 4: Curriculum and Planning',
            indicators: [
                '4.1.1 Plan and deliver lessons using appropriate teaching and learning resources',
                '4.2.1 Plan and deliver lessons using appropriate assessment strategies',
                '4.3.1 Plan and deliver lessons using appropriate instructional planning',
                '4.4.1 Plan and deliver lessons using appropriate learning activities',
                '4.5.1 Plan and deliver lessons using appropriate learning outcomes',
            ]
        },
        {
            name: 'Domain 5: Assessment and Reporting',
            indicators: [
                '5.1.1 Design and use assessment tools that are aligned with learning outcomes',
                '5.2.1 Monitor and evaluate learner progress',
                '5.3.1 Provide timely and accurate feedback to learners',
                '5.4.1 Communicate learner progress to stakeholders',
                '5.5.1 Use assessment data to improve teaching and learning',
            ]
        }
    ];

    // School Head Leadership Domains (for School Head Observations)
    const schoolHeadDomains = [
        {
            name: 'Domain 1: School Leadership',
            indicators: [
                '1.1.1 Demonstrate commitment to the vision, mission, and goals of the school',
                '1.2.1 Set high standards for performance and achievement',
                '1.3.1 Foster a culture of continuous improvement',
                '1.4.1 Lead strategic planning and implementation',
            ]
        },
        {
            name: 'Domain 2: Instructional Leadership',
            indicators: [
                '2.1.1 Supervise and support teaching and learning',
                '2.2.1 Monitor and evaluate curriculum implementation',
                '2.3.1 Promote professional development of teachers',
                '2.4.1 Use data to inform instructional decisions',
            ]
        },
        {
            name: 'Domain 3: Creating a Student-Centered Learning Climate',
            indicators: [
                '3.1.1 Ensure safe and conducive learning environment',
                '3.2.1 Promote inclusive education practices',
                '3.3.1 Implement learner support programs',
                '3.4.1 Foster positive school culture',
            ]
        },
        {
            name: 'Domain 4: Human Resource Development and Management',
            indicators: [
                '4.1.1 Develop and implement human resource plans',
                '4.2.1 Manage staff performance and development',
                '4.3.1 Promote staff welfare and well-being',
                '4.4.1 Build collaborative teams',
            ]
        },
        {
            name: 'Domain 5: Parent Involvement and Community Partnership',
            indicators: [
                '5.1.1 Engage parents in school activities',
                '5.2.1 Build community partnerships',
                '5.3.1 Mobilize community resources',
                '5.4.1 Communicate effectively with stakeholders',
            ]
        },
        {
            name: 'Domain 6: Professional Development and Personal Growth',
            indicators: [
                '6.1.1 Engage in continuous professional learning',
                '6.2.1 Model ethical and professional behavior',
                '6.3.1 Demonstrate reflective practice',
                '6.4.1 Share best practices with colleagues',
            ]
        }
    ];

    function getDomains() {
        return observationType === 'teacher_observation' ? ppstDomains : schoolHeadDomains;
    }

    function addRating() {
        const container = document.getElementById('ratings-container');
        const domains = getDomains();
        const domainSelect = domains.map((d, i) => 
            `<option value="${d.name}">${d.name}</option>`
        ).join('');

        const html = `
            <div class="rating-item bg-white rounded-lg p-4 border border-gray-200" data-index="${ratingIndex}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                        <select name="ratings[${ratingIndex}][domain]" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="">Select Domain</option>
                            ${domainSelect}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Indicator</label>
                        <input type="text" name="ratings[${ratingIndex}][indicator]" required placeholder="Enter indicator"
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating (1-5)</label>
                        <select name="ratings[${ratingIndex}][rating]" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="">Select Rating</option>
                            <option value="1">1 - Beginning</option>
                            <option value="2">2 - Developing</option>
                            <option value="3">3 - Proficient</option>
                            <option value="4">4 - Highly Proficient</option>
                            <option value="5">5 - Distinguished</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
                        <input type="text" name="ratings[${ratingIndex}][comments]" placeholder="Optional comments"
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
                <button type="button" onclick="removeRating(${ratingIndex})" class="mt-2 inline-flex items-center gap-1 text-red-600 hover:text-red-700 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Remove
                </button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        ratingIndex++;
    }

    function removeRating(index) {
        const item = document.querySelector(`.rating-item[data-index="${index}"]`);
        if (item) {
            item.remove();
        }
    }

    // Add initial rating
    addRating();
</script>
@endpush
@endsection
