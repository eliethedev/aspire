@php
    $ratingValues = [1, 2, 3, 4, 5];
    $ratingLabels = [
        5 => 'Always',
        4 => 'Often',
        3 => 'Sometimes',
        2 => 'Seldom',
        1 => 'Never',
    ];
    $ratingColspan = 2 + count($ratingValues);

    $domains = [
            'Domain 1: Establishing a Warm and Clear Opening of the Post Observation Conference' => [
            'School Head acknowledges teacher\'s time (Thanks the teacher for allowing him/her to observe a class)',
            'School Head states the purpose of the conversation',
            'Talks in a voice that is warm, friendly and sincere',
        ],
        'Domain 2: Focus on what\'s going well' => [
            'Congratulates teachers for doing a job well (cite specific instances or teacher behavior/activities that are worth mentioning. Refer to the strengths noted)',
            'Asks the teacher to clearly state the objectives of the lesson',
            'Paraphrases and affirms the teacher\'s lesson objective (Asks what the pupils are able to demonstrate at the end of the lesson)',
            'Asks the teacher what she did to teach the lesson',
            'Asks teacher what made him/her happy about the delivery of the lesson. The SH listens intently to what the teacher is saying',
            'The SH affirms what the teacher considered as things that went well in the delivery of the lesson',
            'The SH extends the positive focus in addition to what the teacher identified as what went well, citing additional specific things referring to the strengths noted',
        ],
        'Domain 3: Identify Challenges Facing the Teacher' => [
            'The SH asks the teacher to tell which part of the lesson she thinks did not go well',
            'The SH paraphrases teacher\'s message to check whether they have the same understanding',
            'The SH enables the teacher to tell additional parts that did not go well by citing specific instances recorded in the strengths noted',
            'The SH avoids diversion and stays focused on the issues/data/documentation at hand when teacher makes caustic statements',
            'The SH is able to verify the teacher\'s perception about the identified areas for improvement',
        ],
        'Domain 4: Generating Ideas for Addressing Teacher\'s Challenges' => [
            'The SH guides the teacher in identifying possible strategies in addressing the challenges',
            'The SH helps solve the problem by offering ideas for improvement if and when the teacher is not able to do so',
            'The SH connects the teacher to available and appropriate resources to help address the challenges',
            'The SH avoids compromising statements that provide an excuse for poor performance',
        ],
        'Domain 5: Prioritizing the Next Steps' => [
            'The Teacher and the principal reviews ideas for improvement and assign priority to possible options',
        ],
        'Domain 6: Ending the Post Observation Conference' => [
            'The SH makes the teacher agree on the next steps by asking the teacher to choose whose help he/she would want to ask to assist in improving the identified challenges',
            'The SH enables the teacher to make a commitment regarding the next steps identified',
            'The SH thanks the teacher for the conversation',
        ],
    ];

    $existingRatings = $epocEvaluation->ratings ?? collect();
    $globalIndex = 0;
@endphp

