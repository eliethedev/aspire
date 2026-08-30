@php
    if (!isset($sectionKey) || !isset($observation)) return;
    $formService = app(\App\Services\FormTemplateService::class);
    $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y') . '-' . (date('Y') + 1));
    $templateFields = $formService->getFieldsForSection($schoolYear, $sectionKey, $observation->observation_type);

    // Determine which model to use for existing values
    $dataModel = null;
    if ($sectionKey === 'pre_conference' && isset($preConference)) $dataModel = $preConference;
    elseif ($sectionKey === 'post_conference' && isset($postConference)) $dataModel = $postConference;
    elseif ($sectionKey === 'pre_observation_planning' && isset($planning)) $dataModel = $planning;
@endphp

@if($templateFields->isNotEmpty())
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-100 dark:border-indigo-900/40">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <h3 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">Dynamic Form Fields</h3>
            <span class="text-xs bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-full font-medium">Template-Based</span>
        </div>
        <div class="space-y-4">
            @foreach($templateFields as $field)
                @php
                    $fieldValue = null;
                    if ($dataModel) {
                        if ($field->column_map) {
                            $fieldValue = $dataModel->{$field->column_map} ?? null;
                        } else {
                            $fieldValue = $dataModel->form_responses[$field->key] ?? null;
                        }
                    }
                @endphp
                <x-dynamic-field :field="$field" :value="$fieldValue" :errors="$errors->getMessages()" />
            @endforeach
        </div>
    </div>
@endif
