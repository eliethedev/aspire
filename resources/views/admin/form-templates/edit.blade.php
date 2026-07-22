@extends('layouts.admin')

@section('title', 'Edit Form Template')

@push('styles')
<style>
    .field-item { transition: all 0.2s ease; }
    .field-item:hover { border-color: #a5b4fc; }
    .field-item.dragging { opacity: 0.5; }
    .section-handle, .field-handle { cursor: grab; }
    .section-handle:active, .field-handle:active { cursor: grabbing; }
    .options-textarea { font-family: 'Courier New', monospace; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 space-y-6">
    <nav class="text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400 dark:text-gray-500">
            <li><a href="{{ route('admin.form-templates.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Form Templates</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">{{ $formTemplate->name }}</li>
        </ol>
    </nav>

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg></div>
            <div class="ml-3"><p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p></div>
        </div>
    </div>
    @endif

    <script>window._templateSections = @json($formTemplate->sections);</script>

    <form method="POST" action="{{ route('admin.form-templates.update', $formTemplate) }}"
          x-data="formBuilder()"
          x-init="init(window._templateSections)">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Template Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 sticky top-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Template Settings</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $formTemplate->name) }}"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">{{ old('description', $formTemplate->description) }}</textarea>
                        </div>
                        <div>
                            <label for="school_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Year</label>
                            <select name="school_year" id="school_year"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @foreach($schoolYears as $value => $label)
                                    <option value="{{ $value }}" {{ old('school_year', $formTemplate->school_year) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="observation_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observation Type</label>
                            <select name="observation_type" id="observation_type"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @foreach($observationTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('observation_type', $formTemplate->observation_type ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Assign to a specific observation type, or "All Types".</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <div class="flex items-center gap-2">
                                @if($formTemplate->is_active)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:text-gray-400 dark:text-gray-500">Inactive</span>
                                @endif
                                <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">v{{ $formTemplate->version }}</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                            Save Template
                        </button>
                        <button type="button" @click="addSection()"
                                class="w-full px-4 py-2.5 border-2 border-dashed border-gray-300 text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:border-indigo-400 hover:text-indigo-600 dark:text-indigo-400 rounded-lg font-medium text-sm transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Section
                        </button>
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <!-- Duplicate Template -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Duplicate Template</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-3">Copy this template to a new school year.</p>
                        <form method="POST" action="{{ route('admin.form-templates.duplicate', $formTemplate) }}" class="flex gap-2" onsubmit="return confirm('Duplicate this template?')">
                            @csrf
                            <select name="school_year" required
                                    class="flex-1 px-2 py-1.5 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($schoolYears as $value => $label)
                                    <option value="{{ $value }}" {{ $value == $formTemplate->school_year ? '' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:bg-indigo-900/30 rounded-lg transition-colors whitespace-nowrap">Duplicate</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Form Builder -->
            <div class="lg:col-span-2 space-y-6">
                <template x-for="(section, sIdx) in sections" :key="sIdx">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <!-- Section Header -->
                        <div class="flex items-center gap-3 p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-xl">
                            <span class="section-handle text-gray-400 dark:text-gray-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/></svg>
                            </span>
                            <div class="flex-1 grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Section Key</label>
                                    <input type="text" x-model="section.key" :name="`sections[${sIdx}][key]`"
                                           :readonly="!!section.id"
                                           :class="section.id ? 'bg-gray-100 text-gray-500 dark:text-gray-400 dark:text-gray-500 cursor-not-allowed' : 'bg-white text-gray-900 dark:text-gray-100'"
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Label</label>
                                    <input type="text" x-model="section.label" :name="`sections[${sIdx}][label]`"
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                            <input type="hidden" :name="`sections[${sIdx}][id]`" x-model="section.id">
                            <button type="button" @click="removeSection(sIdx)" class="text-red-400 hover:text-red-600 dark:text-red-400 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>

                        <!-- Section Fields -->
                        <div class="p-4 space-y-3">
                            <template x-for="(field, fIdx) in section.fields" :key="fIdx">
                                <div class="field-item border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white">
                                    <div class="flex items-start gap-3">
                                        <span class="field-handle text-gray-400 dark:text-gray-500 mt-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/></svg>
                                        </span>
                                        <div class="flex-1 space-y-2">
                                            <div class="grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Key</label>
                                                    <input type="text" x-model="field.key" :name="`sections[${sIdx}][fields][${fIdx}][key]`"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 font-mono">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Label</label>
                                                    <input type="text" x-model="field.label" :name="`sections[${sIdx}][fields][${fIdx}][label]`"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Type</label>
                                                    <select x-model="field.type" :name="`sections[${sIdx}][fields][${fIdx}][type]`"
                                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white">
                                                        @foreach($fieldTypes as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2" x-show="!['heading','paragraph','hr'].includes(field.type)">
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Placeholder</label>
                                                    <input type="text" x-model="field.placeholder" :name="`sections[${sIdx}][fields][${fIdx}][placeholder]`"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Help Text</label>
                                                    <input type="text" x-model="field.help_text" :name="`sections[${sIdx}][fields][${fIdx}][help_text]`"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                            </div>

                                            <div x-show="['select','radio','checkbox'].includes(field.type)">
                                                <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Options <span class="text-gray-400 dark:text-gray-500">(one per line, or <code>value|Label</code>)</span></label>
                                                <textarea x-model="field.options_text" :name="`sections[${sIdx}][fields][${fIdx}][options]`" rows="3"
                                                          class="options-textarea w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                          placeholder="value1|Option 1&#10;value2|Option 2&#10;value3|Option 3"></textarea>
                                            </div>

                                            <div class="grid grid-cols-3 gap-2" x-show="!['heading','paragraph','hr'].includes(field.type)">
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Validation Rules</label>
                                                    <input type="text" x-model="field.validation_rules_text" :name="`sections[${sIdx}][fields][${fIdx}][validation_rules]`"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 font-mono"
                                                           placeholder="required, max:500">
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Column Map</label>
                                                    <select x-model="field.column_map" :name="`sections[${sIdx}][fields][${fIdx}][column_map]`"
                                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white">
                                                        <option value="">-- JSON only --</option>
                                                        @foreach($columnMapOptions as $sectionKey => $columns)
                                                            <optgroup label="{{ $sectionKey }}">
                                                                @foreach($columns as $colValue => $colLabel)
                                                                    <option value="{{ $colValue }}">{{ $colLabel }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="flex items-end gap-2">
                                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                                        <input type="checkbox" x-model="field.required" :name="`sections[${sIdx}][fields][${fIdx}][required]`" value="1"
                                                               class="text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 rounded">
                                                        <span class="text-xs text-gray-600 dark:text-gray-400 dark:text-gray-500">Required</span>
                                                    </label>
                                                    <input type="hidden" :name="`sections[${sIdx}][fields][${fIdx}][id]`" x-model="field.id">
                                                    <button type="button" @click="removeField(sIdx, fIdx)" class="text-red-400 hover:text-red-600 dark:text-red-400 p-1 ml-auto">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="addField(sIdx)"
                                    class="w-full py-2 border-2 border-dashed border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:border-indigo-300 hover:text-indigo-500 rounded-lg text-sm transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Field
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="sections.length === 0" class="text-center py-12 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 font-medium">No sections yet.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Click "Add Section" to start building your form.</p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function formBuilder() {
    return {
        sections: [],
        init(sectionsData) {
            if (sectionsData && sectionsData.length > 0) {
                this.sections = sectionsData.map(s => ({
                    id: s.id || null,
                    key: s.key || '',
                    label: s.label || '',
                    fields: (s.fields || []).map(f => ({
                        id: f.id || null,
                        key: f.key || '',
                        label: f.label || '',
                        type: f.type || 'text',
                        placeholder: f.placeholder || '',
                        help_text: f.help_text || '',
                        default_value: f.default_value || '',
                        validation_rules: f.validation_rules || [],
                        options: f.options || [],
                        order: f.order || 0,
                        required: f.required || false,
                        column_map: f.column_map || '',
                        metadata: f.metadata || null,
                        get options_text() {
                            return (this.options || []).map(o => o.value + '|' + o.label).join('\n');
                        },
                        set options_text(val) {
                            this.options = val.split('\n').filter(l => l.trim()).map(l => {
                                const parts = l.split('|');
                                return { value: parts[0].trim(), label: (parts[1] || parts[0]).trim() };
                            });
                        },
                        get validation_rules_text() {
                            return (this.validation_rules || []).join(', ');
                        },
                        set validation_rules_text(val) {
                            this.validation_rules = val.split(',').map(r => r.trim()).filter(r => r);
                        },
                    }));
                }));
            } else {
                this.addSection('pre_conference', 'Pre-Conference');
            }
        },
        addSection(key, label) {
            this.sections.push({
                id: null,
                key: key || 'section_' + (this.sections.length + 1),
                label: label || 'New Section',
                fields: [],
            });
        },
        removeSection(idx) {
            if (this.sections[idx].id) {
                if (!confirm('Remove this section and all its fields?')) return;
            }
            this.sections.splice(idx, 1);
        },
        addField(sIdx) {
            this.sections[sIdx].fields.push({
                id: null,
                key: 'field_' + (this.sections[sIdx].fields.length + 1),
                label: 'New Field',
                type: 'text',
                placeholder: '',
                help_text: '',
                default_value: '',
                validation_rules: [],
                options: [],
                order: 0,
                required: false,
                column_map: '',
                metadata: null,
            });
        },
        removeField(sIdx, fIdx) {
            this.sections[sIdx].fields.splice(fIdx, 1);
        },
    };
}
</script>
@endpush
