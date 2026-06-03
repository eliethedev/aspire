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

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">✓</div>
                <span class="ml-2 text-white font-medium">Pre-Observation Planning</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-green-600"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">✓</div>
                <span class="ml-2 text-white font-medium">Pre-Conference</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-green-600"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">3</div>
                <span class="ml-2 text-white font-medium">Observation</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-white/20"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 text-white/60 font-semibold">4</div>
                <span class="ml-2 text-white/60">Post-Conference</span>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">
            {{ $observation->isTeacherObservation() ? 'Classroom Observation' : 'School Head Observation' }}
        </h1>
        <p class="text-white/60 mt-1">
            Complete the {{ $observation->isTeacherObservation() ? 'PPST' : 'Leadership' }} COT Form for {{ $observation->observee->user->name }}
        </p>
    </div>

    <!-- Pre-Conference Summary -->
    @if($preConference)
    <div class="bg-white/5 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Pre-Conference Summary</h2>
        <div class="space-y-3">
            @if($preConference->finalized_focus)
            <div>
                <span class="text-white/60 text-sm">Finalized Focus:</span>
                <p class="text-white mt-1">{{ $preConference->finalized_focus }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="POST" action="{{ route('supervisor.observations.storeObservationData', $observation) }}" class="space-y-6">
            @csrf
            
            <!-- Observee Info -->
            <div class="bg-white/5 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-white/60 text-sm">{{ $observation->isTeacherObservation() ? 'Teacher' : 'School Head' }}</span>
                        <p class="text-white font-medium">{{ $observation->observee->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-white/60 text-sm">Observation Date</span>
                        <p class="text-white font-medium">{{ $observation->observation_date->format('M d, Y') }}</p>
                    </div>
                    @if($observation->isTeacherObservation())
                    <div>
                        <span class="text-white/60 text-sm">Subject</span>
                        <p class="text-white font-medium">{{ $observation->subject ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-white/60 text-sm">Grade Level</span>
                        <p class="text-white font-medium">{{ $observation->grade_level ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- COT Ratings -->
            <div>
                <label class="block text-sm font-medium text-white mb-4">
                    {{ $observation->isTeacherObservation() ? 'PPST Ratings (1-5 Scale)' : 'Leadership COT Ratings (1-5 Scale)' }}
                </label>
                
                <div id="ratings-container" class="space-y-4">
                    <!-- Rating items will be added dynamically -->
                </div>

                <button type="button" onclick="addRating()" class="mt-4 px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-sm">
                    + Add Rating
                </button>
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <a href="{{ route('supervisor.observations.preConference', $observation) }}" 
                   class="px-6 py-2 rounded-lg border border-white/20 text-white hover:bg-white/10">
                    Back
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Save & Continue to Post-Conference
                </button>
            </div>
        </form>
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
            <div class="rating-item bg-white/5 rounded-lg p-4" data-index="${ratingIndex}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Domain</label>
                        <select name="ratings[${ratingIndex}][domain]" required class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Domain</option>
                            ${domainSelect}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Indicator</label>
                        <input type="text" name="ratings[${ratingIndex}][indicator]" required placeholder="Enter indicator"
                               class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Rating (1-5)</label>
                        <select name="ratings[${ratingIndex}][rating]" required class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Rating</option>
                            <option value="1">1 - Beginning</option>
                            <option value="2">2 - Developing</option>
                            <option value="3">3 - Proficient</option>
                            <option value="4">4 - Highly Proficient</option>
                            <option value="5">5 - Distinguished</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Comments</label>
                        <input type="text" name="ratings[${ratingIndex}][comments]" placeholder="Optional comments"
                               class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <button type="button" onclick="removeRating(${ratingIndex})" class="mt-3 text-red-400 hover:text-red-300 text-sm">Remove</button>
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