<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Post-Observation Conference Evaluation</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">DepEd CID Format &middot; Post-Observation Conference</p>
            </div>
            @if($schoolHead)
            <div class="text-right">
                <p class="text-xs text-gray-500 dark:text-gray-400">School Head</p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $schoolHead->name }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm epoc-table">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left px-4 py-3 text-gray-600 dark:text-gray-400 font-semibold w-8">#</th>
                    <th class="text-left px-4 py-3 text-gray-600 dark:text-gray-400 font-semibold">Indicators</th>
                    @foreach($ratingValues as $val)
                        <th class="text-center px-1.5 py-3 text-gray-600 dark:text-gray-400 font-semibold w-20">
                            <div class="text-xs font-bold">{{ $val }}</div>
                            <div class="text-[9px] font-normal text-gray-400 dark:text-gray-500 leading-tight mt-0.5">{{ $ratingLabels[$val] }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($domains as $domain => $items)
                    <tr class="bg-indigo-50/50 border-b border-indigo-100">
                        <td colspan="{{ $ratingColspan }}" class="px-4 py-2.5 text-sm font-semibold text-indigo-800 dark:text-indigo-200">{{ $domain }}</td>
                    </tr>
                    @foreach($items as $item)
                        @php
                            $savedRating = null;
                            $savedComment = '';
                            if ($existingRatings->isNotEmpty()) {
                                $match = $existingRatings->first(function ($r) use ($item) {
                                    $stored = $r->indicator ?? '';
                                    return $stored === $item
                                        || html_entity_decode($stored) === $item;
                                });
                                if ($match) {
                                    $savedRating = $match->rating ?? null;
                                    $savedComment = $match->comments ?? '';
                                }
                            }
                        @endphp
                        <tr class="indicator-row border-b border-gray-100 epoc-row" data-index="{{ $globalIndex }}">
                            <td class="px-4 py-2.5 text-gray-400 dark:text-gray-500 text-xs align-top pt-3">{{ $globalIndex + 1 }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-start gap-2">
                                    <span class="text-gray-800 text-xs leading-relaxed">{{ $item }}</span>
                                    <button type="button" data-action="toggle-comment" data-index="{{ $globalIndex }}"
                                            class="comment-toggle shrink-0 mt-0.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium text-gray-400 border border-gray-200 hover:border-indigo-300 {{ $savedComment ? 'has-comment' : '' }}"
                                            title="Add comment for this indicator">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                        {{ $savedComment ? 'View Comment' : 'Add Comment' }}
                                    </button>
                                </div>
                                <input type="hidden" name="epoc_ratings[{{ $globalIndex }}][domain]" value="{{ $domain }}">
                                <input type="hidden" name="epoc_ratings[{{ $globalIndex }}][indicator]" value="{{ $item }}">
                            </td>
                            @foreach($ratingValues as $val)
                                <td class="text-center px-1.5 py-2.5">
                                    <button type="button"
                                            data-action="select-epoc-rating" data-index="{{ $globalIndex }}" data-value="{{ $val }}"
                                            class="rating-btn w-10 h-10 rounded-full text-xs font-bold border-2 {{ $savedRating == $val ? 'active bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                                        {{ $val }}
                                    </button>
                                </td>
                            @endforeach
                        </tr>
                        <tr class="comment-row" id="epoc-comment-row-{{ $globalIndex }}" data-index="{{ $globalIndex }}">
                            <td colspan="{{ $ratingColspan }}">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Comment / Observation Note</label>
                                <textarea name="epoc_ratings[{{ $globalIndex }}][comments]" rows="2" data-epoc-comment-input="{{ $globalIndex }}"
                                          placeholder="Add specific observations for this indicator...">{{ $savedComment }}</textarea>
                            </td>
                        </tr>
                        @php $globalIndex++; @endphp
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Narrative Observation</h2>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Provide a detailed narrative of the post-observation conference session.</p>
    <textarea name="epoc_narrative_observation" rows="6"
              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
              placeholder="Describe the overall flow and key moments of the post-observation conference...">{{ $epocEvaluation->narrative_observation ?? '' }}</textarea>
</div>

<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Agreement</h2>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Document any agreements or commitments made during the conference.</p>
    <textarea name="epoc_agreement" rows="4"
              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
              placeholder="Record the agreements and next steps decided upon...">{{ $epocEvaluation->agreement ?? '' }}</textarea>
</div>

<input type="hidden" name="epoc_total_items" value="{{ $globalIndex }}">

<script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action="select-epoc-rating"]');
        if (!btn) return;
        var index = parseInt(btn.getAttribute('data-index'));
        var value = parseInt(btn.getAttribute('data-value'));
        var row = document.querySelector('.epoc-row[data-index="' + index + '"]');
        if (!row) return;

        row.querySelectorAll('.rating-btn').forEach(function (b) {
            b.classList.remove('active', 'bg-indigo-600', 'text-white', 'border-indigo-600');
            b.classList.add('bg-white', 'dark:bg-gray-900', 'text-gray-600', 'dark:text-gray-400', 'border-gray-300', 'dark:border-gray-600');
        });
        btn.classList.remove('bg-white', 'dark:bg-gray-900', 'text-gray-600', 'dark:text-gray-400', 'border-gray-300', 'dark:border-gray-600');
        btn.classList.add('active', 'bg-indigo-600', 'text-white', 'border-indigo-600');
        row.classList.add('selected');

        var existing = document.getElementById('epoc-ratings-input-' + index);
        if (existing) existing.remove();
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'epoc_ratings[' + index + '][rating]';
        hidden.value = value;
        hidden.id = 'epoc-ratings-input-' + index;
        row.appendChild(hidden);

        if (window.asAutoSaveDebounced) asAutoSaveDebounced('observation');
    });

    function epocToggleComment(index) {
        var commentRow = document.getElementById('epoc-comment-row-' + index);
        if (!commentRow) return;
        var isOpen = commentRow.classList.contains('open');
        if (isOpen) {
            commentRow.classList.remove('open');
        } else {
            commentRow.classList.add('open');
            var textarea = commentRow.querySelector('textarea');
            if (textarea) textarea.focus();
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action="toggle-comment"]');
        if (!btn) return;
        epocToggleComment(parseInt(btn.getAttribute('data-index')));
    });

    document.addEventListener('input', function (e) {
        if (e.target.matches('[data-epoc-comment-input]')) {
            var index = parseInt(e.target.getAttribute('data-epoc-comment-input'));
            var btn = document.querySelector('.comment-toggle[data-index="' + index + '"]');
            if (!btn) return;
            var hasText = e.target.value.trim() !== '';
            btn.classList.toggle('has-comment', hasText);
            var textNode = btn.lastChild;
            if (textNode && textNode.nodeType === 3) {
                textNode.textContent = hasText ? ' View Comment' : ' Add Comment';
            }
            if (window.asAutoSaveDebounced) asAutoSaveDebounced('observation');
        }
    });
</script>
